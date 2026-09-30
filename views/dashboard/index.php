<?php
use App\Helpers\Security;
?>

<!-- Year Selector & Page Action Header -->
<div class="row align-items-center mb-4 g-3">
    <div class="col-lg-6">
        <h4 class="fw-bold mb-1 text-dark">ESG 永續戰情室視覺化儀表板</h4>
        <p class="text-muted small mb-0">即時掌握溫室氣體盤查、減碳進度、環境資源與 ESG 關鍵績效</p>
    </div>
    <div class="col-lg-6 text-lg-end">
        <div class="d-flex flex-wrap gap-2 justify-content-lg-end align-items-center">
            <form method="GET" action="/esg/" class="d-inline-flex align-items-center">
                <label class="me-2 text-muted fw-semibold small text-nowrap">盤查年度：</label>
                <select name="year" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                    <?php for ($y = date('Y') + 1; $y >= 2022; $y--): ?>
                        <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?> 年度</option>
                    <?php endfor; ?>
                </select>
            </form>
            <a href="/esg/reports/print_summary?year=<?= $selectedYear ?>" target="_blank" class="btn btn-outline-secondary btn-sm shadow-sm text-nowrap">
                <i class="fa-solid fa-print me-1"></i>列印總結
            </a>
            <a href="/esg/reports/export_iso14064?year=<?= $selectedYear ?>" class="btn btn-success btn-sm shadow-sm text-nowrap">
                <i class="fa-solid fa-file-excel me-1"></i>匯出盤查清冊
            </a>
        </div>
    </div>
</div>

<!-- Row 1: KPI Top Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Emissions -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 kpi-card bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold">總溫室氣體排放量 (Total GHG)</span>
                    <h2 class="fw-bold my-2 text-success"><?= number_format($scopeTotals['total'], 2) ?> <small class="fs-6 text-muted">tCO₂e</small></h2>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="fa-solid fa-calculator me-1"></i>ISO 14064-1 合計
                    </span>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-3">
                    <i class="fa-solid fa-smog fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Scope 1, 2, 3 Breakdown Snapshot -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 kpi-card kpi-warning bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold">範疇一直接排放 (Scope 1)</span>
                    <h2 class="fw-bold my-2 text-warning"><?= number_format($scopeTotals['scope1'], 2) ?> <small class="fs-6 text-muted">tCO₂e</small></h2>
                    <div class="small text-muted">佔比：<?= $scopeTotals['total'] > 0 ? round(($scopeTotals['scope1'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</div>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-3">
                    <i class="fa-solid fa-fire-flame-curved fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 kpi-card kpi-info bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold">範疇二外購能源 (Scope 2)</span>
                    <h2 class="fw-bold my-2 text-info"><?= number_format($scopeTotals['scope2'], 2) ?> <small class="fs-6 text-muted">tCO₂e</small></h2>
                    <div class="small text-muted">佔比：<?= $scopeTotals['total'] > 0 ? round(($scopeTotals['scope2'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</div>
                </div>
                <div class="p-3 bg-info-subtle text-info rounded-3">
                    <i class="fa-solid fa-bolt fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 kpi-card kpi-danger bg-white shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small fw-semibold">範疇三其他間接 (Scope 3)</span>
                    <h2 class="fw-bold my-2 text-danger"><?= number_format($scopeTotals['scope3'], 2) ?> <small class="fs-6 text-muted">tCO₂e</small></h2>
                    <div class="small text-muted">佔比：<?= $scopeTotals['total'] > 0 ? round(($scopeTotals['scope3'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</div>
                </div>
                <div class="p-3 bg-danger-subtle text-danger rounded-3">
                    <i class="fa-solid fa-plane-departure fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Emission Intensity & Target Gap Card -->
<div class="row g-3 mb-4">
    <!-- Intensity KPIs -->
    <div class="col-lg-4">
        <div class="card p-4 h-100 shadow-sm">
            <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-gauge-high me-2 text-primary"></i>碳排放強度指標 (Emission Intensity)</h6>
            
            <div class="border rounded-3 p-3 mb-3 bg-light">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">營收碳排強度</span>
                    <span class="badge bg-primary">tCO₂e / 百萬元</span>
                </div>
                <h3 class="fw-bold text-dark mt-2 mb-0"><?= number_format($intensityRevenue, 4) ?></h3>
                <small class="text-muted">以合併營收 <?= number_format($settings['annual_revenue_millions'] ?? 3850) ?> 百萬新台幣為計算分母</small>
            </div>

            <div class="border rounded-3 p-3 bg-light">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">人均碳排強度</span>
                    <span class="badge bg-secondary">tCO₂e / 人</span>
                </div>
                <h3 class="fw-bold text-dark mt-2 mb-0"><?= number_format($intensityCapita, 4) ?></h3>
                <small class="text-muted">以全集團 <?= number_format($settings['total_employee_count'] ?? 520) ?> 名員工人數為分母</small>
            </div>
        </div>
    </div>

    <!-- Reduction Target vs Actual -->
    <div class="col-lg-8">
        <div class="card p-4 h-100 shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-bullseye me-2 text-danger"></i>2030 減碳目標達成狀況 (SBTi / NDC 追蹤)</h6>
                <span class="badge <?= $reductionAnalysis['is_on_track'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= $reductionAnalysis['is_on_track'] ? '目前進度超前達標' : '需持續加強節能減碳' ?>
                </span>
            </div>

            <div class="row text-center mb-3 g-2">
                <div class="col-12 col-sm-4 border-bottom border-bottom-sm-0 border-end-sm pb-2 pb-sm-0">
                    <div class="text-muted small">基準年排放 (<?= $settings['base_year'] ?? 2022 ?>)</div>
                    <div class="fs-5 fw-bold text-dark mt-1"><?= number_format($reductionAnalysis['target_emission'] / (1 - (($settings['target_reduction_pct'] ?? 30)/100)), 2) ?> t</div>
                </div>
                <div class="col-12 col-sm-4 border-bottom border-bottom-sm-0 border-end-sm pb-2 pb-sm-0">
                    <div class="text-muted small">目標限制排放量 (-<?= $settings['target_reduction_pct'] ?? 30 ?>%)</div>
                    <div class="fs-5 fw-bold text-danger mt-1"><?= number_format($reductionAnalysis['target_emission'], 2) ?> t</div>
                </div>
                <div class="col-12 col-sm-4 pt-1 pt-sm-0">
                    <div class="text-muted small">當前已減碳百分比</div>
                    <div class="fs-5 fw-bold <?= $reductionAnalysis['reduction_achieved_pct'] >= 0 ? 'text-success' : 'text-danger' ?> mt-1">
                        <?= number_format($reductionAnalysis['reduction_achieved_pct'], 1) ?>%
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-2">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>減碳執行進度條</span>
                    <span>目標 -<?= $settings['target_reduction_pct'] ?? 30 ?>% (基準年對比)</span>
                </div>
                <div class="progress" style="height: 16px;">
                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: <?= max(0, min(100, $reductionAnalysis['reduction_achieved_pct'])) ?>%;">
                        <?= number_format($reductionAnalysis['reduction_achieved_pct'], 1) ?>%
                    </div>
                </div>
            </div>
            <small class="text-muted mt-2 d-block">
                <i class="fa-solid fa-circle-info me-1 text-info"></i>
                距目標限制值差距：<strong><?= number_format(abs($reductionAnalysis['target_gap']), 2) ?> tCO₂e</strong> 
                (<?= $reductionAnalysis['target_gap'] <= 0 ? '優於目標值' : '超過限制配額' ?>)
            </small>
        </div>
    </div>
</div>

<!-- Row 3: Visual Charts (Monthly Trend & Scope / Plant Breakdown) -->
<div class="row g-3 mb-4">
    <!-- Monthly Trend -->
    <div class="col-lg-8">
        <div class="card p-4 shadow-sm h-100">
            <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-chart-line me-2 text-primary"></i><?= $selectedYear ?> 年度每月範疇碳排放趨勢 (tCO₂e)</h6>
            <div style="height: 280px;">
                <canvas id="monthlyTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Scope Donut Chart -->
    <div class="col-lg-4">
        <div class="card p-4 shadow-sm h-100">
            <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-chart-pie me-2 text-success"></i>三大範疇排放佔比</h6>
            <div style="height: 280px;" class="d-flex align-items-center justify-content-center">
                <canvas id="scopePieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Row 4: Plant Emissions & Recent Approval Tasks -->
<div class="row g-3">
    <!-- Plant Breakdown -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100">
            <h6 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-industry me-2 text-warning"></i>各廠區碳排放貢獻度 (tCO₂e)</h6>
            <div style="height: 260px;">
                <canvas id="plantChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Pending Workflow Tasks -->
    <div class="col-lg-6">
        <div class="card p-4 shadow-sm h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-secondary"><i class="fa-solid fa-bell me-2 text-primary"></i>待審查盤查任務與工單</h6>
                <a href="/esg/workflow" class="small text-primary text-decoration-none">查看全部 <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>任務期別與項目</th>
                            <th>指派廠區</th>
                            <th>狀態</th>
                            <th>填報人員</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingTasks)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-3">目前無待辦簽核任務</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($pendingTasks, 0, 5) as $task): ?>
                                <tr>
                                    <td class="fw-semibold small"><?= Security::e($task['task_title']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= Security::e($task['org_name']) ?></span></td>
                                    <td>
                                        <span class="badge badge-status-<?= $task['status'] ?>">
                                            <?= Security::e($task['status']) ?>
                                        </span>
                                    </td>
                                    <td class="small"><?= Security::e($task['assigned_user_name']) ?></td>
                                    <td>
                                        <a href="/esg/workflow" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                                            審核
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Monthly Trends Chart
    const monthlyData = <?= json_encode($monthlyTrends) ?>;
    const months = ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'];
    const s1 = [];
    const s2 = [];
    const s3 = [];

    for (let i = 1; i <= 12; i++) {
        s1.push(monthlyData[i] ? monthlyData[i].scope1 : 0);
        s2.push(monthlyData[i] ? monthlyData[i].scope2 : 0);
        s3.push(monthlyData[i] ? monthlyData[i].scope3 : 0);
    }

    new Chart(document.getElementById('monthlyTrendChart'), {
        type: 'bar',
        data: {
            labels: months,
            datasets: [
                { label: '範疇一 (直接)', data: s1, backgroundColor: '#ffc107', borderRadius: 4 },
                { label: '範疇二 (外購能源)', data: s2, backgroundColor: '#0dcaf0', borderRadius: 4 },
                { label: '範疇三 (其他間接)', data: s3, backgroundColor: '#dc3545', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'tCO₂e' } }
            },
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 2. Scope Pie Chart
    new Chart(document.getElementById('scopePieChart'), {
        type: 'doughnut',
        data: {
            labels: ['範疇一 (直接)', '範疇二 (能源)', '範疇三 (其他)'],
            datasets: [{
                data: [<?= (float)$scopeTotals['scope1'] ?>, <?= (float)$scopeTotals['scope2'] ?>, <?= (float)$scopeTotals['scope3'] ?>],
                backgroundColor: ['#ffc107', '#0dcaf0', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // 3. Plant Emissions Bar Chart
    const plantData = <?= json_encode($plantEmissions) ?>;
    const plantLabels = plantData.map(p => p.org_name);
    const plantValues = plantData.map(p => parseFloat(p.total_co2e));

    new Chart(document.getElementById('plantChart'), {
        type: 'bar',
        data: {
            labels: plantLabels.length ? plantLabels : ['無資料'],
            datasets: [{
                label: '排放量 (tCO₂e)',
                data: plantValues.length ? plantValues : [0],
                backgroundColor: '#198754',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
