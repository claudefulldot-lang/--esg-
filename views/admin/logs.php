<?php
use App\Helpers\Security;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">系統安全與操作審計日誌 (ADM-04)</h4>
        <p class="text-muted small mb-0">全方位記錄登入、數據異動、審批流轉與報表匯出軌跡，具備防篡改性</p>
    </div>
</div>

<div class="card p-3 shadow-sm mb-4">
    <form method="GET" action="/esg/admin/logs" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label small text-muted fw-semibold">模組篩選</label>
            <input type="text" name="module" class="form-control form-control-sm" placeholder="例: GHG, AUTH, SOCIAL" value="<?= Security::e($module ?? '') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted fw-semibold">操作行為</label>
            <input type="text" name="action" class="form-control form-control-sm" placeholder="例: LOGIN_SUCCESS, CREATE_GHG_RECORD" value="<?= Security::e($action ?? '') ?>">
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary btn-sm me-2"><i class="fa-solid fa-filter me-1"></i>篩選</button>
            <a href="/esg/admin/logs" class="btn btn-outline-secondary btn-sm">清除重置</a>
        </div>
    </form>
</div>

<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle table-sm data-table">
            <thead class="table-light">
                <tr>
                    <th>日誌ID</th>
                    <th>操作時間</th>
                    <th>操作帳號</th>
                    <th>來源 IP</th>
                    <th>所屬模組</th>
                    <th>行為代碼</th>
                    <th>影響記錄ID</th>
                    <th>變更詳情</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="text-muted small">#<?= $l['id'] ?></td>
                    <td class="small"><?= $l['created_at'] ?></td>
                    <td class="fw-semibold small"><i class="fa-solid fa-user me-1 text-muted"></i><?= Security::e($l['username'] ?? 'GUEST') ?></td>
                    <td class="small font-monospace text-muted"><?= Security::e($l['ip_address']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= Security::e($l['module']) ?></span></td>
                    <td><span class="badge bg-secondary"><?= Security::e($l['action']) ?></span></td>
                    <td class="small text-muted"><?= $l['record_id'] ? '#' . $l['record_id'] : '-' ?></td>
                    <td class="small">
                        <?php if (!empty($l['new_values'])): ?>
                            <button class="btn btn-outline-info btn-sm py-0 px-2" style="font-size: 0.72rem;" onclick='viewDiff(<?= Security::jsonForHtml($l['old_values']) ?>, <?= Security::jsonForHtml($l['new_values']) ?>)'>
                                <i class="fa-solid fa-code me-1"></i>檢視 JSON 內容
                            </button>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: View JSON Diff -->
<div class="modal fade" id="diffModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h6 class="modal-title fw-bold">日誌異動內容詳情</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">異動前數值 (Old Values)</label>
                    <pre class="bg-light p-3 rounded small border" id="diffOld" style="max-height: 150px; overflow-y: auto;">無</pre>
                </div>
                <div>
                    <label class="form-label fw-semibold small text-muted">異動後數值 (New Values)</label>
                    <pre class="bg-light p-3 rounded small border" id="diffNew" style="max-height: 250px; overflow-y: auto;">無</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewDiff(oldVal, newVal) {
    document.getElementById('diffOld').innerText = oldVal ? (typeof oldVal === 'string' ? oldVal : JSON.stringify(oldVal, null, 2)) : '無異動前資料';
    document.getElementById('diffNew').innerText = newVal ? (typeof newVal === 'string' ? newVal : JSON.stringify(newVal, null, 2)) : '無內容';
    const modal = new bootstrap.Modal(document.getElementById('diffModal'));
    modal.show();
}
</script>
