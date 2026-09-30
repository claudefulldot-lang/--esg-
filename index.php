<?php
/**
 * ESG-Pro System - Single Entry Point
 * Enterprise ESG Sustainability Management & GHG Inventory System
 */

// Error reporting for development
$isDevelopment = (getenv('APP_ENV') ?: 'production') === 'development';
error_reporting(E_ALL);
ini_set('display_errors', $isDevelopment ? '1' : '0');
ini_set('log_errors', '1');

// Set standard timezone
date_default_timezone_set('Asia/Taipei');

// Autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Manual autoload fallback for core classes if composer hasn't re-dumped
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/controllers/',
        'App\\Models\\'      => __DIR__ . '/models/',
        'App\\Helpers\\'     => __DIR__ . '/helpers/',
        'App\\'              => __DIR__ . '/core/'
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

use App\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\GhgController;
use App\Controllers\EnvironmentController;
use App\Controllers\SocialController;
use App\Controllers\GovernanceController;
use App\Controllers\WorkflowController;
use App\Controllers\ReportController;
use App\Controllers\AdminController;
use App\Controllers\AttachmentController;
use App\Controllers\ManualController;
use App\Controllers\AiAssistantController;
use App\Helpers\Security;

Security::sendSecurityHeaders();

$router = new Router();

// Authentication
$router->get('/login', [AuthController::class, 'login']);
$router->post('/login', [AuthController::class, 'doLogin']);
$router->post('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/', [DashboardController::class, 'index']);

// Module 2: Environmental (GHG & Emission Factors)
$router->get('/ghg', [GhgController::class, 'index']);
$router->get('/ghg/create', [GhgController::class, 'create']);
$router->post('/ghg/create', [GhgController::class, 'store']);
$router->post('/ghg/delete', [GhgController::class, 'delete']);
$router->get('/factors', [GhgController::class, 'factors']);
$router->post('/factors/store', [GhgController::class, 'factorStore']);
$router->get('/api/factors', [GhgController::class, 'apiFactors']);

// Module 2: Water & Waste
$router->get('/environment/water', [EnvironmentController::class, 'water']);
$router->post('/environment/water/store', [EnvironmentController::class, 'waterStore']);
$router->get('/environment/waste', [EnvironmentController::class, 'waste']);
$router->post('/environment/waste/store', [EnvironmentController::class, 'wasteStore']);

// Module 3: Social (S)
$router->get('/social', [SocialController::class, 'index']);
$router->post('/social/store', [SocialController::class, 'store']);

// Module 4: Governance (G)
$router->get('/governance', [GovernanceController::class, 'index']);
$router->post('/governance/store', [GovernanceController::class, 'store']);

// Module 5: Workflow & Approval
$router->get('/workflow', [WorkflowController::class, 'index']);
$router->get('/workflow/detail', [WorkflowController::class, 'detail']);
$router->post('/workflow/approve', [WorkflowController::class, 'approve']);
$router->post('/workflow/reject', [WorkflowController::class, 'reject']);
$router->get('/attachments/download', [AttachmentController::class, 'download']);

// Built-in operation manual
$router->get('/manual', [ManualController::class, 'index']);

// Scoped AI operation support assistant
$router->get('/api/ai-assistant/status', [AiAssistantController::class, 'status']);
$router->post('/api/ai-assistant/chat', [AiAssistantController::class, 'chat']);

// Module 6: Reports & Exports
$router->get('/reports', [ReportController::class, 'index']);
$router->get('/reports/export_iso14064', [ReportController::class, 'exportIso14064']);
$router->get('/reports/export_gri', [ReportController::class, 'exportGri']);
$router->get('/reports/print_summary', [ReportController::class, 'printSummary']);

// Module 1: Administration & RBAC
$router->get('/admin/users', [AdminController::class, 'users']);
$router->post('/admin/users/store', [AdminController::class, 'userStore']);
$router->get('/admin/orgs', [AdminController::class, 'orgs']);
$router->post('/admin/orgs/store', [AdminController::class, 'orgStore']);
$router->get('/admin/logs', [AdminController::class, 'logs']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings/store', [AdminController::class, 'settingsStore']);
$router->get('/admin/ai-assistant', [AiAssistantController::class, 'settings']);
$router->post('/admin/ai-assistant/store', [AiAssistantController::class, 'saveSettings']);
$router->post('/admin/ai-assistant/test', [AiAssistantController::class, 'testConnection']);

// Dispatch Request
$router->dispatch();
