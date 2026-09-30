<?php
namespace App\Models;

use App\Database;
use PDO;

class GovernanceRecord {
    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "
            SELECT g.*, o.org_name, u.real_name AS submitter_name, app.real_name AS approver_name
            FROM `esg_governance_records` g
            JOIN `sys_organizations` o ON g.org_id = o.id
            JOIN `sys_users` u ON g.user_id = u.id
            LEFT JOIN `sys_users` app ON g.approver_id = app.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['year'])) {
            $sql .= " AND g.record_year = :year";
            $params[':year'] = (int)$filters['year'];
        }
        if (!empty($filters['category'])) {
            $sql .= " AND g.metric_category = :cat";
            $params[':cat'] = $filters['category'];
        }

        $sql .= " ORDER BY g.record_year DESC, g.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function createOrUpdate(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM `esg_governance_records` WHERE org_id = :org_id AND record_year = :year AND metric_category = :cat LIMIT 1");
        $stmt->execute([
            ':org_id' => $data['org_id'],
            ':year'   => (int)$data['record_year'],
            ':cat'    => $data['metric_category']
        ]);
        $existing = $stmt->fetch();

        $jsonStr = is_string($data['data_json']) ? $data['data_json'] : json_encode($data['data_json'], JSON_UNESCAPED_UNICODE);

        if ($existing) {
            $update = $db->prepare("
                UPDATE `esg_governance_records`
                SET `data_json` = :json, `status` = :status, `user_id` = :user_id, `updated_at` = NOW()
                WHERE `id` = :id
            ");
            $update->execute([
                ':json'    => $jsonStr,
                ':status'  => $data['status'] ?? 'draft',
                ':user_id' => $data['user_id'],
                ':id'      => $existing['id']
            ]);
            return (int)$existing['id'];
        } else {
            $insert = $db->prepare("
                INSERT INTO `esg_governance_records` 
                (`org_id`, `record_year`, `record_quarter`, `metric_category`, `data_json`, `status`, `user_id`, `created_at`)
                VALUES (:org_id, :record_year, :record_quarter, :metric_category, :data_json, :status, :user_id, NOW())
            ");
            $insert->execute([
                ':org_id'          => $data['org_id'],
                ':record_year'     => (int)$data['record_year'],
                ':record_quarter'  => (int)($data['record_quarter'] ?? 0),
                ':metric_category' => $data['metric_category'],
                ':data_json'       => $jsonStr,
                ':status'          => $data['status'] ?? 'draft',
                ':user_id'         => $data['user_id']
            ]);
            return (int)$db->lastInsertId();
        }
    }
}
