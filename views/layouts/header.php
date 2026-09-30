<?php
use App\Auth;
use App\Helpers\Security;

$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="zh-Hant-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::getCsrfToken() ?>">
    <title><?= Security::e($pageTitle ?? 'ESG-Pro') ?> | 企業級 ESG 智慧管理與碳盤查系統</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/esg/assets/css/custom.css" rel="stylesheet">
    <link href="/esg/assets/css/ai-assistant.css" rel="stylesheet">
    <?php foreach (($extraStyles ?? []) as $stylesheet): ?>
        <link href="<?= Security::e($stylesheet) ?>" rel="stylesheet">
    <?php endforeach; ?>
</head>
<body class="bg-light">

<div class="d-flex" id="wrapper">
    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" class="sidebar-backdrop"></div>

    <!-- Sidebar -->
    <?php require __DIR__ . '/sidebar.php'; ?>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom shadow-sm px-3 px-md-4 py-2">
            <div class="container-fluid p-0">
                <button class="btn btn-outline-success btn-sm me-2 me-md-3" id="sidebarToggle" aria-label="切換選單">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h5 class="m-0 text-success fw-bold d-none d-md-block">
                    <i class="fa-solid fa-leaf text-success me-2"></i><?= Security::e($pageTitle ?? 'ESG 永續戰情室') ?>
                </h5>
                <span class="fw-bold text-success d-md-none text-truncate" style="max-width: 160px; font-size: 0.95rem;">
                    <i class="fa-solid fa-leaf text-success me-1"></i><?= Security::e($pageTitle ?? 'ESG-Pro') ?>
                </span>

                <div class="ms-auto d-flex align-items-center">
                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center text-dark p-0" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-1 me-sm-2 shadow-sm" style="width: 34px; height: 34px; font-weight: 600; font-size: 0.9rem;">
                                <?= mb_substr($currentUser['real_name'] ?? 'U', 0, 1) ?>
                            </div>
                            <div class="d-none d-sm-block text-start">
                                <div class="fw-bold small lh-sm"><?= Security::e($currentUser['real_name'] ?? '') ?></div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.68rem;">
                                    <?= Security::e($currentUser['role_name'] ?? '') ?>
                                </span>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li class="dropdown-header text-muted small">
                                <div><strong><?= Security::e($currentUser['real_name'] ?? '') ?></strong> (<?= Security::e($currentUser['username'] ?? '') ?>)</div>
                                <div>所屬：<?= Security::e($currentUser['org_name'] ?? '') ?></div>
                                <div>身分：<span class="text-primary"><?= Security::e($currentUser['role_name'] ?? '') ?></span></div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="/esg/logout" method="POST" class="m-0">
                                    <?= Security::csrfField() ?>
                                    <button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>安全登出</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content Container -->
        <div class="container-fluid px-2 px-sm-3 px-md-4 py-3 py-md-4">
            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i><?= Security::e($_SESSION['flash_success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i><?= Security::e($_SESSION['flash_warning']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_warning']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fa-solid fa-circle-xmark me-2"></i><?= Security::e($_SESSION['flash_error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>
