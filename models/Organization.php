<?php
namespace App\Models;

use App\Database;
use PDO;

class Organization {
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT o.*, p.org_name AS parent_name
            FROM `sys_organizations` o
            LEFT JOIN `sys_organizations` p ON o.parent_id = p.id
            ORDER BY o.sort_order ASC, o.id ASC
        ");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `sys_organizations` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function getPlants(): array {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT * FROM `sys_organizations` WHERE `org_type` = 'plant' ORDER BY `sort_order` ASC");
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `sys_organizations` (`org_code`, `org_name`, `org_type`, `parent_id`, `leader_name`, `contact_phone`, `contact_email`, `sort_order`)
            VALUES (:org_code, :org_name, :org_type, :parent_id, :leader_name, :contact_phone, :contact_email, :sort_order)
        ");
        $stmt->execute([
            ':org_code'      => $data['org_code'],
            ':org_name'      => $data['org_name'],
            ':org_type'      => $data['org_type'],
            ':parent_id'     => !empty($data['parent_id']) ? $data['parent_id'] : null,
            ':leader_name'   => $data['leader_name'] ?? null,
            ':contact_phone' => $data['contact_phone'] ?? null,
            ':contact_email' => $data['contact_email'] ?? null,
            ':sort_order'    => (int)($data['sort_order'] ?? 0)
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            UPDATE `sys_organizations`
            SET `org_code` = :org_code,
                `org_name` = :org_name,
                `org_type` = :org_type,
                `parent_id` = :parent_id,
                `leader_name` = :leader_name,
                `contact_phone` = :contact_phone,
                `contact_email` = :contact_email,
                `sort_order` = :sort_order
            WHERE `id` = :id
        ");
        return $stmt->execute([
            ':org_code'      => $data['org_code'],
            ':org_name'      => $data['org_name'],
            ':org_type'      => $data['org_type'],
            ':parent_id'     => !empty($data['parent_id']) ? $data['parent_id'] : null,
            ':leader_name'   => $data['leader_name'] ?? null,
            ':contact_phone' => $data['contact_phone'] ?? null,
            ':contact_email' => $data['contact_email'] ?? null,
            ':sort_order'    => (int)($data['sort_order'] ?? 0),
            ':id'            => $id
        ]);
    }
}
