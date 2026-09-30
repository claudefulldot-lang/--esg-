<?php
use App\Auth;
$activeNav = $activeNav ?? '';
$user = Auth::user();
?>
<div class="bg-dark text-white border-end shadow" id="sidebar-wrapper">
    <div class="sidebar-heading px-3 px-sm-4 py-3 border-bottom border-secondary d-flex align-items-center bg-success-dark">
        <i class="fa-solid fa-earth-americas text-success fs-3 me-2"></i>
        <div>
            <div class="fw-bold fs-6 text-white tracking-wide">ESG-Pro 智慧系統</div>
            <small class="text-white-50" style="font-size: 0.7rem;">ISO 14064 / GRI / TCFD</small>
        </div>
        <button type="button" class="btn btn-sm text-white-50 ms-auto d-lg-none p-1" id="sidebarClose" aria-label="關閉選單">
            <i class="fa-solid fa-xmark fs-5"></i>
        </button>
    </div>

    <div class="list-group list-group-flush my-2">
        <!-- 戰情室儀表板 -->
        <a href="/esg/" class="list-group-item list-group-item-action bg-transparent text-white <?= $activeNav === 'dashboard' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-chart-pie me-2 text-info"></i>ESG 戰情室首頁
        </a>

        <!-- 模組二：環境保護 (E) -->
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-seedling me-1 text-success"></i>環境保護 (E)
        </div>
        <a href="/esg/ghg" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'ghg_list' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-smog me-2 text-warning"></i>溫室氣體碳盤查
        </a>
        <?php if (Auth::can('ghg', 'create')): ?>
        <a href="/esg/ghg/create" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'ghg_create' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-plus-circle me-2 text-success"></i>活動數據填報
        </a>
        <?php endif; ?>
        <a href="/esg/factors" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'factors' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-database me-2 text-primary"></i>碳排放係數庫
        </a>
        <a href="/esg/environment/water" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'env_water' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-faucet-drip me-2 text-info"></i>水資源與放流水質
        </a>
        <a href="/esg/environment/waste" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'env_waste' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-recycle me-2 text-success"></i>事業廢棄物處置
        </a>

        <!-- 模組三：社會責任 (S) -->
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-users me-1 text-info"></i>社會責任 (S)
        </div>
        <a href="/esg/social" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'social' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-handshake-angle me-2 text-info"></i>社會績效指標 (GRI)
        </a>

        <!-- 模組四：公司治理 (G) -->
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-building-shield me-1 text-warning"></i>公司治理 (G)
        </div>
        <a href="/esg/governance" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'governance' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-scale-balanced me-2 text-warning"></i>治理指標與氣候風險
        </a>

        <!-- 模組五：審批工作流 -->
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-diagram-project me-1 text-primary"></i>審批與工作流
        </div>
        <a href="/esg/workflow" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'workflow' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-list-check me-2 text-primary"></i>待辦與審批中心
        </a>

        <!-- 模組六：智慧報表中心 -->
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-file-invoice me-1 text-danger"></i>智慧報表中心
        </div>
        <a href="/esg/reports" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'reports' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-file-excel me-2 text-success"></i>ISO 清冊 / GRI 索引
        </a>

        <!-- 模組一：系統維護 (Super Admin) -->
        <?php if ($user['role_key'] === 'super_admin' || Auth::can('all')): ?>
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-gear me-1 text-secondary"></i>系統維護與管理
        </div>
        <a href="/esg/admin/users" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'admin_users' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-user-gear me-2"></i>帳號與角色授權
        </a>
        <a href="/esg/admin/orgs" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'admin_orgs' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-sitemap me-2"></i>組織與廠區架構
        </a>
        <a href="/esg/admin/logs" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'admin_logs' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-shield-halved me-2"></i>安全審計日誌
        </a>
        <?php if ($user['role_key'] === 'super_admin'): ?>
        <a href="/esg/admin/settings" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'admin_settings' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-sliders me-2 text-warning"></i>全域延展性參數 (Super Admin 專用)
        </a>
        <a href="/esg/admin/ai-assistant" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'admin_ai_assistant' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-robot me-2 text-info"></i>AI 客服小編設定
        </a>
        <?php endif; ?>
        <?php endif; ?>

        <!-- 系統說明：所有已登入角色皆可使用，固定置於選單最後 -->
        <div class="sidebar-section-header px-4 pt-3 pb-1 text-uppercase text-white-50 small fw-bold">
            <i class="fa-solid fa-circle-question me-1 text-info"></i>說明與支援
        </div>
        <a href="/esg/manual" class="list-group-item list-group-item-action bg-transparent text-white-50 <?= $activeNav === 'manual' ? 'active-nav' : '' ?>">
            <i class="fa-solid fa-book-open me-2 text-info"></i>系統操作手冊
        </a>
    </div>
</div>
