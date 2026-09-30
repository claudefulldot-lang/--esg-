<?php
namespace App\Controllers;

use App\Auth;
use App\Controller;
use App\Helpers\AiAssistant;
use App\Helpers\AuditLogger;

class AiAssistantController extends Controller {
    public function settings(): void {
        $this->requireSuperAdmin();
        $settings = AiAssistant::settings();
        $this->render('admin/ai_assistant', [
            'pageTitle' => 'AI 客服小編設定',
            'activeNav' => 'admin_ai_assistant',
            'settings' => $settings,
            'allowedModels' => AiAssistant::allowedModels(),
            'maskedApiKey' => AiAssistant::maskedStoredKey(),
            'extraStyles' => ['/esg/assets/css/ai-assistant.css'],
            'extraScripts' => ['/esg/assets/js/ai-settings.js'],
        ]);
    }

    public function saveSettings(): void {
        $this->requireSuperAdmin();
        $this->checkCsrf();
        try {
            $model = trim((string)($_POST['openai_model'] ?? ''));
            $enabled = ($_POST['ai_assistant_enabled'] ?? '0') === '1';
            $apiKey = trim((string)($_POST['openai_api_key'] ?? ''));
            $clearKey = ($_POST['clear_api_key'] ?? '0') === '1';
            AiAssistant::saveSettings($enabled, $model, $apiKey !== '' ? $apiKey : null, $clearKey);
            AuditLogger::log('UPDATE_AI_SETTINGS', 'AI_ASSISTANT', null, null, [
                'enabled' => $enabled,
                'model' => $model,
                'api_key_changed' => $apiKey !== '' || $clearKey,
            ]);
            $_SESSION['flash_success'] = 'AI 客服設定已安全儲存。';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = $e->getMessage();
        }
        $this->redirect('/esg/admin/ai-assistant');
    }

    public function testConnection(): void {
        $user = $this->requireSuperAdmin();
        $this->checkCsrf();
        try {
            $payload = $this->jsonInput();
            $result = AiAssistant::testConnection(
                isset($payload['api_key']) ? (string)$payload['api_key'] : null,
                trim((string)($payload['model'] ?? '')),
                (int)$user['id']
            );
            AuditLogger::log('TEST_AI_CONNECTION', 'AI_ASSISTANT', null, null, [
                'model' => $result['model'],
                'latency_ms' => $result['latency_ms'],
                'success' => true,
            ]);
            $this->json(['success' => true, 'message' => 'OpenAI 連線測試成功。', 'data' => $result]);
        } catch (\Throwable $e) {
            AuditLogger::log('TEST_AI_CONNECTION', 'AI_ASSISTANT', null, null, ['success' => false]);
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function status(): void {
        Auth::requireLogin();
        $this->json(['success' => true, 'data' => AiAssistant::status()]);
    }

    public function chat(): void {
        Auth::requireLogin();
        $this->checkCsrf();
        try {
            $payload = $this->jsonInput();
            $result = AiAssistant::chat((string)($payload['message'] ?? ''), Auth::user() ?? []);
            $status = !empty($result['disconnected']) ? 423 : 200;
            $this->json($result, $status);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function requireSuperAdmin(): array {
        Auth::requireLogin();
        $user = Auth::user();
        if (($user['role_key'] ?? '') !== 'super_admin') {
            http_response_code(403);
            die("<h1>403 Forbidden</h1><p>AI 客服 API 設定僅限 Super Admin。<br><a href='/esg/'>返回首頁</a></p>");
        }
        return $user;
    }

    private function jsonInput(): array {
        $raw = file_get_contents('php://input');
        if ($raw === false || strlen($raw) > 12000) return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
