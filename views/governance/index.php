<?php
use App\Helpers\Security;
use App\Auth;

$board = $metrics['board'] ?? [];
$integrity = $metrics['integrity'] ?? [];
$whistle = $metrics['whistleblower'] ?? [];
$infosec = $metrics['infosec'] ?? [];
$tcfd = $metrics['tcfd_risk'] ?? [];
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">公司治理管理 (Governance - G)</h4>
        <p class="text-muted small mb-0">落實健全董事會職能、反貪腐誠信經營、獨立檢舉機制與 TCFD 氣候風險矩陣</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <form method="GET" action="/esg/governance" class="d-inline-flex align-items-center">
            <label class="me-2 text-muted fw-semibold small">檢視年度：</label>
            <select name="year" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?> 年度</option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-pills bg-white p-2 rounded-3 shadow-sm mb-4" id="govTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-board">
            <i class="fa-solid fa-landmark me-1"></i>董事會運作與結構
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-integrity">
            <i class="fa-solid fa-stamp me-1"></i>誠信經營與反貪腐
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-whistle">
            <i class="fa-solid fa-bullhorn me-1"></i>吹哨者獨立檢舉機制
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-infosec">
            <i class="fa-solid fa-shield-virus me-1"></i>資訊安全與個資隱私
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tcfd">
            <i class="fa-solid fa-cloud-bolt me-1"></i>氣候風險評估 (TCFD)
        </button>
    </li>
</ul>

<div class="tab-content" id="govTabContent">
    <!-- 1. 董事會運作 -->
    <div class="tab-pane fade show active" id="tab-board">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-warning"><i class="fa-solid fa-landmark me-2"></i>董事會結構、多元化與出席績效</h5>
            <form action="/esg/governance/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="board">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="1">

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">全體董事總席次</label>
                        <input type="number" name="total_seats" class="form-control fs-5" value="<?= $board['total_seats'] ?? 9 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">獨立董事席次</label>
                        <input type="number" name="independent_directors" class="form-control fs-5" value="<?= $board['independent_directors'] ?? 4 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">女性董事席次</label>
                        <input type="number" name="female_directors" class="form-control" value="<?= $board['female_directors'] ?? 3 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">全年度董事會開會次數</label>
                        <input type="number" name="board_meetings_count" class="form-control" value="<?= $board['board_meetings_count'] ?? 7 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">全體董事平均出席率 (%)</label>
                        <input type="number" step="0.1" name="avg_attendance_rate_pct" class="form-control" value="<?= $board['avg_attendance_rate_pct'] ?? 96.5 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">董事會績效自我評鑑滿意度 (1-5)</label>
                        <input type="number" step="0.1" name="eval_satisfaction" class="form-control" value="<?= $board['eval_satisfaction'] ?? 4.8 ?>" required>
                    </div>
                </div>

                <?php 
                    $indepRatio = ($board['total_seats'] ?? 9) > 0 ? (($board['independent_directors'] ?? 4) / ($board['total_seats'] ?? 9)) : 0;
                ?>
                <div class="alert alert-info py-2 small mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    獨立董事席次佔比：<strong><?= round($indepRatio * 100, 1) ?>%</strong>
                    <?= $indepRatio >= (1/3) ? '<span class="text-success fw-bold ms-2">✓ 符合金管會大於 1/3 法規要求</span>' : '<span class="text-danger fw-bold ms-2">✗ 未達 1/3 法定比率！</span>' ?>
                </div>

                <?php if (Auth::can('governance', 'create')): ?>
                <button type="submit" class="btn btn-warning px-4 fw-semibold">儲存董事會指標</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 2. 誠信經營與反貪腐 -->
    <div class="tab-pane fade" id="tab-integrity">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-success"><i class="fa-solid fa-stamp me-2"></i>商業道德、誠信宣告與反貪腐政策 (GRI 205)</h5>
            <form action="/esg/governance/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="integrity">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="1">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">全員誠信與反貪腐政策簽署率 (%)</label>
                        <input type="number" step="0.1" name="anti_corruption_signing_rate" class="form-control" value="<?= $integrity['anti_corruption_signing_rate'] ?? 100 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">全員道德與誠信培訓覆蓋率 (%)</label>
                        <input type="number" step="0.1" name="ethical_training_coverage" class="form-control" value="<?= $integrity['ethical_training_coverage'] ?? 100 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">利益衝突宣告登記件數</label>
                        <input type="number" name="conflict_of_interest_declarations" class="form-control" value="<?= $integrity['conflict_of_interest_declarations'] ?? 48 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">違反誠信或收賄貪瀆案件數</label>
                        <input type="number" name="violations_count" class="form-control fw-bold <?= ($integrity['violations_count'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>" value="<?= $integrity['violations_count'] ?? 0 ?>" required>
                    </div>
                </div>

                <?php if (Auth::can('governance', 'create')): ?>
                <button type="submit" class="btn btn-success px-4 fw-semibold">儲存誠信經營數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 3. 吹哨者檢舉機制 -->
    <div class="tab-pane fade" id="tab-whistle">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-bullhorn me-2"></i>吹哨者獨立匿名檢舉受理與調查</h5>
            <form action="/esg/governance/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="whistleblower">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="1">

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">本年度受理檢舉案件數</label>
                        <input type="number" name="cases_reported" class="form-control" value="<?= $whistle['cases_reported'] ?? 1 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">啟動實質調查案件數</label>
                        <input type="number" name="cases_investigated" class="form-control" value="<?= $whistle['cases_investigated'] ?? 1 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">已結案案件數</label>
                        <input type="number" name="cases_closed" class="form-control" value="<?= $whistle['cases_closed'] ?? 1 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">檢舉人遭報復案件數</label>
                        <input type="number" name="retaliation_cases" class="form-control text-success fw-bold" value="<?= $whistle['retaliation_cases'] ?? 0 ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">調查總結與改善處置說明</label>
                    <textarea name="summary" class="form-control" rows="3"><?= Security::e($whistle['summary'] ?? '') ?></textarea>
                </div>

                <?php if (Auth::can('governance', 'create')): ?>
                <button type="submit" class="btn btn-secondary px-4 fw-semibold">儲存檢舉調查紀錄</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 4. 資訊安全與隱私 -->
    <div class="tab-pane fade" id="tab-infosec">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-info"><i class="fa-solid fa-shield-virus me-2"></i>資安管理系統 (ISO 27001) 與社交工程演練</h5>
            <form action="/esg/governance/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="infosec">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="1">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-check form-switch fs-5 mt-4">
                            <input class="form-check-input" type="checkbox" name="iso27001_cert_valid" id="iso27001" value="1" <?= !empty($infosec['iso27001_cert_valid']) ? 'checked' : '' ?>>
                            <label class="form-check-label fs-6 fw-semibold" for="iso27001">ISO 27001 資訊安全管理系統國際驗證合格有效</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">重大資安中斷或外洩事件通報件數</label>
                        <input type="number" name="major_incidents_count" class="form-control fw-bold text-success" value="<?= $infosec['major_incidents_count'] ?? 0 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">社交工程防釣魚演練覆蓋率 (%)</label>
                        <input type="number" step="0.1" name="phishing_drill_coverage_pct" class="form-control" value="<?= $infosec['phishing_drill_coverage_pct'] ?? 98.2 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">釣魚信件點擊不合格率 (%)</label>
                        <input type="number" step="0.1" name="phishing_click_rate_pct" class="form-control" value="<?= $infosec['phishing_click_rate_pct'] ?? 1.2 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">侵害客戶/員工隱私客訴件數</label>
                        <input type="number" name="privacy_complaints" class="form-control" value="<?= $infosec['privacy_complaints'] ?? 0 ?>" required>
                    </div>
                </div>

                <?php if (Auth::can('governance', 'create')): ?>
                <button type="submit" class="btn btn-info text-white px-4 fw-semibold">儲存資安與隱私數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 5. TCFD 氣候風險矩陣 -->
    <div class="tab-pane fade" id="tab-tcfd">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-dark"><i class="fa-solid fa-cloud-bolt me-2 text-danger"></i>TCFD 氣候變遷實體與轉型風險評估矩陣</h5>
            <form action="/esg/governance/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="tcfd_risk">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="1">

                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 20%;">風險/機會構面</th>
                                <th style="width: 15%;">發生機率 (1-5)</th>
                                <th style="width: 15%;">衝擊程度 (1-5)</th>
                                <th style="width: 50%;">因應對策與調適行動措施</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold text-danger">實體風險：極端暴雨與廠區淹水</td>
                                <td>
                                    <input type="number" name="flooding_prob" min="1" max="5" class="form-control" value="<?= $tcfd['physical_flooding_risk']['prob'] ?? 2 ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="flooding_impact" min="1" max="5" class="form-control" value="<?= $tcfd['physical_flooding_risk']['impact'] ?? 4 ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="flooding_mitigation" class="form-control" value="<?= Security::e($tcfd['physical_flooding_risk']['mitigation'] ?? '設置防水閘門與地下蓄水池') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-warning">轉型風險：台灣碳費徵收與 CBAM</td>
                                <td>
                                    <input type="number" name="carbon_fee_prob" min="1" max="5" class="form-control" value="<?= $tcfd['carbon_fee_transition_risk']['prob'] ?? 5 ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="carbon_fee_impact" min="1" max="5" class="form-control" value="<?= $tcfd['carbon_fee_transition_risk']['impact'] ?? 4 ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="carbon_fee_mitigation" class="form-control" value="<?= Security::e($tcfd['carbon_fee_transition_risk']['mitigation'] ?? '引進高能效設備並簽訂綠電轉供CPPA契約') ?>" required>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-success">氣候機會：低碳產品與循環封裝</td>
                                <td>
                                    <input type="number" name="green_product_prob" min="1" max="5" class="form-control" value="<?= $tcfd['opportunity_green_product']['prob'] ?? 4 ?>" required>
                                </td>
                                <td>
                                    <input type="number" name="green_product_impact" min="1" max="5" class="form-control" value="<?= $tcfd['opportunity_green_product']['impact'] ?? 5 ?>" required>
                                </td>
                                <td>
                                    <input type="text" name="green_product_mitigation" class="form-control" value="<?= Security::e($tcfd['opportunity_green_product']['mitigation'] ?? '開發低碳半導體製程包材') ?>" required>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (Auth::can('governance', 'create')): ?>
                <button type="submit" class="btn btn-dark px-4 fw-semibold">儲存 TCFD 氣候矩陣</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
