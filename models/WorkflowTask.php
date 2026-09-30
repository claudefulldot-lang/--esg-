<?php
namespace App\Models;

use App\Database;
use App\Helpers\AuditLogger;
use PDO;

class WorkflowTask {
    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "
            SELECT t.*, o.org_name, u.real_name AS assigned_user_name
            FROM `esg_workflow_tasks` t
            JOIN `sys_organizations` o ON t.org_id = o.id
            JOIN `sys_users` u ON t.assigned_user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND t.assigned_user_id = :user_id";
            $params[':user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['module_type'])) {
            $sql .= " AND t.module_type = :mod";
            $params[':mod'] = $filters['module_type'];
        }

        $sql .= " ORDER BY t.due_date ASC, t.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT t.*, o.org_name, u.real_name AS assigned_user_name, u.email AS assigned_user_email
            FROM `esg_workflow_tasks` t
            JOIN `sys_organizations` o ON t.org_id = o.id
            JOIN `sys_users` u ON t.assigned_user_id = u.id
            WHERE t.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function logAction(?int $taskId, string $recordType, int $recordId, string $action, int $approverId, ?string $comments = null): void {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `esg_workflow_logs` (`task_id`, `record_type`, `record_id`, `action`, `approver_id`, `comments`, `created_at`)
            VALUES (:task_id, :record_type, :record_id, :action, :approver_id, :comments, NOW())
        ");
        $stmt->execute([
            ':task_id'     => $taskId,
            ':record_type' => $recordType,
            ':record_id'   => $recordId,
            ':action'      => $action,
            ':approver_id' => $approverId,
            ':comments'    => $comments
        ]);

        AuditLogger::log('WORKFLOW_' . strtoupper($action), $recordType, $recordId, null, [
            'task_id'  => $taskId,
            'comments' => $comments
        ]);
    }

    public static function getLogs(string $recordType, int $recordId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT l.*, u.real_name AS approver_name, r.role_name
            FROM `esg_workflow_logs` l
            JOIN `sys_users` u ON l.approver_id = u.id
            JOIN `sys_roles` r ON u.role_id = r.id
            WHERE l.record_type = :record_type AND l.record_id = :record_id
            ORDER BY l.created_at ASC
        ");
        $stmt->execute([
            ':record_type' => $recordType,
            ':record_id'   => $recordId
        ]);
        return $stmt->fetchAll();
    }
}
