<?php
namespace App\Controllers;

use App\Auth;
use App\Controller;
use App\Models\Attachment;
use App\Models\GhgRecord;

class AttachmentController extends Controller {
    public function download(): void {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        $attachment = $id > 0 ? Attachment::find($id) : null;
        if (!$attachment) {
            http_response_code(404);
            die('找不到附件。');
        }

        $user = Auth::user();
        $allowed = false;
        if ($attachment['record_type'] === 'ghg') {
            $record = GhgRecord::find((int)$attachment['record_id']);
            $allowed = $record && (
                (int)$record['user_id'] === (int)$user['id'] ||
                Auth::can('workflow', 'approve_l1') || Auth::can('workflow', 'approve_final') ||
                Auth::can('ghg', 'read') && $user['role_key'] !== 'submitter'
            );
        } elseif (in_array($attachment['record_type'], ['water', 'waste'], true)) {
            $allowed = Auth::can('environment', 'read');
        }

        if (!$allowed) {
            http_response_code(403);
            die('您無權下載此附件。');
        }

        $uploadRoot = realpath(__DIR__ . '/../uploads');
        $path = realpath(__DIR__ . '/../' . $attachment['file_path']);
        if (!$uploadRoot || !$path || !str_starts_with($path, $uploadRoot . DIRECTORY_SEPARATOR) || !is_file($path)) {
            http_response_code(404);
            die('附件檔案不存在。');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';
        $downloadName = str_replace(["\r", "\n", '"'], '', basename($attachment['file_name']));
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header("Content-Disposition: attachment; filename*=UTF-8''" . rawurlencode($downloadName));
        header('Cache-Control: private, no-store, max-age=0');
        readfile($path);
        exit;
    }
}
