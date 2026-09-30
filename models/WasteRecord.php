<?php
namespace App\Models;

use App\Database;
use PDO;

class WasteRecord {
    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "
            SELECT w.*, o.org_name, u.real_name AS submitter_name, app.real_name AS approver_name
            FROM `esg_waste_records` w
            JOIN `sys_organizations` o ON w.org_id = o.id
            JOIN `sys_users` u ON w.user_id = u.id
            LEFT JOIN `sys_users` app ON w.approver_id = app.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['year'])) {
            $sql .= " AND w.record_year = :year";
            $params[':year'] = (int)$filters['year'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND w.waste_category = :cat";
            $params[':cat'] = $filters['category'];
        }
        if (!empty($filters['org_id'])) {
            $sql .= " AND w.org_id = :org_id";
            $params[':org_id'] = (int)$filters['org_id'];
        }

        $sql .= " ORDER BY w.record_year DESC, w.record_month DESC, w.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `esg_waste_records` 
            (`org_id`, `record_year`, `record_month`, `waste_category`, `waste_name`, `amount`, `unit`, `disposal_method`, `tracking_number`, `status`, `user_id`, `created_at`)
            VALUES (:org_id, :record_year, :record_month, :waste_category, :waste_name, :amount, :unit, :disposal_method, :tracking_number, :status, :user_id, NOW())
        ");
        $stmt->execute([
            ':org_id'          => $data['org_id'],
            ':record_year'     => (int)$data['record_year'],
            ':record_month'    => (int)$data['record_month'],
            ':waste_category'  => $data['waste_category'],
            ':waste_name'      => $data['waste_name'],
            ':amount'          => (float)$data['amount'],
            ':unit'            => $data['unit'] ?? 'ton',
            ':disposal_method' => $data['disposal_method'],
            ':tracking_number' => $data['tracking_number'] ?? null,
            ':status'          => $data['status'] ?? 'draft',
            ':user_id'         => $data['user_id']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function updateStatus(int $id, string $status, ?int $approverId = null, ?string $rejectReason = null): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE `esg_waste_records`
            SET `status` = :status, `approver_id` = :approver_id, `reject_reason` = :reject_reason
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':status'        => $status,
            ':approver_id'   => $approverId,
            ':reject_reason' => $rejectReason,
            ':id'            => $id
        ]);
    }
}
