<?php
use App\Helpers\Security;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">組織機構與廠區層級架構 (ADM-01)</h4>
        <p class="text-muted small mb-0">構建集團、子公司、製造廠區與業務部門樹狀組織體系</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#orgModal" onclick="openOrgModal()">
            <i class="fa-solid fa-plus-circle me-1"></i>建立組織節點
        </button>
    </div>
</div>

<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle data-table">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>組織代碼</th>
                    <th>組織名稱</th>
                    <th>層級類型</th>
                    <th>上層父機構</th>
                    <th>負責人姓名</th>
                    <th>聯絡電話</th>
                    <th>聯絡信箱</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orgs as $o): ?>
                <tr>
                    <td class="text-muted small">#<?= $o['id'] ?></td>
                    <td class="fw-bold font-monospace small"><?= Security::e($o['org_code']) ?></td>
                    <td class="fw-semibold">
                        <?php if ($o['org_type'] === 'group'): ?>
                            <i class="fa-solid fa-building text-primary me-1"></i>
                        <?php elseif ($o['org_type'] === 'plant'): ?>
                            <i class="fa-solid fa-industry text-warning me-1"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-users text-secondary me-1"></i>
                        <?php endif; ?>
                        <?= Security::e($o['org_name']) ?>
                    </td>
                    <td>
                        <?php
                            $typeMap = [
                                'group'   => ['label' => '企業集團', 'class' => 'bg-danger'],
                                'company' => ['label' => '子公司', 'class' => 'bg-primary'],
                                'plant'   => ['label' => '製造廠區', 'class' => 'bg-warning text-dark'],
                                'dept'    => ['label' => '功能部門', 'class' => 'bg-secondary']
                            ];
                            $t = $typeMap[$o['org_type']] ?? ['label' => $o['org_type'], 'class' => 'bg-light text-dark'];
                        ?>
                        <span class="badge <?= $t['class'] ?>"><?= $t['label'] ?></span>
                    </td>
                    <td class="small text-muted"><?= Security::e($o['parent_name'] ?? '無 (根節點)') ?></td>
                    <td class="small"><?= Security::e($o['leader_name'] ?? '-') ?></td>
                    <td class="small"><?= Security::e($o['contact_phone'] ?? '-') ?></td>
                    <td class="small text-muted"><?= Security::e($o['contact_email'] ?? '-') ?></td>
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-sm py-0 px-2" onclick='editOrg(<?= Security::jsonForHtml($o) ?>)'>
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add/Edit Org -->
<div class="modal fade" id="orgModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/esg/admin/orgs/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="org_id" id="modalOrgId" value="">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="orgModalTitle">建立組織機構</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">組織代碼 (唯一識別碼) <span class="text-danger">*</span></label>
                            <input type="text" name="org_code" id="modalOrgCode" class="form-control font-monospace" placeholder="例: PLANT_TY" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">組織機構名稱 <span class="text-danger">*</span></label>
                            <input type="text" name="org_name" id="modalOrgName" class="form-control" placeholder="例: 桃園三廠" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">層級類型 <span class="text-danger">*</span></label>
                            <select name="org_type" id="modalOrgType" class="form-select" required>
                                <option value="group">企業集團 (Group)</option>
                                <option value="company">所屬公司 (Company)</option>
                                <option value="plant">製造廠區 (Plant)</option>
                                <option value="dept">營運部門 (Department)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">上層母機構</label>
                            <select name="parent_id" id="modalParentId" class="form-select">
                                <option value="">無 (作為最高層級)</option>
                                <?php foreach ($orgs as $o): ?>
                                    <option value="<?= $o['id'] ?>"><?= Security::e($o['org_name']) ?> (<?= $o['org_type'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">負責人姓名</label>
                            <input type="text" name="leader_name" id="modalLeader" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">聯絡電話</label>
                            <input type="text" name="contact_phone" id="modalPhone" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">聯絡信箱</label>
                            <input type="email" name="contact_email" id="modalEmail" class="form-control">
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
function openOrgModal() {
    document.getElementById('orgModalTitle').innerText = '建立組織機構節點';
    document.getElementById('modalOrgId').value = '';
    document.getElementById('modalOrgCode').value = '';
    document.getElementById('modalOrgName').value = '';
    document.getElementById('modalOrgType').value = 'plant';
    document.getElementById('modalParentId').value = '2';
    document.getElementById('modalLeader').value = '';
    document.getElementById('modalPhone').value = '';
    document.getElementById('modalEmail').value = '';
}

function editOrg(o) {
    document.getElementById('orgModalTitle').innerText = '編輯組織機構 (#' + o.id + ')';
    document.getElementById('modalOrgId').value = o.id;
    document.getElementById('modalOrgCode').value = o.org_code;
    document.getElementById('modalOrgName').value = o.org_name;
    document.getElementById('modalOrgType').value = o.org_type;
    document.getElementById('modalParentId').value = o.parent_id || '';
    document.getElementById('modalLeader').value = o.leader_name || '';
    document.getElementById('modalPhone').value = o.contact_phone || '';
    document.getElementById('modalEmail').value = o.contact_email || '';

    const modal = new bootstrap.Modal(document.getElementById('orgModal'));
    modal.show();
}
</script>
