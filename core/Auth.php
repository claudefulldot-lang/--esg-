<?php
namespace App;

use App\Database;
use App\Helpers\AuditLogger;
use App\Helpers\Security;
use PDO;

class Auth {
    public static function init(): void {
        Security::startSession();

        $timeout = max(300, (int)(getenv('ESG_SESSION_TIMEOUT_SECONDS') ?: 3600));
        if (!empty($_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > $timeout) {
            $_SESSION = [];
            session_regenerate_id(true);
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Check if user is logged in
     */
    public static function check(): bool {
        self::init();
        return !empty($_SESSION['esg_user']['id']);
    }

    /**
     * Get current logged-in user (refreshes from database to ensure up-to-date profile)
     */
    public static function user(): ?array {
        self::init();
        if (empty($_SESSION['esg_user']['id'])) {
            return null;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT u.id, u.username, u.real_name, u.email, u.org_id, u.role_id,
                       r.role_key, r.role_name, r.permissions, o.org_name, o.org_type
                FROM `sys_users` u
                JOIN `sys_roles` r ON u.role_id = r.id
                JOIN `sys_organizations` o ON u.org_id = o.id
                WHERE u.id = :id AND u.status = 1
                LIMIT 1
            ");
            $stmt->execute([':id' => $_SESSION['esg_user']['id']]);
            $fresh = $stmt->fetch();
            if ($fresh) {
                $permissions = json_decode($fresh['permissions'] ?? '{}', true) ?: [];
                $_SESSION['esg_user'] = [
                    'id'          => (int)$fresh['id'],
                    'username'    => $fresh['username'],
                    'real_name'   => $fresh['real_name'],
                    'email'       => $fresh['email'],
                    'org_id'      => (int)$fresh['org_id'],
                    'org_name'    => $fresh['org_name'],
                    'org_type'    => $fresh['org_type'],
                    'role_id'     => (int)$fresh['role_id'],
                    'role_key'    => $fresh['role_key'],
                    'role_name'   => $fresh['role_name'],
                    'permissions' => $permissions
                ];
            }
        } catch (\Exception $e) {
            // Fallback to existing session
        }

        return $_SESSION['esg_user'] ?? null;
    }

    /**
     * Attempt login
     */
    public static function attempt(string $username, string $password): array {
        self::init();
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT u.*, r.role_key, r.role_name, r.permissions, o.org_name, o.org_type
            FROM `sys_users` u
            JOIN `sys_roles` r ON u.role_id = r.id
            JOIN `sys_organizations` o ON u.org_id = o.id
            WHERE u.username = :username
            LIMIT 1
        ");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            // Keep response timing close to an existing-user password failure.
            password_verify($password, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
            AuditLogger::log('LOGIN_FAILED', 'AUTH', null, null, ['username' => $username, 'reason' => 'User not found']);
            return ['success' => false, 'message' => '帳號或密碼錯誤'];
        }

        // Check account locked
        if (!empty($user['locked_until']) && strtotime($user['locked_until']) > time()) {
            $remainMinutes = ceil((strtotime($user['locked_until']) - time()) / 60);
            return ['success' => false, 'message' => "帳號因連續錯誤已被鎖定，請於 {$remainMinutes} 分鐘後再試。"];
        }

        // Check account status
        if ((int)$user['status'] !== 1) {
            return ['success' => false, 'message' => '此帳號已被停用，請聯絡系統管理員。'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $attempts = (int)$user['failed_attempts'] + 1;
            $lockedUntil = null;
            if ($attempts >= 5) {
                $lockedUntil = date('Y-m-d H:i:s', time() + (15 * 60)); // Lock for 15 minutes
            }

            $updateStmt = $db->prepare("UPDATE `sys_users` SET `failed_attempts` = :attempts, `locked_until` = :locked WHERE `id` = :id");
            $updateStmt->execute([
                ':attempts' => $attempts,
                ':locked'   => $lockedUntil,
                ':id'       => $user['id']
            ]);

            AuditLogger::log('LOGIN_FAILED', 'AUTH', $user['id'], null, ['username' => $username, 'attempts' => $attempts]);
            if ($attempts >= 5) {
                return ['success' => false, 'message' => '密碼連續錯誤達 5 次，帳號已被鎖定 15 分鐘！'];
            }
            return ['success' => false, 'message' => '帳號或密碼錯誤 (剩餘嘗試次數: ' . (5 - $attempts) . ')'];
        }

        // Reset failed attempts on success
        $updateStmt = $db->prepare("
            UPDATE `sys_users` 
            SET `failed_attempts` = 0, `locked_until` = NULL, `last_login_at` = NOW(), `last_login_ip` = :ip
            WHERE `id` = :id
        ");
        $updateStmt->execute([
            ':ip' => Security::getClientIp(),
            ':id' => $user['id']
        ]);

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $rehash = $db->prepare('UPDATE `sys_users` SET `password_hash` = :hash WHERE `id` = :id');
            $rehash->execute([':hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), ':id' => $user['id']]);
        }

        // Parse permissions
        $permissions = json_decode($user['permissions'] ?? '{}', true) ?: [];

        $_SESSION['esg_user'] = [
            'id'          => (int)$user['id'],
            'username'    => $user['username'],
            'real_name'   => $user['real_name'],
            'email'       => $user['email'],
            'org_id'      => (int)$user['org_id'],
            'org_name'    => $user['org_name'],
            'org_type'    => $user['org_type'],
            'role_id'     => (int)$user['role_id'],
            'role_key'    => $user['role_key'],
            'role_name'   => $user['role_name'],
            'permissions' => $permissions
        ];

        session_regenerate_id(true);
        unset($_SESSION['csrf_token']);

        AuditLogger::log('LOGIN_SUCCESS', 'AUTH', $user['id'], null, ['username' => $username]);
        return ['success' => true];
    }

    /**
     * Logout
     */
    public static function logout(): void {
        self::init();
        if (isset($_SESSION['esg_user'])) {
            AuditLogger::log('LOGOUT', 'AUTH', $_SESSION['esg_user']['id']);
            $_SESSION = [];
        }
        session_regenerate_id(true);
    }

    /**
     * Check if current user has permission
     */
    public static function can(string $module, string $action = 'read'): bool {
        $user = self::user();
        if (!$user) return false;
        if ($user['role_key'] === 'super_admin' || !empty($user['permissions']['all'])) {
            return true;
        }

        $perms = $user['permissions'] ?? [];
        if (!isset($perms[$module])) {
            return false;
        }

        if (is_array($perms[$module])) {
            return in_array($action, $perms[$module], true) || in_array('*', $perms[$module], true);
        }

        return (bool)$perms[$module];
    }

    /**
     * Guard: Require login
     */
    public static function requireLogin(): void {
        if (!self::check()) {
            $loginUrl = (defined('BASE_URL') ? BASE_URL : '/esg') . '/login';
            header("Location: {$loginUrl}");
            exit;
        }
    }

    /**
     * Guard: Require specific permission
     */
    public static function requirePermission(string $module, string $action = 'read'): void {
        self::requireLogin();
        if (!self::can($module, $action)) {
            http_response_code(403);
            $homeUrl = (defined('BASE_URL') ? BASE_URL : '/esg') . '/';
            die("<h1>403 Forbidden</h1><p>您無權限存取此模組或執行此操作 ({$module} / {$action})。<br><a href='{$homeUrl}'>返回首頁</a></p>");
        }
    }
}
