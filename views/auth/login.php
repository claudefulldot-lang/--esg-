<?php
use App\Helpers\Security;
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入 | ESG-Pro 智慧永續管理與碳盤查系統</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans TC', sans-serif;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 1.25rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
        }
        .login-header {
            background: linear-gradient(135deg, #134e5e, #71b280);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
        }
        @media (min-width: 576px) {
            .login-header {
                padding: 2.5rem 2rem 2rem 2rem;
            }
        }
        .quick-role-btn {
            font-size: 0.78rem;
            padding: 0.35rem 0.65rem;
            margin: 0.2rem;
            border-radius: 0.5rem;
            transition: all 0.2s;
        }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="login-card mx-auto bg-white">
        <div class="login-header">
            <i class="fa-solid fa-leaf fs-1 mb-2"></i>
            <h3 class="fw-bold mb-1">ESG-Pro 智慧系統</h3>
            <p class="mb-0 opacity-75 small">企業永續數據管理與溫室氣體碳盤查雲端平台</p>
        </div>

        <div class="p-4 p-md-5">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 small d-flex align-items-center mb-4">
                    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                    <div><?= Security::e($error) ?></div>
                </div>
            <?php endif; ?>

            <form action="/esg/login" method="POST">
                <?= Security::csrfField() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">使用者帳號 (Username)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="username" id="username" class="form-control form-control-lg fs-6" placeholder="請輸入登入帳號" value="<?= Security::e($username ?? '') ?>" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted">登入密碼 (Password)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control form-control-lg fs-6" placeholder="請輸入密碼" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 py-2 fw-semibold fs-6 shadow-sm">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>安全登入系統
                </button>
            </form>

            <hr class="my-4">

            <!-- 示範測試快速填入按鈕 -->
            <div class="text-center">
                <div class="text-muted small mb-2 fw-bold">測試展示快速填入 (預設密碼 admin123)：</div>
                <div class="d-flex flex-wrap justify-content-center">
                    <button class="btn btn-outline-dark quick-role-btn" onclick="fillCreds('admin')">Super Admin</button>
                    <button class="btn btn-outline-success quick-role-btn" onclick="fillCreds('cso_chen')">CSO 永續長</button>
                    <button class="btn btn-outline-primary quick-role-btn" onclick="fillCreds('dept_approver')">審核主管</button>
                    <button class="btn btn-outline-warning text-dark quick-role-btn" onclick="fillCreds('submitter_hc')">新竹填報員</button>
                    <button class="btn btn-outline-info text-dark quick-role-btn" onclick="fillCreds('auditor_wang')">稽核員</button>
                </div>
            </div>
        </div>

        <div class="bg-light px-4 py-3 text-center border-top">
            <small class="text-muted">支援 ISO 14064-1:2018 / GHG Protocol / GRI Standards</small>
        </div>
    </div>
</div>

<script>
function fillCreds(u) {
    document.getElementById('username').value = u;
    document.getElementById('password').value = 'admin123';
}
</script>
</body>
</html>
