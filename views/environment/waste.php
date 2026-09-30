<?php
use App\Helpers\Security;
use App\Auth;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">事業廢棄物處置管理 (GRI 306)</h4>
        <p class="text-muted small mb-0">依環保署代碼分類記錄一般事業廢棄物 (D類)、有害事業廢棄物 (C類)、處置方式與清運聯單</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if (Auth::can('environment', 'create')): ?>
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#wasteModal">
            <i class="fa-solid fa-plus-circle me-1"></i>新增廢棄物清運紀錄
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
                    <th>廢棄物分類</th>
                    <th>廢棄物名稱</th>
                    <th>產生數量</th>
                    <th>最終處置方式</th>
                    <th>環保署清運聯單編號</th>
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
                        <?php if ($r['waste_category'] === 'hazardous_C'): ?>
                            <span class="badge bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>有害事業 (C類)</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">一般事業 (D類)</span>
                        <?php endif; ?>
                    </td>
                    <td class="fw-bold small"><?= Security::e($r['waste_name']) ?></td>
                    <td class="fw-bold fs-6">
                        <?= number_format((float)$r['amount'], 4) ?> <small class="text-muted"><?= Security::e($r['unit']) ?></small>
                    </td>
                    <td>
                        <?php
                            $dispMap = [
                                'incineration' => ['label' => '焚化處理', 'class' => 'bg-warning text-dark'],
                                'landfill'     => ['label' => '衛生掩埋', 'class' => 'bg-secondary'],
                                'physical'     => ['label' => '物理/化學處理', 'class' => 'bg-info text-dark'],
                                'recycling'    => ['label' => '資源再利用/循環', 'class' => 'bg-success']
                            ];
                            $disp = $dispMap[$r['disposal_method']] ?? ['label' => $r['disposal_method'], 'class' => 'bg-light text-dark'];
                        ?>
                        <span class="badge <?= $disp['class'] ?>"><?= $disp['label'] ?></span>
                    </td>
                    <td>
                        <?php if (!empty($r['tracking_number'])): ?>
                            <span class="badge bg-light text-dark border font-monospace"><?= Security::e($r['tracking_number']) ?></span>
                        <?php else: ?>
                            <span class="text-muted small">無單號</span>
                        <?php endif; ?>
                    </td>
                    <td class="small"><?= Security::e($r['submitter_name']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Waste Record -->
<div class="modal fade" id="wasteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/esg/environment/waste/store" method="POST" enctype="multipart/form-data">
                <?= Security::csrfField() ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">新增事業廢棄物處置紀錄</h5>
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
                            <input type="number" name="record_year" class="form-control" value="<?= date('Y') ?>" required>
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
                            <label class="form-label fw-semibold">廢棄物類別 <span class="text-danger">*</span></label>
                            <select name="waste_category" class="form-select" required>
                                <option value="general_D">一般事業廢棄物 (D類)</option>
                                <option value="hazardous_C">有害事業廢棄物 (C類)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">廢棄物名稱 <span class="text-danger">*</span></label>
                            <input type="text" name="waste_name" class="form-control" placeholder="例: 廢有機溶劑、廢紙箱、下腳料" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">產生重量/數量 <span class="text-danger">*</span></label>
                            <input type="number" step="0.0001" min="0.0001" name="amount" class="form-control" placeholder="例: 12.5" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">單位</label>
                            <input type="text" name="unit" class="form-control" value="ton" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">最終處置方式 <span class="text-danger">*</span></label>
                            <select name="disposal_method" class="form-select" required>
                                <option value="recycling">資源回收 / 物理再利用 (Recycling)</option>
                                <option value="incineration">焚化處理 (Incineration)</option>
                                <option value="physical">物理或化學固化處理 (Physical)</option>
                                <option value="landfill">衛生掩埋 (Landfill)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">環保署清運三聯單號 (Tracking Number)</label>
                            <input type="text" name="tracking_number" class="form-control" placeholder="例: EPA-202603-D012">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">佐證聯單憑證上傳</label>
                            <input type="file" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.xlsx">
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
