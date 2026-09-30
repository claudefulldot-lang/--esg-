<?php
namespace App\Models;

use App\Database;
use PDO;

class EmissionFactor {
    public static function all(array $filters = []): array {
        $db = Database::getConnection();
        $sql = "SELECT * FROM `esg_emission_factors` WHERE 1=1";
        $params = [];

        if (!empty($filters['category'])) {
            $sql .= " AND `category` = :category";
            $params[':category'] = $filters['category'];
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND `is_active` = :is_active";
            $params[':is_active'] = (int)$filters['is_active'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND `applicable_year` = :year";
            $params[':year'] = (int)$filters['year'];
        }

        $sql .= " ORDER BY `category` ASC, `id` ASC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `esg_emission_factors` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getActiveByCategory(string $category): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `esg_emission_factors` WHERE `category` = :category AND `is_active` = 1 ORDER BY `fuel_name` ASC");
        $stmt->execute([':category' => $category]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `esg_emission_factors` 
            (`category`, `fuel_name`, `activity_unit`, `co2_factor`, `ch4_factor`, `n2o_factor`, `total_factor_co2e`, `source_org`, `applicable_year`, `is_active`, `created_by`, `created_at`)
            VALUES (:category, :fuel_name, :activity_unit, :co2_factor, :ch4_factor, :n2o_factor, :total_factor_co2e, :source_org, :applicable_year, :is_active, :created_by, NOW())
        ");
        $stmt->execute([
            ':category'          => $data['category'],
            ':fuel_name'         => $data['fuel_name'],
            ':activity_unit'     => $data['activity_unit'],
            ':co2_factor'        => (float)($data['co2_factor'] ?? 0),
            ':ch4_factor'        => (float)($data['ch4_factor'] ?? 0),
            ':n2o_factor'        => (float)($data['n2o_factor'] ?? 0),
            ':total_factor_co2e' => (float)$data['total_factor_co2e'],
            ':source_org'        => $data['source_org'],
            ':applicable_year'   => (int)($data['applicable_year'] ?? date('Y')),
            ':is_active'         => (int)($data['is_active'] ?? 1),
            ':created_by'        => $data['created_by'] ?? null
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE `esg_emission_factors`
            SET `category` = :category,
                `fuel_name` = :fuel_name,
                `activity_unit` = :activity_unit,
                `co2_factor` = :co2_factor,
                `ch4_factor` = :ch4_factor,
                `n2o_factor` = :n2o_factor,
                `total_factor_co2e` = :total_factor_co2e,
                `source_org` = :source_org,
                `applicable_year` = :applicable_year,
                `is_active` = :is_active
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':category'          => $data['category'],
            ':fuel_name'         => $data['fuel_name'],
            ':activity_unit'     => $data['activity_unit'],
            ':co2_factor'        => (float)($data['co2_factor'] ?? 0),
            ':ch4_factor'        => (float)($data['ch4_factor'] ?? 0),
            ':n2o_factor'        => (float)($data['n2o_factor'] ?? 0),
            ':total_factor_co2e' => (float)$data['total_factor_co2e'],
            ':source_org'        => $data['source_org'],
            ':applicable_year'   => (int)($data['applicable_year'] ?? date('Y')),
            ':is_active'         => (int)($data['is_active'] ?? 1),
            ':id'                => $id
        ]);
    }
}
