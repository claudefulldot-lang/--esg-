<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Calculator;
use App\Models\SocialRecord;
use App\Models\Organization;
use App\Helpers\AuditLogger;

class SocialController extends Controller {
    public function index(): void {
        Auth::requirePermission('social', 'read');

        $year = (int)($_GET['year'] ?? 2025);
        $records = SocialRecord::all(['year' => $year]);

        // Key-indexed metrics by category
        $metrics = [];
        foreach ($records as $r) {
            $metrics[$r['metric_category']] = json_decode($r['data_json'], true) ?: [];
        }

        $companies = Organization::all();

        $this->render('social/index', [
            'pageTitle' => '社會責任管理 (Social - S)',
            'activeNav' => 'social',
            'year'      => $year,
            'metrics'   => $metrics,
            'companies' => $companies
        ]);
    }

    public function store(): void {
        Auth::requirePermission('social', 'create');
        $this->checkCsrf();

        $user = Auth::user();
        $year = (int)$_POST['record_year'];
        $orgId = (int)$_POST['org_id'];
        $category = $_POST['metric_category'];

        if (!Organization::find($orgId) || $year < 2000 || $year > 2200 ||
            !in_array($category, ['diversity', 'salary', 'ehs', 'training', 'supply_chain'], true)) {
            http_response_code(400);
            die('無效的社會責任指標資料。');
        }

        $data = [];
        if ($category === 'diversity') {
            $data = [
                'total_employees'   => (int)$_POST['total_employees'],
                'male_count'        => (int)$_POST['male_count'],
                'female_count'      => (int)$_POST['female_count'],
                'manager_male'      => (int)$_POST['manager_male'],
                'manager_female'    => (int)$_POST['manager_female'],
                'age_under_30'      => (int)$_POST['age_under_30'],
                'age_30_50'         => (int)$_POST['age_30_50'],
                'age_over_50'       => (int)$_POST['age_over_50'],
                'turnover_rate_pct' => (float)$_POST['turnover_rate_pct']
            ];
        } elseif ($category === 'salary') {
            $avgMale = (float)$_POST['avg_male_salary'];
            $avgFemale = (float)$_POST['avg_female_salary'];
            $ratio = $avgMale > 0 ? round($avgFemale / $avgMale, 3) : 1.0;
            $applied = (int)$_POST['parental_leave_applied_female'] + (int)$_POST['parental_leave_applied_male'];
            $reinstated = (int)$_POST['reinstated_count'];
            $reinstatementRate = $applied > 0 ? round(($reinstated / $applied) * 100, 1) : 100.0;

            $data = [
                'avg_male_salary'               => $avgMale,
                'avg_female_salary'             => $avgFemale,
                'ratio_female_to_male_salary'   => $ratio,
                'parental_leave_applied_male'   => (int)$_POST['parental_leave_applied_male'],
                'parental_leave_applied_female' => (int)$_POST['parental_leave_applied_female'],
                'reinstated_count'              => $reinstated,
                'reinstatement_rate_pct'        => $reinstatementRate
            ];
        } elseif ($category === 'ehs') {
            $totalHours = (float)$_POST['total_working_hours'];
            $lti = (int)$_POST['lti_count'];
            $lostDays = (int)$_POST['lost_days'];

            $fr = Calculator::calculateFR($lti, $totalHours);
            $sr = Calculator::calculateSR($lostDays, $totalHours);

            $data = [
                'total_working_hours' => $totalHours,
                'lti_count'           => $lti,
                'fatalities'          => (int)$_POST['fatalities'],
                'lost_days'           => $lostDays,
                'fr'                  => $fr,
                'sr'                  => $sr,
                'near_miss_count'     => (int)$_POST['near_miss_count']
            ];
        } elseif ($category === 'training') {
            $totalEmployees = (int)($_POST['total_employees'] ?: 520);
            $totalHours = (float)$_POST['total_training_hours'];
            $avgHours = $totalEmployees > 0 ? round($totalHours / $totalEmployees, 1) : 0;

            $data = [
                'total_training_hours'        => $totalHours,
                'avg_hours_per_employee'      => $avgHours,
                'esg_training_hours'          => (float)$_POST['esg_training_hours'],
                'training_satisfaction_score' => (float)$_POST['training_satisfaction_score']
            ];
        } elseif ($category === 'supply_chain') {
            $data = [
                'evaluated_suppliers_count' => (int)$_POST['evaluated_suppliers_count'],
                'tier1_supplier_count'      => (int)$_POST['tier1_supplier_count'],
                'esg_audit_rate_pct'        => (float)$_POST['esg_audit_rate_pct'],
                'grade_a_count'             => (int)$_POST['grade_a_count'],
                'grade_b_count'             => (int)$_POST['grade_b_count'],
                'grade_c_count'             => (int)$_POST['grade_c_count'],
                'volunteer_hours'           => (int)$_POST['volunteer_hours'],
                'donation_amount_twd'       => (float)$_POST['donation_amount_twd']
            ];
        }

        $recordId = SocialRecord::createOrUpdate([
            'org_id'          => $orgId,
            'record_year'     => $year,
            'metric_category' => $category,
            'data_json'       => $data,
            'status'          => 'approved_final',
            'user_id'         => $user['id']
        ]);

        AuditLogger::log('UPDATE_SOCIAL_RECORD', 'SOCIAL', $recordId, null, $data);
        $_SESSION['flash_success'] = '社會責任指標數據已成功儲存！';
        $this->redirect("/esg/social?year={$year}");
    }
}
