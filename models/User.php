<?php
namespace App\Models;

use App\Database;
use PDO;

class User {
    public static function all(): array {
        $db = Database::getConnection();
        $stmt = $db->query("
            SELECT u.id, u.username, u.real_name, u.email, u.phone, u.org_id, u.role_id,
                   u.status, u.failed_attempts, u.locked_until, u.last_login_at, u.last_login_ip,
                   u.created_at, u.updated_at, r.role_name, r.role_key, o.org_name
            FROM `sys_users` u
            JOIN `sys_roles` r ON u.role_id = r.id
            JOIN `sys_organizations` o ON u.org_id = o.id
            ORDER BY u.id ASC
        ");
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT u.*, r.role_name, r.role_key, o.org_name
            FROM `sys_users` u
            JOIN `sys_roles` r ON u.role_id = r.id
            JOIN `sys_organizations` o ON u.org_id = o.id
            WHERE u.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `sys_users` (`username`, `password_hash`, `real_name`, `email`, `phone`, `org_id`, `role_id`, `status`, `created_at`)
            VALUES (:username, :password_hash, :real_name, :email, :phone, :org_id, :role_id, :status, NOW())
        ");
        $stmt->execute([
            ':username'      => $data['username'],
            ':password_hash' => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            ':real_name'     => $data['real_name'],
            ':email'         => $data['email'],
            ':phone'         => $data['phone'] ?? null,
            ':org_id'        => $data['org_id'],
            ':role_id'       => $data['role_id'],
            ':status'        => $data['status'] ?? 1
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $db = Database::getConnection();
        $fields = [
            '`real_name` = :real_name',
            '`email` = :email',
            '`phone` = :phone',
            '`org_id` = :org_id',
            '`role_id` = :role_id',
            '`status` = :status'
        ];
        $params = [
            ':real_name' => $data['real_name'],
            ':email'     => $data['email'],
            ':phone'     => $data['phone'] ?? null,
            ':org_id'    => $data['org_id'],
            ':role_id'   => $data['role_id'],
            ':status'    => $data['status'] ?? 1,
            ':id'        => $id
        ];

        if (!empty($data['password'])) {
            $fields[] = '`password_hash` = :password_hash';
            $params[':password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        $sql = "UPDATE `sys_users` SET " . implode(', ', $fields) . " WHERE `id` = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }

    public static function getRoles(): array {
        $db = Database::getConnection();
        return $db->query("SELECT * FROM `sys_roles` ORDER BY `id` ASC")->fetchAll();
    }
}
