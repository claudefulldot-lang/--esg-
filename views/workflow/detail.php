<?php
use App\Helpers\Security;
use App\Auth;

$currentUser = Auth::user();
?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">活動數據查核與簽核審批</h4>
                <p class="text-muted small mb-0">單號 #<?= $record['id'] ?> | 盤查期別：<?= $record['record_year'] ?> 年 <?= $record['record_month'] ?> 月</p>
            </div>
            <a href="/esg/workflow" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>返回待辦中心
            </a>
        </div>

        <!-- Card 1: Record Details -->
        <div class="card p-4 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                <h5 class="fw-bold mb-0 text-success">
                    <i class="fa-solid fa-file-lines me-2"></i>盤查活動數據詳情
                </h5>
                <div>
                    <?php
                        $statusMap = [
                            'draft'          => ['label' => '暫存草稿', 'class' => 'bg-secondary'],
                            'submitted'      => ['label' => '已提交待初審', 'class' => 'bg-warning text-dark'],
                            'approved_l1'    => ['label' => '初審通過 (待委員會複核)', 'class' => 'bg-primary'],
                            'approved_final' => ['label' => '最終核准 (已鎖定防篡改)', 'class' => 'bg-success'],
                            'rejected'       => ['label' => '已退回修正', 'class' => 'bg-danger']
                        ];
                        $st = $statusMap[$record['status']] ?? ['label' => $record['status'], 'class' => 'bg-light text-dark'];
                    ?>
                    <span class="badge <?= $st['class'] ?> fs-6 px-3 py-2"><?= $st['label'] ?></span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">所屬廠區 / 部門</div>
                    <div class="fw-bold fs-6 mt-1"><?= Security::e($record['org_name']) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">盤查範疇</div>
                    <div class="fw-bold fs-6 mt-1">
                        範疇 <?= $record['scope'] ?> (<?= Security::e($record['source_type']) ?>)
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">活動項目名稱</div>
                    <div class="fw-bold fs-6 text-primary mt-1"><?= Security::e($record['fuel_name']) ?></div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">活動數據用量</div>
                    <div class="fw-bold fs-5 mt-1">
                        <?= number_format((float)$record['activity_amount'], 4) ?> <span class="fs-6 text-muted"><?= Security::e($record['activity_unit']) ?></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">引用排放係數</div>
                    <div class="fw-bold fs-6 mt-1">
                        <?= number_format((float)$record['total_factor_co2e'], 6) ?> <small class="text-muted">kgCO₂e/<?= Security::e($record['activity_unit']) ?></small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">試算碳排當量</div>
                    <div class="fw-bold fs-4 text-success mt-1">
                        <?= number_format((float)$record['calculated_co2e'], 4) ?> <span class="fs-6 text-muted">tCO₂e</span>
                    </div>
                </div>

                <div class="col-md-6 border-top pt-3">
                    <div class="text-muted small">填報人員：<strong class="text-dark"><?= Security::e($record['submitter_name']) ?></strong></div>
                    <small class="text-muted">建立時間：<?= $record['created_at'] ?></small>
                </div>
                <div class="col-md-6 border-top pt-3">
                    <div class="text-muted small">最後審核主管：<strong class="text-dark"><?= Security::e($record['approver_name'] ?? '尚未簽核') ?></strong></div>
                    <?php if (!empty($record['reject_reason'])): ?>
                        <div class="text-danger small mt-1">退件備註：<?= Security::e($record['reject_reason']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Card 2: 佐證單據附件查驗 -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 text-secondary">
                <i class="fa-solid fa-paperclip me-2 text-primary"></i>佐證憑證單據查核 (發票/電費單/清運聯單)
            </h5>

            <?php if (empty($attachments)): ?>
                <div class="alert alert-warning py-3 mb-0 small">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><strong>無上傳附件憑證！</strong> 建議審核主管確認數據來源真偽或要求填報人補件。
                </div>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($attachments as $att): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <i class="fa-solid fa-file-pdf fs-4 text-danger me-3"></i>
                                <strong><?= Security::e($att['file_name']) ?></strong>
                                <span class="text-muted small ms-2">(<?= round($att['file_size'] / 1024, 1) ?> KB)</span>
                                <div class="small text-muted mt-1">
                                    上傳者：<?= Security::e($att['uploader_name']) ?> | 上傳時間：<?= $att['uploaded_at'] ?>
                                </div>
                            </div>
                            <a href="/esg/attachments/download?id=<?= (int)$att['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fa-solid fa-download me-1"></i>下載/檢視憑證
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Card 3: 簽核歷程軌跡 (Audit Log) -->
        <div class="card p-4 shadow-sm mb-4">
            <h5 class="fw-bold mb-3 text-secondary">
                <i class="fa-solid fa-timeline me-2 text-info"></i>審批與工作流簽核軌跡 (Workflow Audit Trail)
            </h5>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>時間</th>
                            <th>簽核動作</th>
                            <th>簽核主管 / 人員</th>
                            <th>角色</th>
                            <th>批註意見與退件說明</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr><td colspan="5" class="text-center text-muted">尚無簽核軌跡紀錄</td></tr>
                        <?php else: ?>
                            <?php foreach ($logs as $l): ?>
                                <tr>
                                    <td class="small text-muted"><?= $l['created_at'] ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= Security::e($l['action']) ?></span>
                                    </td>
                                    <td class="fw-semibold small"><?= Security::e($l['approver_name']) ?></td>
                                    <td class="small text-muted"><?= Security::e($l['role_name']) ?></td>
                                    <td class="small"><?= Security::e($l['comments'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card 4: 審核操作區 (Role Action Box) -->
        <div class="card p-4 shadow-sm border-2 border-primary">
            <h5 class="fw-bold mb-3 text-primary">
                <i class="fa-solid fa-gavel me-2"></i>簽核決策操作
            </h5>

            <?php if ($record['status'] === 'approved_final'): ?>
                <div class="alert alert-success d-flex align-items-center mb-0">
                    <i class="fa-solid fa-shield-halved fs-3 me-3"></i>
                    <div>
                        <h6 class="fw-bold mb-1">此筆盤查活動數據已獲 ESG 委員會最終核准並封存！</h6>
                        <small>數據已納入正式計算引擎，處於唯讀防篡改鎖定狀態。</small>
                    </div>
                </div>

            <?php elseif ($record['status'] === 'submitted' && (Auth::can('workflow', 'approve_l1') || Auth::can('all'))): ?>
                <!-- 初審主管操作 -->
                <p class="text-muted small">此筆數據目前處於【待初審】狀態，請核對憑證單據後進行初審核准或退回修正。</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <form action="/esg/workflow/approve" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="type" value="ghg">
                            <input type="hidden" name="id" value="<?= $record['id'] ?>">
                            <input type="hidden" name="level" value="l1">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">初審通過批註意見</label>
                                <input type="text" name="comment" class="form-control" value="活動用量與單據核對無誤，初審通過送委員會複核" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="fa-solid fa-check-circle me-1"></i>核准通過初審 (Approve Level 1)
                            </button>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <form action="/esg/workflow/reject" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="type" value="ghg">
                            <input type="hidden" name="id" value="<?= $record['id'] ?>">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-danger">退件補正原因 <span class="text-danger">*</span></label>
                                <input type="text" name="reject_reason" class="form-control border-danger" placeholder="請詳細說明退回修正原因，例如發票模糊或用量不符" required>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-semibold" onclick="return confirm('確定要退回此筆活動數據嗎？');">
                                <i class="fa-solid fa-xmark me-1"></i>退回修正 (Reject)
                            </button>
                        </form>
                    </div>
                </div>

            <?php elseif ($record['status'] === 'approved_l1' && (Auth::can('workflow', 'approve_final') || Auth::can('all'))): ?>
                <!-- 委員會複核操作 -->
                <p class="text-muted small">此筆數據已通過部門初審，處於【待委員會複核】狀態，核准後將正式納入年度盤查並鎖定。</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <form action="/esg/workflow/approve" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="type" value="ghg">
                            <input type="hidden" name="id" value="<?= $record['id'] ?>">
                            <input type="hidden" name="level" value="final">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold">委員會複核批註</label>
                                <input type="text" name="comment" class="form-control" value="ESG推動小組複核無誤，最終核准並封存數據" required>
                            </div>
                            <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                                <i class="fa-solid fa-lock me-1"></i>最終核准並封存數據 (Final Approve & Lock)
                            </button>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <form action="/esg/workflow/reject" method="POST">
                            <?= Security::csrfField() ?>
                            <input type="hidden" name="type" value="ghg">
                            <input type="hidden" name="id" value="<?= $record['id'] ?>">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-danger">退件原因 <span class="text-danger">*</span></label>
                                <input type="text" name="reject_reason" class="form-control border-danger" placeholder="請填寫委員會退件要求" required>
                            </div>
                            <button type="submit" class="btn btn-outline-danger w-100 py-2 fw-semibold" onclick="return confirm('確定要退回此筆活動數據嗎？');">
                                <i class="fa-solid fa-rotate-left me-1"></i>委員會退回修正
                            </button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <div class="alert alert-secondary mb-0 small">
                    <i class="fa-solid fa-lock me-2"></i>目前狀態：<strong><?= $record['status'] ?></strong>。您無權在此階段執行審核操作或此數據尚未送審。
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
