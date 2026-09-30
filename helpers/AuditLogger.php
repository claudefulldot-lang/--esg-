<?php
namespace App\Helpers;

use App\Database;
use PDO;

class AuditLogger {
    private static function redact(mixed $value): mixed {
        if (!is_array($value)) return $value;
        $result = [];
        foreach ($value as $key => $item) {
            $normalized = strtolower((string)$key);
            if (preg_match('/password|passwd|smtp_pass|secret|token|authorization|cookie|api[_-]?key/', $normalized)) {
                $result[$key] = '[REDACTED]';
            } else {
                $result[$key] = self::redact($item);
            }
        }
        return $result;
    }
    /**
     * Log an action to sys_audit_logs
     */
    public static function log(string $action, string $module, ?int $recordId = null, $oldValues = null, $newValues = null): void {
        try {
            $db = Database::getConnection();
            $userId = $_SESSION['esg_user']['id'] ?? null;
            $username = $_SESSION['esg_user']['username'] ?? 'GUEST';
            $ip = Security::getClientIp();

            $oldValues = self::redact($oldValues);
            $newValues = self::redact($newValues);
            $oldJson = $oldValues !== null ? (is_string($oldValues) ? $oldValues : json_encode($oldValues, JSON_UNESCAPED_UNICODE)) : null;
            $newJson = $newValues !== null ? (is_string($newValues) ? $newValues : json_encode($newValues, JSON_UNESCAPED_UNICODE)) : null;

            $stmt = $db->prepare("
                INSERT INTO `sys_audit_logs` (`user_id`, `username`, `action`, `module`, `record_id`, `ip_address`, `old_values`, `new_values`, `created_at`)
                VALUES (:user_id, :username, :action, :module, :record_id, :ip_address, :old_values, :new_values, NOW())
            ");
            $stmt->execute([
                ':user_id'    => $userId,
                ':username'   => $username,
                ':action'     => $action,
                ':module'     => $module,
                ':record_id'  => $recordId,
                ':ip_address' => $ip,
                ':old_values' => $oldJson,
                ':new_values' => $newJson
            ]);
        } catch (\Exception $e) {
            // Log silently so as not to break business transaction
            error_log("Audit log failed: " . $e->getMessage());
        }
    }
}
