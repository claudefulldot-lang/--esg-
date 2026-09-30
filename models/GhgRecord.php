<?php
namespace App\Models;

use App\Database;
use App\Calculator;
use PDO;

class GhgRecord {
    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "
            SELECT r.*, 
                   o.org_name, 
                   f.fuel_name, f.activity_unit, f.total_factor_co2e, f.category AS factor_category,
                   u.real_name AS submitter_name,
                   app.real_name AS approver_name
            FROM `esg_ghg_records` r
            JOIN `sys_organizations` o ON r.org_id = o.id
            JOIN `esg_emission_factors` f ON r.factor_id = f.id
            JOIN `sys_users` u ON r.user_id = u.id
            LEFT JOIN `sys_users` app ON r.approver_id = app.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['year'])) {
            $sql .= " AND r.record_year = :year";
            $params[':year'] = (int)$filters['year'];
        }
        if (!empty($filters['month'])) {
            $sql .= " AND r.record_month = :month";
            $params[':month'] = (int)$filters['month'];
        }
        if (!empty($filters['scope'])) {
            $sql .= " AND r.scope = :scope";
            $params[':scope'] = (int)$filters['scope'];
        }
        if (!empty($filters['org_id'])) {
            $sql .= " AND r.org_id = :org_id";
            $params[':org_id'] = (int)$filters['org_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND r.user_id = :user_id";
            $params[':user_id'] = (int)$filters['user_id'];
        }

        $sql .= " ORDER BY r.record_year DESC, r.record_month DESC, r.id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT r.*, 
                   o.org_name, 
                   f.fuel_name, f.activity_unit, f.total_factor_co2e, f.category AS factor_category,
                   u.real_name AS submitter_name,
                   app.real_name AS approver_name
            FROM `esg_ghg_records` r
            JOIN `sys_organizations` o ON r.org_id = o.id
            JOIN `esg_emission_factors` f ON r.factor_id = f.id
            JOIN `sys_users` u ON r.user_id = u.id
            LEFT JOIN `sys_users` app ON r.approver_id = app.id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();

        // Calculate tCO2e using Calculator
        $factor = EmissionFactor::find((int)$data['factor_id']);
        $factorValue = $factor ? (float)$factor['total_factor_co2e'] : 0.0;
        $calculatedCo2e = Calculator::calculateGhg((float)$data['activity_amount'], $factorValue);

        $stmt = $db->prepare("
            INSERT INTO `esg_ghg_records` 
            (`org_id`, `record_year`, `record_month`, `scope`, `source_type`, `factor_id`, `activity_amount`, `calculated_co2e`, `status`, `user_id`, `created_at`)
            VALUES (:org_id, :record_year, :record_month, :scope, :source_type, :factor_id, :activity_amount, :calculated_co2e, :status, :user_id, NOW())
        ");
        $stmt->execute([
            ':org_id'          => $data['org_id'],
            ':record_year'     => (int)$data['record_year'],
            ':record_month'    => (int)$data['record_month'],
            ':scope'           => (int)$data['scope'],
            ':source_type'     => $data['source_type'],
            ':factor_id'       => (int)$data['factor_id'],
            ':activity_amount' => (float)$data['activity_amount'],
            ':calculated_co2e' => $calculatedCo2e,
            ':status'          => $data['status'] ?? 'draft',
            ':user_id'         => $data['user_id']
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();

        $factor = EmissionFactor::find((int)$data['factor_id']);
        $factorValue = $factor ? (float)$factor['total_factor_co2e'] : 0.0;
        $calculatedCo2e = Calculator::calculateGhg((float)$data['activity_amount'], $factorValue);

        $stmt = $db->prepare("
            UPDATE `esg_ghg_records`
            SET `org_id` = :org_id,
                `record_year` = :record_year,
                `record_month` = :record_month,
                `scope` = :scope,
                `source_type` = :source_type,
                `factor_id` = :factor_id,
                `activity_amount` = :activity_amount,
                `calculated_co2e` = :calculated_co2e,
                `status` = :status
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':org_id'          => $data['org_id'],
            ':record_year'     => (int)$data['record_year'],
            ':record_month'    => (int)$data['record_month'],
            ':scope'           => (int)$data['scope'],
            ':source_type'     => $data['source_type'],
            ':factor_id'       => (int)$data['factor_id'],
            ':activity_amount' => (float)$data['activity_amount'],
            ':calculated_co2e' => $calculatedCo2e,
            ':status'          => $data['status'] ?? 'draft',
            ':id'              => $id
        ]);
    }

    public static function updateStatus(int $id, string $status, ?int $approverId = null, ?string $rejectReason = null, array $allowedCurrentStatuses = []): bool {
        $db = Database::getConnection();
        $sql = "
            UPDATE `esg_ghg_records`
            SET `status` = :status,
                `approver_id` = :approver_id,
                `reject_reason` = :reject_reason
            WHERE `id` = :id
        ";
        $params = [
            ':status'        => $status,
            ':approver_id'   => $approverId,
            ':reject_reason' => $rejectReason,
            ':id'            => $id
        ];
        if ($allowedCurrentStatuses) {
            $placeholders = [];
            foreach (array_values($allowedCurrentStatuses) as $i => $currentStatus) {
                $key = ':current_status_' . $i;
                $placeholders[] = $key;
                $params[$key] = $currentStatus;
            }
            $sql .= ' AND `status` IN (' . implode(', ', $placeholders) . ')';
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() === 1;
    }

    public static function delete(int $id, ?int $ownerId = null): bool {
        $db = Database::getConnection();
        $sql = "DELETE FROM `esg_ghg_records` WHERE `id` = :id AND `status` IN ('draft', 'rejected')";
        $params = [':id' => $id];
        if ($ownerId !== null) {
            $sql .= ' AND `user_id` = :owner_id';
            $params[':owner_id'] = $ownerId;
        }
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() === 1;
    }

    /**
     * Aggregation for Dashboard
     */
    public static function getScopeTotals(int $year): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT `scope`, SUM(`calculated_co2e`) as total_co2e
            FROM `esg_ghg_records`
            WHERE `record_year` = :year AND `status` = 'approved_final'
            GROUP BY `scope`
        ");
        $stmt->execute([':year' => $year]);
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return [
            'scope1' => (float)($rows[1] ?? 0.0),
            'scope2' => (float)($rows[2] ?? 0.0),
            'scope3' => (float)($rows[3] ?? 0.0),
            'total'  => (float)(($rows[1] ?? 0.0) + ($rows[2] ?? 0.0) + ($rows[3] ?? 0.0))
        ];
    }

    public static function getMonthlyTrends(int $year): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT `record_month`, `scope`, SUM(`calculated_co2e`) as month_co2e
            FROM `esg_ghg_records`
            WHERE `record_year` = :year AND `status` = 'approved_final'
            GROUP BY `record_month`, `scope`
            ORDER BY `record_month` ASC
        ");
        $stmt->execute([':year' => $year]);
        $rows = $stmt->fetchAll();

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[$m] = ['scope1' => 0.0, 'scope2' => 0.0, 'scope3' => 0.0, 'total' => 0.0];
        }
        foreach ($rows as $row) {
            $m = (int)$row['record_month'];
            $s = 'scope' . (int)$row['scope'];
            $val = (float)$row['month_co2e'];
            $result[$m][$s] = $val;
            $result[$m]['total'] += $val;
        }
        return $result;
    }

    public static function getPlantEmissions(int $year): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT o.org_name, SUM(r.calculated_co2e) as total_co2e
            FROM `esg_ghg_records` r
            JOIN `sys_organizations` o ON r.org_id = o.id
            WHERE r.record_year = :year AND r.status = 'approved_final'
            GROUP BY r.org_id, o.org_name
            ORDER BY total_co2e DESC
        ");
        $stmt->execute([':year' => $year]);
        return $stmt->fetchAll();
    }
}
