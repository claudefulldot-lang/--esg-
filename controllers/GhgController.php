<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Models\GhgRecord;
use App\Models\EmissionFactor;
use App\Models\Organization;
use App\Models\WorkflowTask;
use App\Helpers\Uploader;
use App\Helpers\AuditLogger;

class GhgController extends Controller {
    public function index(): void {
        Auth::requirePermission('ghg', 'read');

        $filters = [
            'year'   => $_GET['year'] ?? date('Y'),
            'month'  => $_GET['month'] ?? '',
            'scope'  => $_GET['scope'] ?? '',
            'org_id' => $_GET['org_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $user = Auth::user();
        if ($user['role_key'] === 'submitter') {
            $filters['user_id'] = $user['id'];
        }

        $records = GhgRecord::all(array_filter($filters));
        $plants = Organization::getPlants();

        $this->render('ghg/index', [
            'pageTitle' => '溫室氣體碳盤查 (GHG Inventory)',
            'activeNav' => 'ghg_list',
            'records'   => $records,
            'filters'   => $filters,
            'plants'    => $plants
        ]);
    }

    public function create(): void {
        Auth::requirePermission('ghg', 'create');

        $plants = Organization::getPlants();
        $factors = EmissionFactor::all(['is_active' => 1]);

        $this->render('ghg/create', [
            'pageTitle' => '新增碳盤查活動數據',
            'activeNav' => 'ghg_create',
            'plants'    => $plants,
            'factors'   => $factors
        ]);
    }

    public function store(): void {
        Auth::requirePermission('ghg', 'create');
        $this->checkCsrf();

        $user = Auth::user();
        $orgId = (int)$_POST['org_id'];
        $year = (int)$_POST['record_year'];
        $month = (int)$_POST['record_month'];
        $scope = (int)$_POST['scope'];
        $sourceType = trim($_POST['source_type'] ?? '');
        $factorId = (int)$_POST['factor_id'];
        $amount = (float)$_POST['activity_amount'];
        $actionType = $_POST['action_type'] ?? 'draft'; // draft or submit

        $factor = EmissionFactor::find($factorId);
        $validSourceTypes = ['Stationary', 'Mobile', 'Fugitive', 'Electric', 'Steam', 'ValueChain'];
        $factorScope = $factor ? (str_starts_with($factor['category'], 'Scope1') ? 1 : (str_starts_with($factor['category'], 'Scope2') ? 2 : 3)) : 0;
        if ($orgId <= 0 || !Organization::find($orgId) || $year < 2000 || $year > 2200 ||
            $month < 1 || $month > 12 || !in_array($scope, [1, 2, 3], true) ||
            !$factor || !(int)$factor['is_active'] || $factorScope !== $scope || $amount <= 0 || !is_finite($amount) ||
            !in_array($sourceType, $validSourceTypes, true) || !in_array($actionType, ['draft', 'submit'], true)) {
            $_SESSION['flash_error'] = '請填寫所有必填欄位且用量必須大於 0';
            $this->redirect('/esg/ghg/create');
            return;
        }

        $status = ($actionType === 'submit') ? 'submitted' : 'draft';

        $recordId = GhgRecord::create([
            'org_id'          => $orgId,
            'record_year'     => $year,
            'record_month'    => $month,
            'scope'           => $scope,
            'source_type'     => $sourceType,
            'factor_id'       => $factorId,
            'activity_amount' => $amount,
            'status'          => $status,
            'user_id'         => $user['id']
        ]);

        // Handle attachment upload if present
        if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Uploader::upload($_FILES['attachment'], 'ghg', $recordId, $user['id']);
            if (!$uploadResult['success']) {
                $_SESSION['flash_warning'] = '活動數據已建立，但憑證上傳失敗: ' . $uploadResult['message'];
            }
        }

        // Workflow logging
        if ($status === 'submitted') {
            WorkflowTask::logAction(null, 'ghg', $recordId, 'submit', $user['id'], '填報人員提交盤查活動數據');
        }

        AuditLogger::log('CREATE_GHG_RECORD', 'GHG', $recordId, null, [
            'amount' => $amount,
            'factor_id' => $factorId,
            'status' => $status
        ]);

        $_SESSION['flash_success'] = ($status === 'submitted') ? '活動數據已成功提交送審！' : '活動數據已暫存為草稿！';
        $this->redirect('/esg/ghg');
    }

    public function factors(): void {
        Auth::requirePermission('ghg', 'read');

        $category = $_GET['category'] ?? '';
        $filters = [];
        if (!empty($category)) {
            $filters['category'] = $category;
        }
        $factors = EmissionFactor::all($filters);

        $this->render('ghg/factors', [
            'pageTitle' => '碳排放係數庫管理',
            'activeNav' => 'factors',
            'factors'   => $factors,
            'selectedCategory' => $category
        ]);
    }

    public function factorStore(): void {
        Auth::requirePermission('ghg', 'create');
        $this->checkCsrf();

        $user = Auth::user();
        $id = !empty($_POST['factor_id']) ? (int)$_POST['factor_id'] : null;

        $data = [
            'category'          => trim($_POST['category']),
            'fuel_name'         => trim($_POST['fuel_name']),
            'activity_unit'     => trim($_POST['activity_unit']),
            'co2_factor'        => (float)($_POST['co2_factor'] ?? 0),
            'ch4_factor'        => (float)($_POST['ch4_factor'] ?? 0),
            'n2o_factor'        => (float)($_POST['n2o_factor'] ?? 0),
            'total_factor_co2e' => (float)$_POST['total_factor_co2e'],
            'source_org'        => trim($_POST['source_org']),
            'applicable_year'   => (int)($_POST['applicable_year'] ?? date('Y')),
            'is_active'         => isset($_POST['is_active']) ? 1 : 0,
            'created_by'        => $user['id']
        ];

        $validCategories = ['Scope1_Fuel', 'Scope1_Mobile', 'Scope1_Fugitive', 'Scope2_Electricity', 'Scope2_Steam', 'Scope3'];
        if (!in_array($data['category'], $validCategories, true) || $data['fuel_name'] === '' ||
            $data['activity_unit'] === '' || $data['source_org'] === '' ||
            $data['total_factor_co2e'] < 0 || $data['applicable_year'] < 1990 || $data['applicable_year'] > 2200) {
            $_SESSION['flash_error'] = '排放係數資料格式或範圍不正確。';
            $this->redirect('/esg/factors');
            return;
        }

        if ($id) {
            EmissionFactor::update($id, $data);
            AuditLogger::log('UPDATE_FACTOR', 'GHG_FACTOR', $id, null, $data);
            $_SESSION['flash_success'] = '排放係數已更新！';
        } else {
            $newId = EmissionFactor::create($data);
            AuditLogger::log('CREATE_FACTOR', 'GHG_FACTOR', $newId, null, $data);
            $_SESSION['flash_success'] = '新排放係數已成功建立！';
        }

        $this->redirect('/esg/factors');
    }

    public function apiFactors(): void {
        Auth::requireLogin();
        $category = $_GET['category'] ?? '';
        $factors = !empty($category) ? EmissionFactor::getActiveByCategory($category) : EmissionFactor::all(['is_active' => 1]);
        $this->json(['success' => true, 'factors' => $factors]);
    }

    public function delete(): void {
        Auth::requirePermission('ghg', 'create');
        $this->checkCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $user = Auth::user();
        $ownerId = $user['role_key'] === 'super_admin' ? null : (int)$user['id'];
        $deleted = $id > 0 && GhgRecord::delete($id, $ownerId);

        if ($deleted) {
            AuditLogger::log('DELETE_GHG_RECORD', 'GHG', $id);
            $_SESSION['flash_success'] = '紀錄已刪除';
        } else {
            $_SESSION['flash_error'] = '無法刪除已送審或核准的數據！';
        }

        $this->redirect('/esg/ghg');
    }
}
