<?php
use App\Helpers\Security;
use App\Auth;

$currentUser = Auth::user();
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">待辦任務與審批簽核中心</h4>
        <p class="text-muted small mb-0">落實各部門活動數據多級簽核流、憑證單據審閱與防偽封存</p>
    </div>
</div>

<!-- Section 1: 待主管初審之活動數據 -->
<div class="card p-4 shadow-sm mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="fw-bold mb-0 text-warning">
            <i class="fa-solid fa-clock-rotate-left me-2"></i>待部門初審之盤查活動數據 (Submitted)
        </h5>
        <span class="badge bg-warning text-dark"><?= count($pendingGhgRecords) ?> 筆待審核</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>序號</th>
                    <th>廠區/部門</th>
                    <th>盤查期別</th>
                    <th>範疇</th>
                    <th>排放源項目</th>
                    <th>活動數據</th>
                    <th>試算碳排量 (tCO₂e)</th>
                    <th>填報人員</th>
                    <th>提交時間</th>
                    <th class="text-center">簽核審查</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingGhgRecords)): ?>
                    <tr><td colspan="10" class="text-center text-muted py-3">目前無待初審之活動數據</td></tr>
                <?php else: ?>
                    <?php foreach ($pendingGhgRecords as $r): ?>
                    <tr>
                        <td class="text-muted small">#<?= $r['id'] ?></td>
                        <td class="fw-semibold small"><?= Security::e($r['org_name']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $r['record_year'] ?>/<?= sprintf('%02d', $r['record_month']) ?></span></td>
                        <td><span class="badge bg-info-subtle text-info border">範疇<?= $r['scope'] ?></span></td>
                        <td class="fw-bold small"><?= Security::e($r['fuel_name']) ?></td>
                        <td><?= number_format((float)$r['activity_amount'], 2) ?> <?= Security::e($r['activity_unit']) ?></td>
                        <td class="fw-bold text-success fs-6"><?= number_format((float)$r['calculated_co2e'], 4) ?></td>
                        <td class="small"><?= Security::e($r['submitter_name']) ?></td>
                        <td class="small text-muted"><?= substr($r['created_at'], 0, 16) ?></td>
                        <td class="text-center">
                            <a href="/esg/workflow/detail?type=ghg&id=<?= $r['id'] ?>" class="btn btn-warning btn-sm text-dark fw-semibold px-3 shadow-sm">
                                <i class="fa-solid fa-clipboard-check me-1"></i>主管初審
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 2: 待 ESG 委員會複核與最終封存之數據 -->
<div class="card p-4 shadow-sm mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h5 class="fw-bold mb-0 text-primary">
            <i class="fa-solid fa-stamp me-2"></i>待 ESG 委員會複核與數據封存 (Approved L1)
        </h5>
        <span class="badge bg-primary"><?= count($l1GhgRecords) ?> 筆待複核</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>序號</th>
                    <th>廠區/部門</th>
                    <th>盤查期別</th>
                    <th>範疇</th>
                    <th>排放源項目</th>
                    <th>活動數據</th>
                    <th>試算碳排量 (tCO₂e)</th>
                    <th>初審主管</th>
                    <th class="text-center">最終核准</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($l1GhgRecords)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-3">目前無待最終複核之活動數據</td></tr>
                <?php else: ?>
                    <?php foreach ($l1GhgRecords as $r): ?>
                    <tr>
                        <td class="text-muted small">#<?= $r['id'] ?></td>
                        <td class="fw-semibold small"><?= Security::e($r['org_name']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= $r['record_year'] ?>/<?= sprintf('%02d', $r['record_month']) ?></span></td>
                        <td><span class="badge bg-info-subtle text-info border">範疇<?= $r['scope'] ?></span></td>
                        <td class="fw-bold small"><?= Security::e($r['fuel_name']) ?></td>
                        <td><?= number_format((float)$r['activity_amount'], 2) ?> <?= Security::e($r['activity_unit']) ?></td>
                        <td class="fw-bold text-success fs-6"><?= number_format((float)$r['calculated_co2e'], 4) ?></td>
                        <td class="small"><?= Security::e($r['approver_name'] ?? '主管') ?></td>
                        <td class="text-center">
                            <a href="/esg/workflow/detail?type=ghg&id=<?= $r['id'] ?>" class="btn btn-primary btn-sm fw-semibold px-3 shadow-sm">
                                <i class="fa-solid fa-lock me-1"></i>委員會複核與封存
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Section 3: 排程自動派發之填報任務工單 -->
<div class="card p-4 shadow-sm">
    <h5 class="fw-bold mb-3 text-secondary">
        <i class="fa-solid fa-list-check me-2"></i>每月填報排程任務工單 (Workflow Tasks)
    </h5>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle data-table">
            <thead class="table-light">
                <tr>
                    <th>工單號</th>
                    <th>工作任務說明</th>
                    <th>廠區/單位</th>
                    <th>指派填報人員</th>
                    <th>期別</th>
                    <th>模組</th>
                    <th>截止期限</th>
                    <th>工單狀態</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tasks as $t): ?>
                <tr>
                    <td class="text-muted small">#TK-<?= $t['id'] ?></td>
                    <td class="fw-bold small"><?= Security::e($t['task_title']) ?></td>
                    <td><?= Security::e($t['org_name']) ?></td>
                    <td><?= Security::e($t['assigned_user_name']) ?></td>
                    <td><?= $t['period_year'] ?>/<?= sprintf('%02d', $t['period_month']) ?></td>
                    <td><span class="badge bg-secondary"><?= strtoupper($t['module_type']) ?></span></td>
                    <td class="small text-danger fw-semibold"><i class="fa-regular fa-calendar-xmark me-1"></i><?= $t['due_date'] ?></td>
                    <td>
                        <span class="badge badge-status-<?= $t['status'] ?>"><?= $t['status'] ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
