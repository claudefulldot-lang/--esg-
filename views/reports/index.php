<?php
use App\Helpers\Security;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">智慧報表與永續報告書產出中心</h4>
        <p class="text-muted small mb-0">一鍵自動匯出符合國際 ISO 14064-1 與 GRI Standards 2021 規範之正式報告書底稿</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <form method="GET" action="/esg/reports" class="d-inline-flex align-items-center">
            <label class="me-2 text-muted fw-semibold small">報表年度：</label>
            <select name="year" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <?php for ($y = date('Y') + 1; $y >= 2022; $y--): ?>
                    <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?> 年度</option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- Three Core Report Export Cards -->
<div class="row g-4 mb-5">
    <!-- Report 1: ISO 14064-1 Inventory Excel -->
    <div class="col-lg-4">
        <div class="card p-4 h-100 shadow-sm border-top border-4 border-success">
            <div class="d-flex align-items-center mb-3">
                <div class="p-3 bg-success-subtle text-success rounded-3 me-3">
                    <i class="fa-solid fa-file-excel fs-2"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">ISO 14064-1 盤查清冊</h5>
                    <small class="text-muted">溫室氣體直接/能源/其他間接排放</small>
                </div>
            </div>
            <p class="text-muted small">
                完整匯出全集團各廠區活動用量、係數基準與排放當量試算底稿，支援外部第三方第三方查證單位抽核。
            </p>
            <div class="mt-auto">
                <a href="/esg/reports/export_iso14064?year=<?= $selectedYear ?>" class="btn btn-success w-100 fw-semibold shadow-sm">
                    <i class="fa-solid fa-download me-2"></i>匯出 Excel 清冊 (.xlsx)
                </a>
            </div>
        </div>
    </div>

    <!-- Report 2: GRI Content Index Excel -->
    <div class="col-lg-4">
        <div class="card p-4 h-100 shadow-sm border-top border-4 border-primary">
            <div class="d-flex align-items-center mb-3">
                <div class="p-3 bg-primary-subtle text-primary rounded-3 me-3">
                    <i class="fa-solid fa-table-list fs-2"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">GRI 永續指標對應表</h5>
                    <small class="text-muted">GRI Standards 2021 準則索引</small>
                </div>
            </div>
            <p class="text-muted small">
                自動映射 GRI 302(能源)、303(水資源)、305(碳排)、401(勞雇)、403(安衛)、205(反貪腐)等指標底稿。
            </p>
            <div class="mt-auto">
                <a href="/esg/reports/export_gri?year=<?= $selectedYear ?>" class="btn btn-primary w-100 fw-semibold shadow-sm">
                    <i class="fa-solid fa-download me-2"></i>匯出 GRI 索引表 (.xlsx)
                </a>
            </div>
        </div>
    </div>

    <!-- Report 3: Printable Executive ESG Summary -->
    <div class="col-lg-4">
        <div class="card p-4 h-100 shadow-sm border-top border-4 border-dark">
            <div class="d-flex align-items-center mb-3">
                <div class="p-3 bg-secondary-subtle text-dark rounded-3 me-3">
                    <i class="fa-solid fa-print fs-2"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">ESG 永續年度總結報告</h5>
                    <small class="text-muted">適合列印與主管層簽核</small>
                </div>
            </div>
            <p class="text-muted small">
                精簡美觀之企業永續總結，包含三大範疇碳盤查總覽、各廠區績效及減碳進度表，具備簽核欄位。
            </p>
            <div class="mt-auto">
                <a href="/esg/reports/print_summary?year=<?= $selectedYear ?>" target="_blank" class="btn btn-outline-dark w-100 fw-semibold">
                    <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>預覽並列印 PDF 報告
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Section: Inventory Data Preview Table -->
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-list-ol me-2 text-success"></i><?= $selectedYear ?> 年度盤查數據概況預覽 (共 <?= count($records) ?> 筆)
        </h5>
        <div>
            <span class="badge bg-success fs-6">年度總排放：<?= number_format($scopeTotals['total'], 2) ?> tCO₂e</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle table-sm data-table">
            <thead class="table-light">
                <tr>
                    <th>廠區名稱</th>
                    <th>盤查期別</th>
                    <th>範疇分類</th>
                    <th>排放項目</th>
                    <th>用量</th>
                    <th>係數 (kgCO₂e)</th>
                    <th>試算排放量 (tCO₂e)</th>
                    <th>狀態</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td class="fw-semibold small"><?= Security::e($r['org_name']) ?></td>
                    <td><?= $r['record_year'] ?>/<?= sprintf('%02d', $r['record_month']) ?></td>
                    <td>
                        <?php if ($r['scope'] == 1): ?>
                            <span class="badge bg-warning text-dark">範疇一</span>
                        <?php elseif ($r['scope'] == 2): ?>
                            <span class="badge bg-info text-dark">範疇二</span>
                        <?php else: ?>
                            <span class="badge bg-danger">範疇三</span>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= Security::e($r['fuel_name']) ?></td>
                    <td><?= number_format((float)$r['activity_amount'], 2) ?> <?= Security::e($r['activity_unit']) ?></td>
                    <td class="small text-muted"><?= number_format((float)$r['total_factor_co2e'], 4) ?></td>
                    <td class="fw-bold text-success"><?= number_format((float)$r['calculated_co2e'], 4) ?></td>
                    <td><span class="badge badge-status-<?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
