<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Database;
use App\Calculator;
use App\Models\GhgRecord;
use App\Models\WorkflowTask;
use PDO;

class DashboardController extends Controller {
    public function index(): void {
        Auth::requireLogin();

        $db = Database::getConnection();
        $selectedYear = (int)($_GET['year'] ?? date('Y'));

        // System Settings
        $stmtSettings = $db->query("SELECT `setting_key`, `setting_value` FROM `sys_settings`");
        $settings = $stmtSettings->fetchAll(PDO::FETCH_KEY_PAIR);

        $baseYear = (int)($settings['base_year'] ?? 2022);
        $targetReductionPct = (float)($settings['target_reduction_pct'] ?? 30.0);
        $revenueMillions = (float)($settings['annual_revenue_millions'] ?? 3850);
        $employeeCount = (int)($settings['total_employee_count'] ?? 520);

        // Scope Totals for Selected Year
        $scopeTotals = GhgRecord::getScopeTotals($selectedYear);
        $baseScopeTotals = GhgRecord::getScopeTotals($baseYear);
        if ($baseScopeTotals['total'] == 0) {
            // If base year has no records yet, use 2025 as baseline reference
            $baseScopeTotals = GhgRecord::getScopeTotals(2025);
        }

        // Carbon Intensities
        $intensityRevenue = Calculator::calculateIntensityRevenue($scopeTotals['total'], $revenueMillions);
        $intensityCapita = Calculator::calculateIntensityCapita($scopeTotals['total'], $employeeCount);

        // Target Reduction Analysis
        $reductionAnalysis = Calculator::calculateReductionProgress(
            $baseScopeTotals['total'] > 0 ? $baseScopeTotals['total'] : 650.0, // fallback baseline if empty
            $scopeTotals['total'],
            $targetReductionPct
        );

        // Monthly Trends
        $monthlyTrends = GhgRecord::getMonthlyTrends($selectedYear);

        // Plant Emissions
        $plantEmissions = GhgRecord::getPlantEmissions($selectedYear);

        // Environmental Metrics Summary (Water & Waste)
        $stmtWater = $db->prepare("
            SELECT record_type, SUM(amount) as total_amount, unit 
            FROM `esg_energy_water_records` 
            WHERE record_year = :year 
            GROUP BY record_type, unit
        ");
        $stmtWater->execute([':year' => $selectedYear]);
        $waterMetrics = $stmtWater->fetchAll();

        $stmtWaste = $db->prepare("
            SELECT waste_category, SUM(amount) as total_amount 
            FROM `esg_waste_records` 
            WHERE record_year = :year 
            GROUP BY waste_category
        ");
        $stmtWaste->execute([':year' => $selectedYear]);
        $wasteMetrics = $stmtWaste->fetchAll(PDO::FETCH_KEY_PAIR);

        // Pending Workflow Tasks
        $pendingTasks = WorkflowTask::all(['status' => 'submitted']);
        if (empty($pendingTasks)) {
            $pendingTasks = WorkflowTask::all();
        }

        $this->render('dashboard/index', [
            'pageTitle'          => 'ESG 戰情室儀表板',
            'activeNav'          => 'dashboard',
            'selectedYear'       => $selectedYear,
            'scopeTotals'        => $scopeTotals,
            'intensityRevenue'   => $intensityRevenue,
            'intensityCapita'    => $intensityCapita,
            'reductionAnalysis'  => $reductionAnalysis,
            'monthlyTrends'      => $monthlyTrends,
            'plantEmissions'     => $plantEmissions,
            'waterMetrics'       => $waterMetrics,
            'wasteMetrics'       => $wasteMetrics,
            'pendingTasks'       => $pendingTasks,
            'settings'           => $settings
        ]);
    }
}
