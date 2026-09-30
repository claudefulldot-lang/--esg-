<?php
use App\Helpers\Security;
?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1 text-dark">使用者帳號與權限角色管理 (RBAC)</h4>
        <p class="text-muted small mb-0">維護全集團使用者、指派所屬廠區部門與角色權限</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openUserModal()">
            <i class="fa-solid fa-user-plus me-1"></i>建立新使用者
        </button>
    </div>
</div>

<div class="card p-3 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle data-table">
            <thead class="table-light">
                <tr>
                    <th>序號</th>
                    <th>登入帳號</th>
                    <th>真實姓名</th>
                    <th>電子信箱</th>
                    <th>所屬組織 / 廠區</th>
                    <th>指派系統角色</th>
                    <th>帳號狀態</th>
                    <th>最後登入</th>
                    <th class="text-center">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="text-muted small">#<?= $u['id'] ?></td>
                    <td class="fw-bold small"><?= Security::e($u['username']) ?></td>
                    <td class="fw-semibold"><?= Security::e($u['real_name']) ?></td>
                    <td class="small"><?= Security::e($u['email']) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= Security::e($u['org_name']) ?></span></td>
                    <td>
                        <?php
                            $roleColors = [
                                'super_admin' => 'bg-danger',
                                'cso'         => 'bg-success',
                                'approver'    => 'bg-primary',
                                'submitter'   => 'bg-warning text-dark',
                                'auditor'     => 'bg-info text-dark'
                            ];
                            $c = $roleColors[$u['role_key']] ?? 'bg-secondary';
                        ?>
                        <span class="badge <?= $c ?>"><?= Security::e($u['role_name']) ?></span>
                    </td>
                    <td>
                        <?= $u['status'] ? '<span class="badge bg-success">正常啟用</span>' : '<span class="badge bg-danger">停用</span>' ?>
                    </td>
                    <td class="small text-muted"><?= $u['last_login_at'] ? substr($u['last_login_at'], 0, 16) : '從未登入' ?></td>
                    <td class="text-center">
                        <button class="btn btn-outline-primary btn-sm py-0 px-2" onclick='editUser(<?= Security::jsonForHtml($u) ?>)'>
                            <i class="fa-solid fa-user-pen me-1"></i>編輯
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add/Edit User -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="/esg/admin/users/store" method="POST">
                <?= Security::csrfField() ?>
                <input type="hidden" name="user_id" id="modalUserId" value="">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="userModalTitle">建立新使用者帳號</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">登入帳號 (Username) <span class="text-danger">*</span></label>
                            <input type="text" name="username" id="modalUsername" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">真實中文姓名 <span class="text-danger">*</span></label>
                            <input type="text" name="real_name" id="modalRealName" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">登入密碼 <small class="text-muted">(若不修改密碼請留空)</small></label>
                            <input type="password" name="password" id="modalPassword" class="form-control" placeholder="••••••••">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">電子郵件 (用於工作流通知信) <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="modalEmail" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">所屬組織 / 廠區 / 部門 <span class="text-danger">*</span></label>
                            <select name="org_id" id="modalOrgId" class="form-select" required>
                                <?php foreach ($orgs as $o): ?>
                                    <option value="<?= $o['id'] ?>"><?= Security::e($o['org_name']) ?> (<?= $o['org_type'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">系統授權角色 (Role) <span class="text-danger">*</span></label>
                            <select name="role_id" id="modalRoleId" class="form-select" required>
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>"><?= Security::e($r['role_name']) ?> (<?= $r['role_key'] ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">分機或聯絡電話</label>
                            <input type="text" name="phone" id="modalPhone" class="form-control">
                        </div>
                        <div class="col-md-6 d-flex align-items-center mt-4">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="status" id="modalStatus" value="1" checked>
                                <label class="form-check-label fs-6 fw-semibold">帳號正常啟用</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                    <button type="submit" class="btn btn-primary fw-semibold">確認儲存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUserModal() {
    document.getElementById('userModalTitle').innerText = '建立新使用者帳號';
    document.getElementById('modalUserId').value = '';
    document.getElementById('modalUsername').value = '';
    document.getElementById('modalUsername').removeAttribute('readonly');
    document.getElementById('modalRealName').value = '';
    document.getElementById('modalEmail').value = '';
    document.getElementById('modalPhone').value = '';
    document.getElementById('modalPassword').required = true;
    document.getElementById('modalStatus').checked = true;
}

function editUser(u) {
    document.getElementById('userModalTitle').innerText = '編輯使用者 (#' + u.id + ' ' + u.username + ')';
    document.getElementById('modalUserId').value = u.id;
    document.getElementById('modalUsername').value = u.username;
    document.getElementById('modalUsername').setAttribute('readonly', 'readonly');
    document.getElementById('modalRealName').value = u.real_name;
    document.getElementById('modalEmail').value = u.email;
    document.getElementById('modalPhone').value = u.phone || '';
    document.getElementById('modalOrgId').value = u.org_id;
    document.getElementById('modalRoleId').value = u.role_id;
    document.getElementById('modalPassword').required = false;
    document.getElementById('modalStatus').checked = parseInt(u.status) === 1;

    const modal = new bootstrap.Modal(document.getElementById('userModal'));
    modal.show();
}
</script>
