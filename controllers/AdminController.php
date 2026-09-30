<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Database;
use App\Models\User;
use App\Models\Organization;
use App\Helpers\AuditLogger;
use PDO;

class AdminController extends Controller {
    public function users(): void {
        Auth::requirePermission('all');

        $users = User::all();
        $roles = User::getRoles();
        $orgs = Organization::all();

        $this->render('admin/users', [
            'pageTitle' => '使用者帳號與權限指派',
            'activeNav' => 'admin_users',
            'users'     => $users,
            'roles'     => $roles,
            'orgs'      => $orgs
        ]);
    }

    public function userStore(): void {
        Auth::requirePermission('all');
        $this->checkCsrf();

        $id = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;

        $data = [
            'username'  => trim($_POST['username']),
            'password'  => $_POST['password'] ?? '',
            'real_name' => trim($_POST['real_name']),
            'email'     => trim($_POST['email']),
            'phone'     => trim($_POST['phone'] ?? ''),
            'org_id'    => (int)$_POST['org_id'],
            'role_id'   => (int)$_POST['role_id'],
            'status'    => isset($_POST['status']) ? 1 : 0
        ];

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || !Organization::find($data['org_id']) ||
            !array_filter(User::getRoles(), fn($role) => (int)$role['id'] === $data['role_id'])) {
            $_SESSION['flash_error'] = '電子信箱、組織或角色資料無效。';
            $this->redirect('/esg/admin/users');
            return;
        }
        if (!$id && strlen($data['password']) < 12) {
            $_SESSION['flash_error'] = '新帳號密碼至少需要 12 個字元。';
            $this->redirect('/esg/admin/users');
            return;
        }
        if ($id && $data['password'] !== '' && strlen($data['password']) < 12) {
            $_SESSION['flash_error'] = '新密碼至少需要 12 個字元。';
            $this->redirect('/esg/admin/users');
            return;
        }

        if ($id) {
            User::update($id, $data);
            AuditLogger::log('UPDATE_USER', 'ADMIN', $id, null, $data);
            $_SESSION['flash_success'] = '使用者帳號已成功更新！';
        } else {
            if (empty($data['password'])) {
                $_SESSION['flash_error'] = '新建使用者必須設定登入密碼！';
                $this->redirect('/esg/admin/users');
                return;
            }
            $newId = User::create($data);
            AuditLogger::log('CREATE_USER', 'ADMIN', $newId, null, $data);
            $_SESSION['flash_success'] = '新使用者已成功建立！';
        }

        $this->redirect('/esg/admin/users');
    }

    public function orgs(): void {
        Auth::requirePermission('all');

        $orgs = Organization::all();

        $this->render('admin/orgs', [
            'pageTitle' => '組織機構與廠區層級架構',
            'activeNav' => 'admin_orgs',
            'orgs'      => $orgs
        ]);
    }

    public function orgStore(): void {
        Auth::requirePermission('all');
        $this->checkCsrf();

        $id = !empty($_POST['org_id']) ? (int)$_POST['org_id'] : null;

        $data = [
            'org_code'      => trim($_POST['org_code']),
            'org_name'      => trim($_POST['org_name']),
            'org_type'      => $_POST['org_type'],
            'parent_id'     => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
            'leader_name'   => trim($_POST['leader_name'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'sort_order'    => (int)($_POST['sort_order'] ?? 0)
        ];

        if (!in_array($data['org_type'], ['group', 'company', 'plant', 'dept'], true) ||
            ($data['contact_email'] !== '' && !filter_var($data['contact_email'], FILTER_VALIDATE_EMAIL)) ||
            ($id && $data['parent_id'] === $id)) {
            $_SESSION['flash_error'] = '組織類型、電子信箱或父組織設定無效。';
            $this->redirect('/esg/admin/orgs');
            return;
        }

        if ($id) {
            Organization::update($id, $data);
            AuditLogger::log('UPDATE_ORG', 'ADMIN', $id, null, $data);
            $_SESSION['flash_success'] = '組織機構已更新！';
        } else {
            $newId = Organization::create($data);
            AuditLogger::log('CREATE_ORG', 'ADMIN', $newId, null, $data);
            $_SESSION['flash_success'] = '新組織架構已建立！';
        }

        $this->redirect('/esg/admin/orgs');
    }

    public function logs(): void {
        Auth::requirePermission('audit_logs', 'read');

        $db = Database::getConnection();
        $module = $_GET['module'] ?? '';
        $action = $_GET['action'] ?? '';

        $sql = "SELECT * FROM `sys_audit_logs` WHERE 1=1";
        $params = [];

        if (!empty($module)) {
            $sql .= " AND `module` = :mod";
            $params[':mod'] = $module;
        }
        if (!empty($action)) {
            $sql .= " AND `action` = :act";
            $params[':act'] = $action;
        }

        $sql .= " ORDER BY `created_at` DESC LIMIT 200";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        $this->render('admin/logs', [
            'pageTitle' => '系統安全與操作稽核日誌 (Audit Log)',
            'activeNav' => 'admin_logs',
            'logs'      => $logs,
            'module'    => $module,
            'action'    => $action
        ]);
    }

    public function settings(): void {
        Auth::requireLogin();
        $user = Auth::user();
        if ($user['role_key'] !== 'super_admin') {
            http_response_code(403);
            die("<h1>403 Forbidden</h1><p>權限不足：系統全域參數與延展性配置僅限系統最高管理員 (Super Admin) 調整。<br><a href='/esg/'>返回首頁</a></p>");
        }

        $db = Database::getConnection();
        $rawSettings = $db->query("SELECT * FROM `sys_settings` ORDER BY `id` ASC")->fetchAll();
        $groupedSettings = [];
        foreach ($rawSettings as $s) {
            $groupedSettings[$s['setting_group']][] = $s;
        }

        $this->render('admin/settings', [
            'pageTitle'       => '系統可擴充與延展性全域參數配置',
            'activeNav'       => 'admin_settings',
            'groupedSettings' => $groupedSettings
        ]);
    }

    public function settingsStore(): void {
        Auth::requireLogin();
        $user = Auth::user();
        if ($user['role_key'] !== 'super_admin') {
            http_response_code(403);
            die("<h1>403 Forbidden</h1><p>權限不足：系統全域參數與延展性配置僅限系統最高管理員 (Super Admin) 調整。<br><a href='/esg/'>返回首頁</a></p>");
        }
        $this->checkCsrf();

        $db = Database::getConnection();
        foreach ($_POST['settings'] ?? [] as $key => $val) {
            if (!is_string($key) || !is_scalar($val) || strlen((string)$val) > 4000) {
                continue;
            }
            $stmt = $db->prepare("UPDATE `sys_settings` SET `setting_value` = :val WHERE `setting_key` = :key");
            $stmt->execute([':val' => trim($val), ':key' => $key]);
        }

        AuditLogger::log('UPDATE_SETTINGS', 'ADMIN', null, null, $_POST['settings']);
        $_SESSION['flash_success'] = '系統全域延展性參數已成功更新並生效！';
        $this->redirect('/esg/admin/settings');
    }
}
