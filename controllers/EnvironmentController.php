<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Models\EnergyWaterRecord;
use App\Models\WasteRecord;
use App\Models\Organization;
use App\Helpers\Uploader;
use App\Helpers\AuditLogger;

class EnvironmentController extends Controller {
    public function water(): void {
        Auth::requirePermission('environment', 'read');

        $filters = [
            'year'   => $_GET['year'] ?? date('Y'),
            'type'   => $_GET['type'] ?? '',
            'org_id' => $_GET['org_id'] ?? ''
        ];

        $records = EnergyWaterRecord::all(array_filter($filters));
        $plants = Organization::getPlants();

        $this->render('environment/water', [
            'pageTitle' => '水資源與放流水質管理',
            'activeNav' => 'env_water',
            'records'   => $records,
            'filters'   => $filters,
            'plants'    => $plants
        ]);
    }

    public function waterStore(): void {
        Auth::requirePermission('environment', 'create');
        $this->checkCsrf();

        $user = Auth::user();
        $orgId = (int)($_POST['org_id'] ?? 0);
        $year = (int)($_POST['record_year'] ?? 0);
        $month = (int)($_POST['record_month'] ?? 0);
        $type = $_POST['record_type'] ?? '';
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        $validTypes = ['tap_water', 'ground_water', 'recycled_water', 'wastewater', 'solar_power', 'green_electricity'];
        if (!$orgId || !Organization::find($orgId) || $year < 2000 || $year > 2200 || $month < 1 || $month > 12 ||
            !in_array($type, $validTypes, true) || $amount === false || $amount < 0) {
            $_SESSION['flash_error'] = '輸入資料格式或範圍不正確。';
            $this->redirect('/esg/environment/water');
            return;
        }
        $recordId = EnergyWaterRecord::create([
            'org_id'       => $orgId,
            'record_year'  => $year,
            'record_month' => $month,
            'record_type'  => $type,
            'amount'       => (float)$amount,
            'unit'         => $_POST['unit'] ?? '度',
            'cod_val'      => $_POST['cod_val'] ?? null,
            'bod_val'      => $_POST['bod_val'] ?? null,
            'ss_val'       => $_POST['ss_val'] ?? null,
            'notes'        => trim($_POST['notes'] ?? ''),
            'status'       => 'approved_final',
            'user_id'      => $user['id']
        ]);

        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            Uploader::upload($_FILES['attachment'], 'water', $recordId, $user['id']);
        }

        AuditLogger::log('CREATE_WATER_RECORD', 'WATER', $recordId);
        $_SESSION['flash_success'] = '水資源數據已成功登記！';
        $this->redirect('/esg/environment/water');
    }

    public function waste(): void {
        Auth::requirePermission('environment', 'read');

        $filters = [
            'year'     => $_GET['year'] ?? date('Y'),
            'category' => $_GET['category'] ?? '',
            'org_id'   => $_GET['org_id'] ?? ''
        ];

        $records = WasteRecord::all(array_filter($filters));
        $plants = Organization::getPlants();

        $this->render('environment/waste', [
            'pageTitle' => '事業廢棄物處置管理',
            'activeNav' => 'env_waste',
            'records'   => $records,
            'filters'   => $filters,
            'plants'    => $plants
        ]);
    }

    public function wasteStore(): void {
        Auth::requirePermission('environment', 'create');
        $this->checkCsrf();

        $user = Auth::user();
        $orgId = (int)($_POST['org_id'] ?? 0);
        $year = (int)($_POST['record_year'] ?? 0);
        $month = (int)($_POST['record_month'] ?? 0);
        $category = $_POST['waste_category'] ?? '';
        $method = $_POST['disposal_method'] ?? '';
        $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
        if (!$orgId || !Organization::find($orgId) || $year < 2000 || $year > 2200 || $month < 1 || $month > 12 ||
            !in_array($category, ['general_D', 'hazardous_C'], true) ||
            !in_array($method, ['incineration', 'landfill', 'physical', 'recycling'], true) ||
            $amount === false || $amount < 0 || trim($_POST['waste_name'] ?? '') === '') {
            $_SESSION['flash_error'] = '輸入資料格式或範圍不正確。';
            $this->redirect('/esg/environment/waste');
            return;
        }
        $recordId = WasteRecord::create([
            'org_id'          => $orgId,
            'record_year'     => $year,
            'record_month'    => $month,
            'waste_category'  => $category,
            'waste_name'      => trim($_POST['waste_name']),
            'amount'          => (float)$amount,
            'unit'            => $_POST['unit'] ?? 'ton',
            'disposal_method' => $method,
            'tracking_number' => trim($_POST['tracking_number'] ?? ''),
            'status'          => 'approved_final',
            'user_id'         => $user['id']
        ]);

        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            Uploader::upload($_FILES['attachment'], 'waste', $recordId, $user['id']);
        }

        AuditLogger::log('CREATE_WASTE_RECORD', 'WASTE', $recordId);
        $_SESSION['flash_success'] = '廢棄物處置清單已成功登記！';
        $this->redirect('/esg/environment/waste');
    }
}
