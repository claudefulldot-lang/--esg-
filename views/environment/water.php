<?php
use App\Helpers\Security;
use App\Auth;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">水資源與放流水質監控管理 (GRI 303)</h4>
        <p class="text-muted small mb-0">追蹤自來水度數、中水回收再利用量、放流排放量及放流水質指標 (COD, BOD, SS)</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if (Auth::can('environment', 'create')): ?>
        <button class="btn btn-info text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#waterModal">
            <i class="fa-solid fa-plus-circle me-1"></i>新增水資源/水質數據
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle data-table">
            <thead class="table-light">
                <tr>
                    <th>序號</th>
                    <th>廠區</th>
                    <th>年月</th>
                    <th>水資源類型</th>
                    <th>數量/度數</th>
                    <th>水質 COD (mg/L)</th>
                    <th>水質 BOD (mg/L)</th>
                    <th>水質 SS (mg/L)</th>
                    <th>備註說明</th>
                    <th>填報人員</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td class="text-muted small">#<?= $r['id'] ?></td>
                    <td class="fw-semibold small"><?= Security::e($r['org_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $r['record_year'] ?>/<?= sprintf('%02d', $r['record_month']) ?></span></td>
                    <td>
                        <?php
                            $typeMap = [
                                'tap_water'         => ['label' => '自來水取用', 'class' => 'bg-primary'],
                                'ground_water'      => ['label' => '地下水抽取', 'class' => 'bg-secondary'],
                                'recycled_water'    => ['label' => '回收水再利用', 'class' => 'bg-success'],
                                'wastewater'        => ['label' => '放流水排放', 'class' => 'bg-warning text-dark'],
                                'solar_power'       => ['label' => '太陽能綠電', 'class' => 'bg-info text-dark'],
                                'green_electricity' => ['label' => '綠電轉供(CPPA)', 'class' => 'bg-success']
                            ];
                            $badge = $typeMap[$r['record_type']] ?? ['label' => $r['record_type'], 'class' => 'bg-light text-dark'];
                        ?>
                        <span class="badge <?= $badge['class'] ?>"><?= $badge['label'] ?></span>
                    </td>
                    <td class="fw-bold fs-6">
                        <?= number_format((float)$r['amount'], 2) ?> <small class="text-muted"><?= Security::e($r['unit']) ?></small>
                    </td>
                    <td><?= $r['cod_val'] !== null ? number_format((float)$r['cod_val'], 2) : '<span class="text-muted">-</span>' ?></td>
                    <td><?= $r['bod_val'] !== null ? number_format((float)$r['bod_val'], 2) : '<span class="text-muted">-</span>' ?></td>
                    <td><?= $r['ss_val'] !== null ? number_format((float)$r['ss_val'], 2) : '<span class="text-muted">-</span>' ?></td>
                    <td class="small text-muted"><?= Security::e($r['notes'] ?? '') ?></td>
                    <td class="small"><?= Security::e($r['submitter_name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Water Data -->
<div class="modal fade" id="waterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/esg/environment/water/store" method="POST" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold">新增水資源與水質數據</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">廠區 / 部門 <span class="text-danger">*</span></label>
                            <select name="org_id" class="form-select" required>
                                <?php foreach ($plants as $p): ?>
                                    <option value="<?= $p['id'] ?>"><?= Security::e($p['org_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">年度</label>
                            <input type="number" name="record_year" class="form-select" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">月份</label>
                            <select name="record_month" class="form-select" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m == (int)date('m') ? 'selected' : '' ?>><?= $m ?> 月</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">資源指標類別 <span class="text-danger">*</span></label>
                            <select name="record_type" class="form-select" required>
                                <option value="tap_water">自來水取用 (度)</option>
                                <option value="recycled_water">中水/冷卻水回收再利用 (度)</option>
                                <option value="wastewater">放流水排放量 (度)</option>
                                <option value="solar_power">廠房太陽能綠電發電量 (kWh)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">數量用量 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" name="amount" class="form-control" placeholder="例: 4500" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">單位</label>
                            <input type="text" name="unit" class="form-control" value="度" required>
                        </div>
                    </div>

                    <div class="card p-3 bg-light border mb-3">
                        <h6 class="fw-bold mb-2 text-secondary"><i class="fa-solid fa-flask-vial me-1"></i>放流水檢驗水質 (放流水必填檢驗值)</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small text-muted">化學需氧量 COD (mg/L)</label>
                                <input type="number" step="0.01" name="cod_val" class="form-control" placeholder="例: 45.20">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">生化需氧量 BOD (mg/L)</label>
                                <input type="number" step="0.01" name="bod_val" class="form-control" placeholder="例: 18.50">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small text-muted">懸浮固體 SS (mg/L)</label>
                                <input type="number" step="0.01" name="ss_val" class="form-control" placeholder="例: 22.00">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">備註說明</label>
                        <input type="text" name="notes" class="form-control" placeholder="例: 水單收據、水質檢測委外報告合格">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">佐證憑證單據上傳</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.xlsx">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-info text-white fw-semibold">確認登記</button>
                </div>
            </form>
        </div>
    </div>
</div>
