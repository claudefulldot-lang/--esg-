<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Models\WorkflowTask;
use App\Models\GhgRecord;
use App\Models\EnergyWaterRecord;
use App\Models\WasteRecord;
use App\Models\Attachment;
use App\Helpers\AuditLogger;
use App\Database;

class WorkflowController extends Controller {
    public function index(): void {
        Auth::requirePermission('workflow', 'read');

        $user = Auth::user();
        $status = $_GET['status'] ?? '';
        $filters = [];
        if (!empty($status)) {
            $filters['status'] = $status;
        }

        // Submitter sees their tasks; approver/admin sees all
        if ($user['role_key'] === 'submitter') {
            $filters['user_id'] = $user['id'];
        }

        $tasks = WorkflowTask::all($filters);

        // Also fetch submitted GHG records awaiting approval
        $pendingGhgRecords = $user['role_key'] === 'submitter' ? [] : GhgRecord::all(['status' => 'submitted']);
        $l1GhgRecords = $user['role_key'] === 'submitter' ? [] : GhgRecord::all(['status' => 'approved_l1']);

        $this->render('workflow/index', [
            'pageTitle'         => '審批簽核與待辦中心',
            'activeNav'         => 'workflow',
            'tasks'             => $tasks,
            'pendingGhgRecords' => $pendingGhgRecords,
            'l1GhgRecords'      => $l1GhgRecords,
            'statusFilter'      => $status
        ]);
    }

    public function detail(): void {
        Auth::requirePermission('workflow', 'read');

        $type = $_GET['type'] ?? 'ghg';
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $this->redirect('/esg/workflow');
            return;
        }

        $record = null;
        if ($type === 'ghg') {
            $record = GhgRecord::find($id);
        }

        if (!$record) {
            $_SESSION['flash_error'] = '找不到該筆業務數據';
            $this->redirect('/esg/workflow');
            return;
        }

        $user = Auth::user();
        if ($user['role_key'] === 'submitter' && (int)$record['user_id'] !== (int)$user['id']) {
            http_response_code(403);
            die('<h1>403 Forbidden</h1><p>您無權檢視其他填報人員的資料。</p>');
        }

        $attachments = Attachment::getByRecord($type, $id);
        $logs = WorkflowTask::getLogs($type, $id);

        $this->render('workflow/detail', [
            'pageTitle'   => '活動數據審核與歷程檢視',
            'activeNav'   => 'workflow',
            'type'        => $type,
            'record'      => $record,
            'attachments' => $attachments,
            'logs'        => $logs
        ]);
    }

    public function approve(): void {
        $type = $_POST['type'] ?? 'ghg';
        $id = (int)($_POST['id'] ?? 0);
        $comment = trim($_POST['comment'] ?? '審核無誤，核准通過');
        $level = $_POST['level'] ?? 'l1'; // l1 (初審) or final (最終核准)

        if ($type !== 'ghg' || $id <= 0 || !in_array($level, ['l1', 'final'], true)) {
            http_response_code(400);
            die('無效的簽核請求。');
        }

        if ($level === 'final') {
            Auth::requirePermission('workflow', 'approve_final');
            $newStatus = 'approved_final';
            $action = 'approve_final';
        } else {
            Auth::requirePermission('workflow', 'approve_l1');
            $newStatus = 'approved_l1';
            $action = 'approve_l1';
        }

        $this->checkCsrf();
        $user = Auth::user();
        $allowedCurrent = $level === 'final' ? ['approved_l1'] : ['submitted'];

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            if (!GhgRecord::updateStatus($id, $newStatus, $user['id'], null, $allowedCurrent)) {
                throw new \RuntimeException('資料狀態已變更，請重新整理後再操作。');
            }
            WorkflowTask::logAction(null, $type, $id, $action, $user['id'], mb_substr($comment, 0, 1000));
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/esg/workflow/detail?type=' . $type . '&id=' . $id);
            return;
        }
        $_SESSION['flash_success'] = '審核成功，已更新狀態為: ' . ($newStatus === 'approved_final' ? '最終核准並封存' : '初審通過');
        $this->redirect('/esg/workflow/detail?type=' . $type . '&id=' . $id);
    }

    public function reject(): void {
        Auth::requirePermission('workflow', 'reject');
        $this->checkCsrf();
        $user = Auth::user();

        $type = $_POST['type'] ?? 'ghg';
        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reject_reason'] ?? '');

        if ($type !== 'ghg' || $id <= 0 || empty($reason)) {
            $_SESSION['flash_error'] = '退回修正必須填寫退件原因！';
            $this->redirect('/esg/workflow/detail?type=' . $type . '&id=' . $id);
            return;
        }

        $db = Database::getConnection();
        try {
            $db->beginTransaction();
            if (!GhgRecord::updateStatus($id, 'rejected', $user['id'], mb_substr($reason, 0, 2000), ['submitted', 'approved_l1'])) {
                throw new \RuntimeException('資料狀態已變更，無法退回。');
            }
            WorkflowTask::logAction(null, $type, $id, 'reject', $user['id'], '退回修正: ' . mb_substr($reason, 0, 1000));
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['flash_error'] = $e->getMessage();
            $this->redirect('/esg/workflow/detail?type=' . $type . '&id=' . $id);
            return;
        }
        $_SESSION['flash_warning'] = '已退回該筆資料供填報人修正。';
        $this->redirect('/esg/workflow/detail?type=' . $type . '&id=' . $id);
    }
}
