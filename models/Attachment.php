<?php
namespace App\Models;

use App\Database;
use PDO;

class Attachment {
    public static function getByRecord(string $recordType, int $recordId): array {
        $db = Database::getConnection();
        $stmt = $db->prepare("
            SELECT a.*, u.real_name AS uploader_name
            FROM `esg_attachments` a
            JOIN `sys_users` u ON a.uploaded_by = u.id
            WHERE a.record_type = :record_type AND a.record_id = :record_id
            ORDER BY a.uploaded_at DESC
        ");
        $stmt->execute([
            ':record_type' => $recordType,
            ':record_id'   => $recordId
        ]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM `esg_attachments` WHERE `id` = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
