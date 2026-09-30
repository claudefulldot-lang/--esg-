<?php
use App\Helpers\Security;
use App\Auth;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">溫室氣體活動數據盤查清冊</h4>
        <p class="text-muted small mb-0">依據 ISO 14064-1:2018 與 GHG Protocol 標準分類記錄</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end">
            <?php if (Auth::can('ghg', 'create')): ?>
            <a href="/esg/ghg/create" class="btn btn-success shadow-sm text-nowrap">
                <i class="fa-solid fa-plus-circle me-1"></i>新增盤查活動數據
            </a>
            <?php endif; ?>
            <a href="/esg/reports/export_iso14064?year=<?= $filters['year'] ?>" class="btn btn-outline-success shadow-sm text-nowrap">
                <i class="fa-solid fa-file-excel me-1"></i>匯出 ISO 清冊
            </a>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card p-3 mb-4 shadow-sm">
    <form method="GET" action="/esg/ghg" class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted fw-semibold">盤查年度</label>
            <select name="year" class="form-select form-select-sm">
                <option value="">全部年度</option>
                <?php for ($y = date('Y') + 1; $y >= 2022; $y--): ?>
                    <option value="<?= $y ?>" <?= ($filters['year'] ?? '') == $y ? 'selected' : '' ?>><?= $y ?> 年</option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted fw-semibold">盤查月份</label>
            <select name="month" class="form-select form-select-sm">
                <option value="">全部月份</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($filters['month'] ?? '') == $m ? 'selected' : '' ?>><?= $m ?> 月</option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small text-muted fw-semibold">盤查範疇</label>
            <select name="scope" class="form-select form-select-sm">
                <option value="">全部範疇</option>
                <option value="1" <?= ($filters['scope'] ?? '') == '1' ? 'selected' : '' ?>>範疇一 (直接排放)</option>
                <option value="2" <?= ($filters['scope'] ?? '') == '2' ? 'selected' : '' ?>>範疇二 (能源間接)</option>
                <option value="3" <?= ($filters['scope'] ?? '') == '3' ? 'selected' : '' ?>>範疇三 (其他間接)</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <label class="form-label small text-muted fw-semibold">所屬廠區</label>
            <select name="org_id" class="form-select form-select-sm">
                <option value="">全集團全部廠區</option>
                <?php foreach ($plants as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= ($filters['org_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= Security::e($p['org_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-8 col-md-2">
            <label class="form-label small text-muted fw-semibold">審批狀態</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">全部狀態</option>
                <option value="draft" <?= ($filters['status'] ?? '') == 'draft' ? 'selected' : '' ?>>暫存草稿</option>
                <option value="submitted" <?= ($filters['status'] ?? '') == 'submitted' ? 'selected' : '' ?>>待主管初審</option>
                <option value="approved_l1" <?= ($filters['status'] ?? '') == 'approved_l1' ? 'selected' : '' ?>>初審通過/待複核</option>
                <option value="approved_final" <?= ($filters['status'] ?? '') == 'approved_final' ? 'selected' : '' ?>>最終核准(已封存)</option>
                <option value="rejected" <?= ($filters['status'] ?? '') == 'rejected' ? 'selected' : '' ?>>退回修正</option>
            </select>
        </div>
        <div class="col-4 col-md-1">
            <button type="submit" class="btn btn-primary btn-sm w-100 py-1" title="查詢">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>
    </form>
</div>

<!-- Records Data Table -->
<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle data-table">
            <thead class="table-light">
                <tr>
                    <th>序號</th>
                    <th>廠區名稱</th>
                    <th>期別</th>
                    <th>範疇</th>
                    <th>排放項目與來源</th>
                    <th>活動數據用量</th>
                    <th>使用係數 (kgCO₂e)</th>
                    <th>排放量 (tCO₂e)</th>
                    <th>審批狀態</th>
                    <th>填報/簽核人員</th>
                    <th class="text-center">操作與審查</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $r): ?>
                <tr>
                    <td class="text-muted small">#<?= $r['id'] ?></td>
                    <td class="fw-semibold small"><?= Security::e($r['org_name']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $r['record_year'] ?>/<?= sprintf('%02d', $r['record_month']) ?></span></td>
                    <td>
                        <?php if ($r['scope'] == 1): ?>
                            <span class="badge bg-warning text-dark"><i class="fa-solid fa-fire me-1"></i>範疇一</span>
                        <?php elseif ($r['scope'] == 2): ?>
                            <span class="badge bg-info text-dark"><i class="fa-solid fa-bolt me-1"></i>範疇二</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fa-solid fa-plane me-1"></i>範疇三</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-bold small"><?= Security::e($r['fuel_name']) ?></div>
                        <small class="text-muted"><?= Security::e($r['source_type']) ?></small>
                    </td>
                    <td class="fw-bold">
                        <?= number_format((float)$r['activity_amount'], 2) ?> <small class="text-muted"><?= Security::e($r['activity_unit']) ?></small>
                    </td>
                    <td class="small text-muted">
                        <?= number_format((float)$r['total_factor_co2e'], 4) ?>
                    </td>
                    <td>
                        <span class="fw-bold text-success fs-6"><?= number_format((float)$r['calculated_co2e'], 4) ?></span>
                    </td>
                    <td>
                        <?php
                            $statusMap = [
                                'draft'          => ['label' => '暫存草稿', 'class' => 'bg-secondary'],
                                'submitted'      => ['label' => '待初審', 'class' => 'bg-warning text-dark'],
                                'approved_l1'    => ['label' => '初審通過', 'class' => 'bg-primary'],
                                'approved_final' => ['label' => '最終核准封存', 'class' => 'bg-success'],
                                'rejected'       => ['label' => '退回修正', 'class' => 'bg-danger'],
                            ];
                            $st = $statusMap[$r['status']] ?? ['label' => $r['status'], 'class' => 'bg-light text-dark'];
                        ?>
                        <span class="badge <?= $st['class'] ?>"><?= $st['label'] ?></span>
                        <?php if ($r['status'] === 'rejected' && !empty($r['reject_reason'])): ?>
                            <div class="small text-danger mt-1" style="font-size: 0.75rem;">
                                原因：<?= Security::e($r['reject_reason']) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="small">
                        <div>填報：<?= Security::e($r['submitter_name']) ?></div>
                        <?php if (!empty($r['approver_name'])): ?>
                            <div class="text-muted">核准：<?= Security::e($r['approver_name']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="/esg/workflow/detail?type=ghg&id=<?= $r['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2" title="查核與歷程">
                            <i class="fa-solid fa-eye me-1"></i>查核
                        </a>
                        <?php if (in_array($r['status'], ['draft', 'rejected']) && Auth::can('ghg', 'create')): ?>
                            <form action="/esg/ghg/delete" method="POST" class="d-inline" onsubmit="return confirm('確定要刪除這筆草稿嗎？');">
                                <?= Security::csrfField() ?>
                                <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="刪除">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
