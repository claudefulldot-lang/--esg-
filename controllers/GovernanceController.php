<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Models\GovernanceRecord;
use App\Models\Organization;
use App\Helpers\AuditLogger;

class GovernanceController extends Controller {
    public function index(): void {
        Auth::requirePermission('governance', 'read');

        $year = (int)($_GET['year'] ?? 2025);
        $records = GovernanceRecord::all(['year' => $year]);

        $metrics = [];
        foreach ($records as $r) {
            $metrics[$r['metric_category']] = json_decode($r['data_json'], true) ?: [];
        }

        $companies = Organization::all();

        $this->render('governance/index', [
            'pageTitle' => '公司治理管理 (Governance - G)',
            'activeNav' => 'governance',
            'year'      => $year,
            'metrics'   => $metrics,
            'companies' => $companies
        ]);
    }

    public function store(): void {
        Auth::requirePermission('governance', 'create');
        $this->checkCsrf();

        $user = Auth::user();
        $year = (int)$_POST['record_year'];
        $orgId = (int)$_POST['org_id'];
        $category = $_POST['metric_category'];

        if (!Organization::find($orgId) || $year < 2000 || $year > 2200 ||
            !in_array($category, ['board', 'integrity', 'whistleblower', 'infosec', 'tcfd_risk'], true)) {
            http_response_code(400);
            die('無效的公司治理指標資料。');
        }

        $data = [];
        if ($category === 'board') {
            $data = [
                'total_seats'             => (int)$_POST['total_seats'],
                'independent_directors'   => (int)$_POST['independent_directors'],
                'female_directors'        => (int)$_POST['female_directors'],
                'avg_attendance_rate_pct' => (float)$_POST['avg_attendance_rate_pct'],
                'board_meetings_count'    => (int)$_POST['board_meetings_count'],
                'eval_satisfaction'       => (float)$_POST['eval_satisfaction']
            ];
        } elseif ($category === 'integrity') {
            $data = [
                'anti_corruption_signing_rate'       => (float)$_POST['anti_corruption_signing_rate'],
                'ethical_training_coverage'          => (float)$_POST['ethical_training_coverage'],
                'conflict_of_interest_declarations' => (int)$_POST['conflict_of_interest_declarations'],
                'violations_count'                   => (int)$_POST['violations_count']
            ];
        } elseif ($category === 'whistleblower') {
            $data = [
                'cases_reported'     => (int)$_POST['cases_reported'],
                'cases_investigated' => (int)$_POST['cases_investigated'],
                'cases_closed'       => (int)$_POST['cases_closed'],
                'retaliation_cases'  => (int)$_POST['retaliation_cases'],
                'summary'            => trim($_POST['summary'] ?? '')
            ];
        } elseif ($category === 'infosec') {
            $data = [
                'iso27001_cert_valid'          => isset($_POST['iso27001_cert_valid']) ? 1 : 0,
                'phishing_drill_coverage_pct' => (float)$_POST['phishing_drill_coverage_pct'],
                'phishing_click_rate_pct'     => (float)$_POST['phishing_click_rate_pct'],
                'major_incidents_count'       => (int)$_POST['major_incidents_count'],
                'privacy_complaints'          => (int)$_POST['privacy_complaints']
            ];
        } elseif ($category === 'tcfd_risk') {
            $data = [
                'physical_flooding_risk' => [
                    'prob'       => (int)$_POST['flooding_prob'],
                    'impact'     => (int)$_POST['flooding_impact'],
                    'mitigation' => trim($_POST['flooding_mitigation'] ?? '')
                ],
                'carbon_fee_transition_risk' => [
                    'prob'       => (int)$_POST['carbon_fee_prob'],
                    'impact'     => (int)$_POST['carbon_fee_impact'],
                    'mitigation' => trim($_POST['carbon_fee_mitigation'] ?? '')
                ],
                'opportunity_green_product' => [
                    'prob'       => (int)$_POST['green_product_prob'],
                    'impact'     => (int)$_POST['green_product_impact'],
                    'mitigation' => trim($_POST['green_product_mitigation'] ?? '')
                ]
            ];
        }

        $recordId = GovernanceRecord::createOrUpdate([
            'org_id'          => $orgId,
            'record_year'     => $year,
            'metric_category' => $category,
            'data_json'       => $data,
            'status'          => 'approved_final',
            'user_id'         => $user['id']
        ]);

        AuditLogger::log('UPDATE_GOVERNANCE_RECORD', 'GOVERNANCE', $recordId, null, $data);
        $_SESSION['flash_success'] = '公司治理指標數據已成功儲存！';
        $this->redirect("/esg/governance?year={$year}");
    }
}
