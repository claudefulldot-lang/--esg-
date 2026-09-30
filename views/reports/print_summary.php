<?php
use App\Helpers\Security;
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $year ?> 年度企業 ESG 與溫室氣體盤查總結報告</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Noto Sans TC", sans-serif; color: #222; }
        .report-header { border-bottom: 2px solid #198754; padding-bottom: 1rem; margin-bottom: 2rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; font-size: 12pt; }
            .card { box-shadow: none !important; border: 1px solid #ccc !important; }
            .table-responsive { overflow: visible !important; }
        }
    </style>
</head>
<body class="p-2 p-sm-3 p-md-5 bg-white">

<div class="container-fluid">
    <!-- Action Bar -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4 no-print bg-light p-3 rounded">
        <div>
            <strong>報表列印與存檔預覽</strong> (建議在列印設定中選擇「另存為 PDF」)
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-print me-1"></i>立即列印 / 存為 PDF</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">關閉視窗</button>
        </div>
    </div>

    <!-- Header -->
    <div class="report-header d-flex justify-content-between align-items-end">
        <div>
            <h2 class="fw-bold text-success mb-1">台灣晶綠科技股份有限公司</h2>
            <h4 class="fw-bold text-dark"><?= $year ?> 年度溫室氣體碳盤查與永續管理總結報告</h4>
            <div class="text-muted small">標準依據：ISO 14064-1:2018 / GHG Protocol 企業會計與報告標準</div>
        </div>
        <div class="text-end text-muted small">
            <div>報告產出日期：<?= date('Y 年 m 月 d 日') ?></div>
            <div>系統版本：ESG-Pro System v1.0</div>
        </div>
    </div>

    <!-- 1. Executive Summary Table -->
    <h5 class="fw-bold mb-3">一、三大範疇溫室氣體排放量總覽 (Summary of GHG Emissions)</h5>
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>範疇分類 (Scopes)</th>
                    <th>涵蓋主要排放源細項</th>
                    <th>排放量 (公噸 tCO₂e)</th>
                    <th>所佔比例 (%)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold text-start ps-3">範疇一：直接溫室氣體排放 (Scope 1)</td>
                    <td class="text-start">固定燃燒源 (柴油/天然氣)、公務移動源、空調冷媒與化糞池逸散</td>
                    <td class="fw-bold fs-6"><?= number_format($scopeTotals['scope1'], 2) ?></td>
                    <td><?= $scopeTotals['total'] > 0 ? round(($scopeTotals['scope1'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</td>
                </tr>
                <tr>
                    <td class="fw-bold text-start ps-3">範疇二：能源間接排放 (Scope 2)</td>
                    <td class="text-start">台電外購一般電力 (係數 0.495 kgCO₂e/kWh)、外購管道蒸氣</td>
                    <td class="fw-bold fs-6"><?= number_format($scopeTotals['scope2'], 2) ?></td>
                    <td><?= $scopeTotals['total'] > 0 ? round(($scopeTotals['scope2'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</td>
                </tr>
                <tr>
                    <td class="fw-bold text-start ps-3">範疇三：其他間接排放 (Scope 3)</td>
                    <td class="text-start">員工公務航空商務差旅、陸運重卡運輸、事業廢棄物委外掩埋</td>
                    <td class="fw-bold fs-6"><?= number_format($scopeTotals['scope3'], 2) ?></td>
                    <td><?= $scopeTotals['total'] > 0 ? round(($scopeTotals['scope3'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</td>
                </tr>
                <tr class="table-success fw-bold">
                    <td colspan="2" class="text-start ps-3 fs-5">全集團年度總排放量合計 (Total tCO₂e)</td>
                    <td class="fs-5"><?= number_format($scopeTotals['total'], 2) ?></td>
                    <td class="fs-5">100.0%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 2. Plant Summary -->
    <h5 class="fw-bold mb-3">二、各廠區盤查碳排放貢獻統計</h5>
    <div class="table-responsive mb-4">
        <table class="table table-bordered align-middle text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>廠區名稱</th>
                    <th>碳排總量 (tCO₂e)</th>
                    <th>全集團佔比 (%)</th>
                    <th>主要排放來源</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plants as $p): ?>
                <tr>
                    <td class="fw-bold text-start ps-3"><?= Security::e($p['org_name']) ?></td>
                    <td class="fw-bold"><?= number_format((float)$p['total_co2e'], 2) ?></td>
                    <td><?= $scopeTotals['total'] > 0 ? round(((float)$p['total_co2e'] / $scopeTotals['total']) * 100, 1) : 0 ?>%</td>
                    <td class="text-muted small">外購電力、製程設備燃燒</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 3. Monthly Distribution -->
    <h5 class="fw-bold mb-3">三、<?= $year ?> 年度月度碳排分佈 (tCO₂e)</h5>
    <div class="table-responsive mb-5">
        <table class="table table-bordered table-sm align-middle text-center mb-0">
            <thead class="table-light">
                <tr>
                    <th>範疇</th>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <th><?= $m ?>月</th>
                    <?php endfor; ?>
                    <th>合計</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="fw-bold">範疇一</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td><?= number_format($monthly[$m]['scope1'], 1) ?></td>
                    <?php endfor; ?>
                    <td class="fw-bold"><?= number_format($scopeTotals['scope1'], 1) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">範疇二</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td><?= number_format($monthly[$m]['scope2'], 1) ?></td>
                    <?php endfor; ?>
                    <td class="fw-bold"><?= number_format($scopeTotals['scope2'], 1) ?></td>
                </tr>
                <tr>
                    <td class="fw-bold">範疇三</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td><?= number_format($monthly[$m]['scope3'], 1) ?></td>
                    <?php endfor; ?>
                    <td class="fw-bold"><?= number_format($scopeTotals['scope3'], 1) ?></td>
                </tr>
                <tr class="fw-bold table-light">
                    <td>月度合計</td>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <td><?= number_format($monthly[$m]['total'], 1) ?></td>
                    <?php endfor; ?>
                    <td><?= number_format($scopeTotals['total'], 1) ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Sign-off Block -->
    <div class="row pt-4 pt-md-5 mt-3 mt-md-5 g-4">
        <div class="col-12 col-md-4 text-center">
            <div class="border-top pt-2 fw-semibold">盤查填報人員 (製表人)</div>
            <div class="text-muted small mt-1">簽名/蓋章日期：____年__月__日</div>
        </div>
        <div class="col-12 col-md-4 text-center">
            <div class="border-top pt-2 fw-semibold">部門初審主管 / 廠長</div>
            <div class="text-muted small mt-1">簽名/蓋章日期：____年__月__日</div>
        </div>
        <div class="col-12 col-md-4 text-center">
            <div class="border-top pt-2 fw-semibold">ESG 永續長 (CSO) / 委員會召集人</div>
            <div class="text-muted small mt-1">簽名/蓋章日期：____年__月__日</div>
        </div>
    </div>
</div>

</body>
</html>
