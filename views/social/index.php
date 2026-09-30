<?php
use App\Helpers\Security;
use App\Auth;

$diversity = $metrics['diversity'] ?? [];
$salary = $metrics['salary'] ?? [];
$ehs = $metrics['ehs'] ?? [];
$training = $metrics['training'] ?? [];
$supply = $metrics['supply_chain'] ?? [];
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">社會責任管理 (Social - S)</h4>
        <p class="text-muted small mb-0">落實 GRI 401(勞雇關係)、403(安衛)、404(培訓)、405(多元平等) 與供應鏈永續</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <form method="GET" action="/esg/social" class="d-inline-flex align-items-center">
            <label class="me-2 text-muted fw-semibold small">檢視年度：</label>
            <select name="year" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <?php for ($y = date('Y'); $y >= 2022; $y--): ?>
                    <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?> 年度</option>
                <?php endfor; ?>
            </select>
        </form>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills bg-white p-2 rounded-3 shadow-sm mb-4" id="socialTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diversity">
            <i class="fa-solid fa-users me-1"></i>人力資本與多元化 (GRI 401/405)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-salary">
            <i class="fa-solid fa-scale-balanced me-1"></i>薪酬平權與育嬰留停
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ehs">
            <i class="fa-solid fa-shield-halved me-1"></i>職業安全衛生 (FR / SR)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-training">
            <i class="fa-solid fa-graduation-cap me-1"></i>員工培育與發展 (GRI 404)
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-supply">
            <i class="fa-solid fa-truck-ramp-box me-1"></i>供應鏈與社會參與
        </button>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="socialTabContent">
    <!-- 1. 人力資本與多元化 -->
    <div class="tab-pane fade show active" id="tab-diversity">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-users me-2"></i>員工結構與多元化指標維護 (年度數據)</h5>
            <form action="/esg/social/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="diversity">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="2">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">全集團員工總人數</label>
                        <input type="number" name="total_employees" class="form-control fs-5 fw-bold" value="<?= $diversity['total_employees'] ?? 520 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">男性員工人數</label>
                        <input type="number" name="male_count" class="form-control" value="<?= $diversity['male_count'] ?? 310 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">女性員工人數</label>
                        <input type="number" name="female_count" class="form-control" value="<?= $diversity['female_count'] ?? 210 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">管理職男性人數</label>
                        <input type="number" name="manager_male" class="form-control" value="<?= $diversity['manager_male'] ?? 32 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">管理職女性人數 (女性主管佔比)</label>
                        <input type="number" name="manager_female" class="form-control" value="<?= $diversity['manager_female'] ?? 18 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">年齡 30 歲以下人數</label>
                        <input type="number" name="age_under_30" class="form-control" value="<?= $diversity['age_under_30'] ?? 135 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">年齡 30-50 歲人數</label>
                        <input type="number" name="age_30_50" class="form-control" value="<?= $diversity['age_30_50'] ?? 320 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">年齡 50 歲以上人數</label>
                        <input type="number" name="age_over_50" class="form-control" value="<?= $diversity['age_over_50'] ?? 65 ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">年度員工流動率 (%)</label>
                        <input type="number" step="0.1" name="turnover_rate_pct" class="form-control" value="<?= $diversity['turnover_rate_pct'] ?? 7.5 ?>" required>
                    </div>
                </div>

                <?php if (Auth::can('social', 'create')): ?>
                <button type="submit" class="btn btn-primary px-4 fw-semibold mt-2">儲存多元化數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 2. 薪酬平權與育嬰留停 -->
    <div class="tab-pane fade" id="tab-salary">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-success"><i class="fa-solid fa-scale-balanced me-2"></i>男女薪酬平等比率與育嬰留停復職追蹤 (GRI 401-3, 405-2)</h5>
            <form action="/esg/social/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="salary">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="2">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">基層/一般同仁男性平均年薪 (NTD)</label>
                        <input type="number" name="avg_male_salary" class="form-control" value="<?= $salary['avg_male_salary'] ?? 850000 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">基層/一般同仁女性平均年薪 (NTD)</label>
                        <input type="number" name="avg_female_salary" class="form-control" value="<?= $salary['avg_female_salary'] ?? 820000 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">申請育嬰留停男性人數</label>
                        <input type="number" name="parental_leave_applied_male" class="form-control" value="<?= $salary['parental_leave_applied_male'] ?? 4 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">申請育嬰留停女性人數</label>
                        <input type="number" name="parental_leave_applied_female" class="form-control" value="<?= $salary['parental_leave_applied_female'] ?? 12 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">期滿實際復職人數</label>
                        <input type="number" name="reinstated_count" class="form-control" value="<?= $salary['reinstated_count'] ?? 16 ?>" required>
                    </div>
                </div>

                <?php if (Auth::can('social', 'create')): ?>
                <button type="submit" class="btn btn-success px-4 fw-semibold mt-2">儲存薪酬與育嬰留停數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 3. 職業安全衛生 EHS -->
    <div class="tab-pane fade" id="tab-ehs">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-danger"><i class="fa-solid fa-shield-halved me-2"></i>職業安全衛生指標 (失能傷害頻率 FR / 嚴重率 SR 計算)</h5>
            <form action="/esg/social/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="ehs">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="2">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">全體同仁總經歷工時 (小時) <span class="text-danger">*</span></label>
                        <input type="number" name="total_working_hours" class="form-control fs-5" value="<?= $ehs['total_working_hours'] ?? 1040000 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">失能傷害件數 (LTI)</label>
                        <input type="number" name="lti_count" class="form-control" value="<?= $ehs['lti_count'] ?? 0 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">總損失工作日數 (日)</label>
                        <input type="number" name="lost_days" class="form-control" value="<?= $ehs['lost_days'] ?? 0 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">因工殉職人數</label>
                        <input type="number" name="fatalities" class="form-control" value="<?= $ehs['fatalities'] ?? 0 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">虛驚事件通報件數 (Near Miss)</label>
                        <input type="number" name="near_miss_count" class="form-control" value="<?= $ehs['near_miss_count'] ?? 3 ?>" required>
                    </div>
                </div>

                <!-- Display calculated FR / SR -->
                <div class="row g-3 p-3 bg-light border rounded-3 mb-3">
                    <div class="col-md-6 text-center">
                        <span class="text-muted small">失能傷害頻率 (FR)</span>
                        <h4 class="fw-bold text-danger mb-0"><?= number_format($ehs['fr'] ?? 0, 2) ?></h4>
                        <small class="text-muted">公式：(失能次數 × 10⁶) ÷ 總經歷工時</small>
                    </div>
                    <div class="col-md-6 text-center border-start">
                        <span class="text-muted small">失能傷害嚴重率 (SR)</span>
                        <h4 class="fw-bold text-danger mb-0"><?= number_format($ehs['sr'] ?? 0, 2) ?></h4>
                        <small class="text-muted">公式：(損失日數 × 10⁶) ÷ 總經歷工時</small>
                    </div>
                </div>

                <?php if (Auth::can('social', 'create')): ?>
                <button type="submit" class="btn btn-danger px-4 fw-semibold">計算並儲存安衛數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 4. 培育與發展 -->
    <div class="tab-pane fade" id="tab-training">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-info"><i class="fa-solid fa-graduation-cap me-2"></i>員工教育訓練與永續能力培養 (GRI 404)</h5>
            <form action="/esg/social/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="training">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="2">

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">全集團年度總培訓時數 (小時)</label>
                        <input type="number" name="total_training_hours" class="form-control fs-5" value="<?= $training['total_training_hours'] ?? 18200 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ESG / 永續與安衛專題培訓時數 (小時)</label>
                        <input type="number" name="esg_training_hours" class="form-control" value="<?= $training['esg_training_hours'] ?? 3200 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">員工滿意度評分 (1.0 - 5.0)</label>
                        <input type="number" step="0.1" max="5.0" min="1.0" name="training_satisfaction_score" class="form-control" value="<?= $training['training_satisfaction_score'] ?? 4.6 ?>" required>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="p-3 bg-light rounded w-100 text-center">
                            <span class="text-muted small">平均每人年受訓時數</span>
                            <h4 class="fw-bold text-info mb-0"><?= number_format($training['avg_hours_per_employee'] ?? 35, 1) ?> 小時</h4>
                        </div>
                    </div>
                </div>

                <?php if (Auth::can('social', 'create')): ?>
                <button type="submit" class="btn btn-info text-white px-4 fw-semibold">儲存培育訓練數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- 5. 供應鏈與社區參與 -->
    <div class="tab-pane fade" id="tab-supply">
        <div class="card p-4 shadow-sm">
            <h5 class="fw-bold mb-3 text-secondary"><i class="fa-solid fa-truck-ramp-box me-2"></i>永續供應鏈評估與社會公益參與 (GRI 308/414/413)</h5>
            <form action="/esg/social/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="metric_category" value="supply_chain">
                <input type="hidden" name="record_year" value="<?= $year ?>">
                <input type="hidden" name="org_id" value="2">

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">第一階主要供應商總數</label>
                        <input type="number" name="tier1_supplier_count" class="form-control" value="<?= $supply['tier1_supplier_count'] ?? 92 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">已完成 ESG 評鑑供應商家數</label>
                        <input type="number" name="evaluated_suppliers_count" class="form-control" value="<?= $supply['evaluated_suppliers_count'] ?? 85 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">供應商 ESG 評鑑覆蓋率 (%)</label>
                        <input type="number" step="0.1" name="esg_audit_rate_pct" class="form-control" value="<?= $supply['esg_audit_rate_pct'] ?? 92.4 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">評級 A 級優良供應商家數</label>
                        <input type="number" name="grade_a_count" class="form-control" value="<?= $supply['grade_a_count'] ?? 72 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">評級 B 級合格供應商家數</label>
                        <input type="number" name="grade_b_count" class="form-control" value="<?= $supply['grade_b_count'] ?? 11 ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">評級 C 級需限期輔導家數</label>
                        <input type="number" name="grade_c_count" class="form-control text-danger fw-bold" value="<?= $supply['grade_c_count'] ?? 2 ?>" required>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">同仁志工服務總時數 (小時)</label>
                        <input type="number" name="volunteer_hours" class="form-control" value="<?= $supply['volunteer_hours'] ?? 680 ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">社會公益慈善捐贈總額 (NTD)</label>
                        <input type="number" name="donation_amount_twd" class="form-control" value="<?= $supply['donation_amount_twd'] ?? 1200000 ?>" required>
                    </div>
                </div>

                <?php if (Auth::can('social', 'create')): ?>
                <button type="submit" class="btn btn-secondary px-4 fw-semibold">儲存供應鏈與社會公益數據</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
