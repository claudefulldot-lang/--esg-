<?php
namespace App\Helpers;

use App\Database;

class Uploader {
    /**
     * Upload an attachment and save to esg_attachments table
     */
    public static function upload(array $file, string $recordType, int $recordId, int $userId): array {
        $appConfig = require __DIR__ . '/../config/app.php';

        if (!in_array($recordType, ['ghg', 'water', 'waste', 'social', 'gov'], true) || $recordId <= 0 || $userId <= 0) {
            return ['success' => false, 'message' => '附件關聯資料無效'];
        }

        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => '無效的上傳檔案參數'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE   => '檔案大小超過伺服器 php.ini 限制',
                UPLOAD_ERR_FORM_SIZE  => '檔案大小超過 HTML 表單限制',
                UPLOAD_ERR_PARTIAL    => '檔案僅部分上傳',
                UPLOAD_ERR_NO_FILE    => '未選擇上傳檔案',
                UPLOAD_ERR_NO_TMP_DIR => '缺少臨時資料夾',
                UPLOAD_ERR_CANT_WRITE => '檔案寫入失敗',
                UPLOAD_ERR_EXTENSION  => '上傳被 PHP 擴充套件中斷',
            ];
            return ['success' => false, 'message' => $errors[$file['error']] ?? '上傳發生未預期錯誤'];
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => '檔案來源驗證失敗'];
        }

        if ($file['size'] > $appConfig['max_upload_size']) {
            return ['success' => false, 'message' => '檔案大小不得超過 ' . ($appConfig['max_upload_size'] / 1024 / 1024) . ' MB'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $appConfig['allowed_upload_mimes'])) {
            return ['success' => false, 'message' => '不支援的檔案格式: ' . htmlspecialchars($mime)];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $appConfig['allowed_upload_exts'])) {
            return ['success' => false, 'message' => '副檔名不被允許: ' . htmlspecialchars($ext)];
        }

        // Validate the internal structure of formats commonly abused for malware delivery.
        if (in_array($ext, ['jpg', 'jpeg', 'png'], true) && @getimagesize($file['tmp_name']) === false) {
            return ['success' => false, 'message' => '圖片內容驗證失敗'];
        }
        if ($ext === 'pdf') {
            $handle = fopen($file['tmp_name'], 'rb');
            $signature = $handle ? fread($handle, 5) : '';
            if ($handle) fclose($handle);
            if ($signature !== '%PDF-') {
                return ['success' => false, 'message' => 'PDF 檔案結構無效'];
            }
        }
        if (in_array($ext, ['xlsx', 'docx'], true) && class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($file['tmp_name']) !== true || $zip->numFiles > 2000) {
                return ['success' => false, 'message' => 'Office 檔案結構無效或項目過多'];
            }
            $totalUncompressed = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $totalUncompressed += (int)($stat['size'] ?? 0);
                if ($totalUncompressed > 200 * 1024 * 1024) {
                    $zip->close();
                    return ['success' => false, 'message' => 'Office 檔案解壓後大小異常'];
                }
            }
            $zip->close();
        }

        // Optional antivirus integration. Configure an absolute clamscan path in production.
        $scanner = getenv('ESG_CLAMSCAN_PATH') ?: '';
        if ($scanner !== '') {
            if (!is_file($scanner) || !is_executable($scanner) || !function_exists('exec')) {
                return ['success' => false, 'message' => '防毒掃描器設定無效，已拒絕上傳'];
            }
            $output = [];
            $exitCode = 2;
            exec(escapeshellarg($scanner) . ' --no-summary ' . escapeshellarg($file['tmp_name']), $output, $exitCode);
            if ($exitCode !== 0) {
                return ['success' => false, 'message' => $exitCode === 1 ? '檔案疑似含惡意程式，已拒絕上傳' : '防毒掃描失敗，已拒絕上傳'];
            }
        }

        $yearMonth = date('Y/m');
        $uploadDir = __DIR__ . '/../uploads/' . $yearMonth;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uniqueFileName = bin2hex(random_bytes(16)) . '.' . $ext;
        $relativeStoragePath = 'uploads/' . $yearMonth . '/' . $uniqueFileName;
        $targetFullPath = __DIR__ . '/../' . $relativeStoragePath;

        if (!move_uploaded_file($file['tmp_name'], $targetFullPath)) {
            return ['success' => false, 'message' => '伺服器移動檔案失敗'];
        }

        // Save record to DB
        $db = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO `esg_attachments` (`record_type`, `record_id`, `file_name`, `file_path`, `file_size`, `file_ext`, `uploaded_by`, `uploaded_at`)
            VALUES (:record_type, :record_id, :file_name, :file_path, :file_size, :file_ext, :uploaded_by, NOW())
        ");
        try {
            $stmt->execute([
                ':record_type' => $recordType,
                ':record_id'   => $recordId,
                ':file_name'   => mb_substr(basename($file['name']), 0, 255),
                ':file_path'   => $relativeStoragePath,
                ':file_size'   => $file['size'],
                ':file_ext'    => $ext,
                ':uploaded_by' => $userId
            ]);
        } catch (\Throwable $e) {
            @unlink($targetFullPath);
            throw $e;
        }

        $attachmentId = (int)$db->lastInsertId();

        AuditLogger::log('UPLOAD_ATTACHMENT', $recordType, $recordId, null, [
            'attachment_id' => $attachmentId,
            'file_name'     => $file['name']
        ]);

        return [
            'success'       => true,
            'attachment_id' => $attachmentId,
            'file_name'     => mb_substr(basename($file['name']), 0, 255),
            'file_path'     => $relativeStoragePath
        ];
    }
}
