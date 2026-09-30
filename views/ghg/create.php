<?php
use App\Helpers\Security;
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card p-4 shadow-sm">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <h4 class="fw-bold mb-1 text-success"><i class="fa-solid fa-plus-circle me-2"></i>新增溫室氣體盤查活動數據</h4>
                    <p class="text-muted small mb-0">依據 ISO 14064-1:2018 填報各廠區活動用量並自動試算排碳當量</p>
                </div>
                <a href="/esg/ghg" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i>返回清冊
                </a>
            </div>

            <form action="/esg/ghg/create" method="POST" enctype="multipart/form-data" id="ghgForm">
                <?= Security::csrfField() ?>

                <div class="row g-3 mb-4">
                    <!-- Plant Selection -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">所屬廠區 / 部門 <span class="text-danger">*</span></label>
                        <select name="org_id" class="form-select" required>
                            <option value="">-- 請選擇廠區或部門 --</option>
                            <?php foreach ($plants as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= Security::e($p['org_name']) ?> (<?= $p['org_code'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Period -->
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">盤查年度 <span class="text-danger">*</span></label>
                        <select name="record_year" class="form-select" required>
                            <?php for ($y = date('Y') + 1; $y >= 2022; $y--): ?>
                                <option value="<?= $y ?>" <?= $y == date('Y') ? 'selected' : '' ?>><?= $y ?> 年度</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">盤查月份 <span class="text-danger">*</span></label>
                        <select name="record_month" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == (int)date('m') ? 'selected' : '' ?>><?= $m ?> 月</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Scope Selection -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">盤查範疇 <span class="text-danger">*</span></label>
                        <select name="scope" id="scopeSelect" class="form-select" required>
                            <option value="1">範疇一 (直接排放 - 固定/移動/逸散)</option>
                            <option value="2" selected>範疇二 (能源間接 - 外購電力/蒸氣)</option>
                            <option value="3">範疇三 (其他間接 - 價值鏈/差旅)</option>
                        </select>
                    </div>

                    <!-- Source Type -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">排放源細分類 <span class="text-danger">*</span></label>
                        <select name="source_type" id="sourceTypeSelect" class="form-select" required>
                            <option value="Electric">外購一般電力 (台電)</option>
                            <option value="Steam">外購蒸氣/熱能</option>
                        </select>
                    </div>

                    <!-- Factor Dropdown -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">碳排放係數標準 <span class="text-danger">*</span></label>
                        <select name="factor_id" id="factorSelect" class="form-select" required>
                            <option value="">-- 請選擇排碳係數 --</option>
                            <?php foreach ($factors as $f): ?>
                                <option value="<?= $f['id'] ?>" 
                                        data-scope-cat="<?= $f['category'] ?>"
                                        data-unit="<?= Security::e($f['activity_unit']) ?>"
                                        data-factor="<?= (float)$f['total_factor_co2e'] ?>"
                                        data-source="<?= Security::e($f['source_org']) ?>">
                                    <?= Security::e($f['fuel_name']) ?> (<?= (float)$f['total_factor_co2e'] ?> kgCO₂e/<?= Security::e($f['activity_unit']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Activity Amount & Live Calculation Display -->
                <div class="card p-3 bg-light border mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark">活動數據用量 (Activity Amount) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.0001" min="0.0001" name="activity_amount" id="activityAmount" class="form-control form-control-lg" placeholder="例: 12500" required>
                                <span class="input-group-text fw-bold text-success bg-white" id="unitDisplay">單位</span>
                            </div>
                            <small class="text-muted" id="factorSourceDisplay">係數來源：尚未選擇</small>
                        </div>

                        <div class="col-md-2 text-center d-none d-md-block">
                            <i class="fa-solid fa-arrow-right fs-3 text-muted"></i>
                        </div>

                        <div class="col-md-5">
                            <div class="p-3 bg-white border border-success rounded-3 text-center shadow-sm">
                                <span class="text-muted small fw-semibold">系統自動即時試算碳排放量</span>
                                <h3 class="fw-bold text-success my-1">
                                    <span id="calculatedCo2eDisplay">0.0000</span> <small class="fs-6 text-muted">tCO₂e</small>
                                </h3>
                                <small class="text-muted">公式：(活動用量 × 綜合係數) ÷ 1,000</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attachment Upload -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">佐證憑證單據上傳 (發票、電單、檢驗報告)</label>
                    <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.xlsx,.docx">
                    <div class="form-text text-muted">支援 PDF、JPG、PNG、Excel 檔案，大小限制 20MB。</div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-column flex-sm-row justify-content-sm-end gap-2 border-top pt-3">
                    <button type="submit" name="action_type" value="draft" class="btn btn-outline-secondary px-4 w-100 w-sm-auto">
                        <i class="fa-solid fa-floppy-disk me-1"></i>暫存為草稿
                    </button>
                    <button type="submit" name="action_type" value="submit" class="btn btn-success px-4 fw-semibold shadow-sm w-100 w-sm-auto">
                        <i class="fa-solid fa-paper-plane me-1"></i>確認送出主管審核
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const scopeSelect = document.getElementById('scopeSelect');
    const sourceTypeSelect = document.getElementById('sourceTypeSelect');
    const factorSelect = document.getElementById('factorSelect');
    const activityAmount = document.getElementById('activityAmount');
    const unitDisplay = document.getElementById('unitDisplay');
    const factorSourceDisplay = document.getElementById('factorSourceDisplay');
    const calculatedCo2eDisplay = document.getElementById('calculatedCo2eDisplay');

    // Dynamic source type options based on scope
    scopeSelect.addEventListener('change', function() {
        const val = this.value;
        sourceTypeSelect.innerHTML = '';

        if (val === '1') {
            sourceTypeSelect.innerHTML = `
                <option value="Stationary">固定燃燒源 (鍋爐/天然氣/柴油)</option>
                <option value="Mobile">移動燃燒源 (公務車/堆高機)</option>
                <option value="Fugitive">製程與逸散源 (冷媒填充/化糞池/滅火器)</option>
            `;
        } else if (val === '2') {
            sourceTypeSelect.innerHTML = `
                <option value="Electric">外購一般電力 (台電)</option>
                <option value="Steam">外購蒸氣/熱能</option>
            `;
        } else {
            sourceTypeSelect.innerHTML = `
                <option value="ValueChain">原物料陸運/廢棄物委外掩埋</option>
                <option value="BusinessTravel">員工公務差旅/航空差旅</option>
            `;
        }
    });

    // Update unit & factor details on factor select
    factorSelect.addEventListener('change', updateCalculation);
    activityAmount.addEventListener('input', updateCalculation);

    function updateCalculation() {
        const selectedOpt = factorSelect.selectedOptions[0];
        if (!selectedOpt || !selectedOpt.value) {
            unitDisplay.innerText = '單位';
            factorSourceDisplay.innerText = '係數來源：尚未選擇';
            calculatedCo2eDisplay.innerText = '0.0000';
            return;
        }

        const unit = selectedOpt.getAttribute('data-unit') || '';
        const factor = parseFloat(selectedOpt.getAttribute('data-factor')) || 0;
        const source = selectedOpt.getAttribute('data-source') || '';
        const amount = parseFloat(activityAmount.value) || 0;

        unitDisplay.innerText = unit;
        factorSourceDisplay.innerText = `係數來源：${source} (${factor} kgCO₂e/${unit})`;

        if (amount > 0 && factor > 0) {
            const tco2e = (amount * factor) / 1000.0;
            calculatedCo2eDisplay.innerText = tco2e.toFixed(4);
        } else {
            calculatedCo2eDisplay.innerText = '0.0000';
        }
    }
});
</script>
