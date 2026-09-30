<?php
use App\Helpers\Security;
use App\Auth;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">碳排放係數庫管理 (Emission Factor Database)</h4>
        <p class="text-muted small mb-0">整合環境部、能源局及 IPCC 官方基準數據，支援自訂係數與版本歷史管控</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if (Auth::can('ghg', 'create')): ?>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#factorModal" onclick="openFactorModal()">
            <i class="fa-solid fa-plus-circle me-1"></i>新增自訂排放係數
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Category Tabs -->
<div class="mb-4">
    <div class="nav nav-pills bg-white p-2 rounded-3 shadow-sm">
        <a class="nav-link <?= empty($selectedCategory) ? 'active' : '' ?>" href="/esg/factors">全部係數</a>
        <a class="nav-link <?= $selectedCategory === 'Scope1_Fuel' ? 'active' : '' ?>" href="/esg/factors?category=Scope1_Fuel">範疇一：固定燃燒</a>
        <a class="nav-link <?= $selectedCategory === 'Scope1_Mobile' ? 'active' : '' ?>" href="/esg/factors?category=Scope1_Mobile">範疇一：移動燃燒</a>
        <a class="nav-link <?= $selectedCategory === 'Scope1_Fugitive' ? 'active' : '' ?>" href="/esg/factors?category=Scope1_Fugitive">範疇一：逸散源(冷媒/化糞池)</a>
        <a class="nav-link <?= $selectedCategory === 'Scope2_Electricity' ? 'active' : '' ?>" href="/esg/factors?category=Scope2_Electricity">範疇二：電力</a>
        <a class="nav-link <?= $selectedCategory === 'Scope3' ? 'active' : '' ?>" href="/esg/factors?category=Scope3">範疇三：其他間接</a>
    </div>
</div>

<!-- Factor Table -->
<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle data-table">
            <thead class="table-light">
                <tr>
                    <th>序號</th>
                    <th>類別</th>
                    <th>排放源燃料/項目名稱</th>
                    <th>活動單位</th>
                    <th>綜合當量係數 (kgCO₂e/單位)</th>
                    <th>CO₂ 係數</th>
                    <th>CH₄ 係數</th>
                    <th>N₂O 係數</th>
                    <th>發布機構</th>
                    <th>適用年度</th>
                    <th>狀態</th>
                    <?php if (Auth::can('ghg', 'create')): ?>
                    <th class="text-center">操作</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factors as $f): ?>
                <tr>
                    <td class="text-muted small">#<?= $f['id'] ?></td>
                    <td><span class="badge bg-light text-dark border"><?= Security::e($f['category']) ?></span></td>
                    <td class="fw-bold small"><?= Security::e($f['fuel_name']) ?></td>
                    <td><span class="badge bg-secondary-subtle text-secondary"><?= Security::e($f['activity_unit']) ?></span></td>
                    <td class="fw-bold text-success fs-6"><?= number_format((float)$f['total_factor_co2e'], 6) ?></td>
                    <td class="small text-muted"><?= number_format((float)$f['co2_factor'], 6) ?></td>
                    <td class="small text-muted"><?= number_format((float)$f['ch4_factor'], 6) ?></td>
                    <td class="small text-muted"><?= number_format((float)$f['n2o_factor'], 6) ?></td>
                    <td class="small"><?= Security::e($f['source_org']) ?></td>
                    <td><span class="badge bg-info-subtle text-info border"><?= $f['applicable_year'] ?></span></td>
                    <td>
                        <?= $f['is_active'] ? '<span class="badge bg-success">有效啟用</span>' : '<span class="badge bg-danger">停用</span>' ?>
                    </td>
                    <?php if (Auth::can('ghg', 'create')): ?>
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-sm py-0 px-2" onclick='editFactor(<?= Security::jsonForHtml($f) ?>)'>
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add/Edit Factor -->
<div class="modal fade" id="factorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/esg/factors/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="factor_id" id="modalFactorId" value="">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="factorModalTitle">新增自訂碳排放係數</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">係數類別 <span class="text-danger">*</span></label>
                            <select name="category" id="modalCategory" class="form-select" required>
                                <option value="Scope1_Fuel">Scope1_Fuel (範疇一固定燃燒)</option>
                                <option value="Scope1_Mobile">Scope1_Mobile (範疇一移動燃燒)</option>
                                <option value="Scope1_Fugitive">Scope1_Fugitive (範疇一逸散源)</option>
                                <option value="Scope2_Electricity">Scope2_Electricity (範疇二電力)</option>
                                <option value="Scope2_Steam">Scope2_Steam (範疇二蒸氣)</option>
                                <option value="Scope3">Scope3 (範疇三其他間接)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">排放源項目名稱 <span class="text-danger">*</span></label>
                            <input type="text" name="fuel_name" id="modalFuelName" class="form-control" placeholder="例: 特殊供應商原物料柴油" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">活動數據單位 <span class="text-danger">*</span></label>
                            <input type="text" name="activity_unit" id="modalUnit" class="form-control" placeholder="例: L, m3, kWh, ton, kg" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">綜合排碳係數 (kgCO₂e) <span class="text-danger">*</span></label>
                            <input type="number" step="0.000001" name="total_factor_co2e" id="modalTotalCo2e" class="form-control fw-bold text-success" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">適用年度</label>
                            <input type="number" name="applicable_year" id="modalYear" class="form-control" value="<?= date('Y') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small text-muted">CO₂ 分項係數</label>
                            <input type="number" step="0.000001" name="co2_factor" id="modalCo2" class="form-control" value="0.000000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">CH₄ 分項係數</label>
                            <input type="number" step="0.000001" name="ch4_factor" id="modalCh4" class="form-control" value="0.000000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted">N₂O 分項係數</label>
                            <input type="number" step="0.000001" name="n2o_factor" id="modalN2o" class="form-control" value="0.000000">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">數據發布/出處機構 <span class="text-danger">*</span></label>
                            <input type="text" name="source_org" id="modalSourceOrg" class="form-control" placeholder="例: 環境部碳足跡庫 / 自訂盤查報告" required>
                        </div>
                        <div class="col-md-4 d-flex align-items-center mt-4">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="is_active" id="modalIsActive" value="1" checked>
                                <label class="form-check-label fs-6 fw-semibold">有效啟用</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-success fw-semibold">確認儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openFactorModal() {
    document.getElementById('factorModalTitle').innerText = '新增自訂碳排放係數';
    document.getElementById('modalFactorId').value = '';
    document.getElementById('modalFuelName').value = '';
    document.getElementById('modalUnit').value = '';
    document.getElementById('modalTotalCo2e').value = '';
    document.getElementById('modalCo2').value = '0.000000';
    document.getElementById('modalCh4').value = '0.000000';
    document.getElementById('modalN2o').value = '0.000000';
    document.getElementById('modalSourceOrg').value = '台灣環境部 1.2版';
    document.getElementById('modalIsActive').checked = true;
}

function editFactor(f) {
    document.getElementById('factorModalTitle').innerText = '編輯碳排放係數 (#' + f.id + ')';
    document.getElementById('modalFactorId').value = f.id;
    document.getElementById('modalCategory').value = f.category;
    document.getElementById('modalFuelName').value = f.fuel_name;
    document.getElementById('modalUnit').value = f.activity_unit;
    document.getElementById('modalTotalCo2e').value = f.total_factor_co2e;
    document.getElementById('modalCo2').value = f.co2_factor;
    document.getElementById('modalCh4').value = f.ch4_factor;
    document.getElementById('modalN2o').value = f.n2o_factor;
    document.getElementById('modalSourceOrg').value = f.source_org;
    document.getElementById('modalYear').value = f.applicable_year;
    document.getElementById('modalIsActive').checked = parseInt(f.is_active) === 1;

    const modal = new bootstrap.Modal(document.getElementById('factorModal'));
    modal.show();
}
</script>
