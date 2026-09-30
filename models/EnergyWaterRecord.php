<?php
namespace App\Models;

use App\Database;
use PDO;

class EnergyWaterRecord {
    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "
            SELECT ew.*, o.org_name, u.real_name AS submitter_name, app.real_name AS approver_name
            FROM `esg_energy_water_records` ew
            JOIN `sys_organizations` o ON ew.org_id = o.id
            JOIN `sys_users` u ON ew.user_id = u.id
            LEFT JOIN `sys_users` app ON ew.approver_id = app.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['year'])) {
            $sql .= " AND ew.record_year = :year";
            $params[':year'] = (int)$filters['year'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND ew.record_type = :type";
            $params[':type'] = $filters['type'];
        }
        if (!empty($filters['org_id'])) {
            $sql .= " AND ew.org_id = :org_id";
            $params[':org_id'] = (int)$filters['org_id'];
        }

        $sql .= " ORDER BY ew.record_year DESC, ew.record_month DESC, ew.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `esg_energy_water_records` 
            (`org_id`, `record_year`, `record_month`, `record_type`, `amount`, `unit`, `cod_val`, `bod_val`, `ss_val`, `notes`, `status`, `user_id`, `created_at`)
            VALUES (:org_id, :record_year, :record_month, :record_type, :amount, :unit, :cod_val, :bod_val, :ss_val, :notes, :status, :user_id, NOW())
        ");
        $stmt->execute([
            ':org_id'       => $data['org_id'],
            ':record_year'  => (int)$data['record_year'],
            ':record_month' => (int)$data['record_month'],
            ':record_type'  => $data['record_type'],
            ':amount'       => (float)$data['amount'],
            ':unit'         => $data['unit'],
            ':cod_val'      => !empty($data['cod_val']) ? (float)$data['cod_val'] : null,
            ':bod_val'      => !empty($data['bod_val']) ? (float)$data['bod_val'] : null,
            ':ss_val'       => !empty($data['ss_val']) ? (float)$data['ss_val'] : null,
            ':notes'        => $data['notes'] ?? null,
            ':status'       => $data['status'] ?? 'draft',
            ':user_id'      => $data['user_id']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function updateStatus(int $id, string $status, ?int $approverId = null, ?string $rejectReason = null): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE `esg_energy_water_records`
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
