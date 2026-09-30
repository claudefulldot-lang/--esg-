<?php
use App\Helpers\Security;

// Group definitions with icons and descriptions
$groupMeta = [
    'environmental' => [
        'title' => '環境保護與碳盤查基準 (GHG Standards)',
        'icon'  => 'fa-solid fa-leaf text-success',
        'desc'  => '設定基準年、SBTi 減碳目標、GWP 版本、碳費單價與組織邊界方法，直接連動碳排試算與戰情室指標'
    ],
    'finance' => [
        'title' => '財務營收與人均強度基準 (Finance Benchmarks)',
        'icon'  => 'fa-solid fa-chart-line text-primary',
        'desc'  => '維護集團合併營業額與總員工人數，作為計算每單位營收碳排放強度與人均碳排放量之分母'
    ],
    'workflow' => [
        'title' => '工作流程與審查期限 (Workflow & SLA Policies)',
        'icon'  => 'fa-solid fa-diagram-project text-warning',
        'desc'  => '設定每月填報截止日、主管審查 SLA 警示天數、多階簽核模式與佐證憑證強制上傳開關'
    ],
    'security' => [
        'title' => '系統資訊安全與防護政策 (Security Policies)',
        'icon'  => 'fa-solid fa-shield-halved text-danger',
        'desc'  => '密碼錯誤鎖定門檻、帳號鎖定時間、Session 逾時時間與檔案上傳白名單等資安防禦'
    ],
    'notification' => [
        'title' => '推播通知與 SMTP 郵件引擎 (Notification & SMTP)',
        'icon'  => 'fa-solid fa-envelope text-info',
        'desc'  => '設定企業內部 Exchange 或外部 SMTP 伺服器，處理每月填報催辦與審查退件通知信'
    ],
    'general' => [
        'title' => '企業主體與系統基底 (General Identity)',
        'icon'  => 'fa-solid fa-building text-secondary',
        'desc'  => '企業抬頭全名、系統全域名稱與所屬行業類別，用於正式永續報告書與 GRI 清冊抬頭'
    ]
];

// Expert guidance and recommendations for each key
$itemGuidance = [
    'base_year' => [
        'explain' => '溫室氣體盤查的歷史比較起點年。所有年度減碳成果與 SBTi 目標均以此年之總排放量作為對比基準。',
        'recommend' => '建議設定為 2022 年（金管會上市櫃路徑圖基準）或企業首度取得 ISO 14064-1 外部查證之年度。'
    ],
    'target_year' => [
        'explain' => '企業承諾達成階段性減碳目標的終點年份（如國家自定貢獻 NDC 與科學基礎減碳目標 SBTi 承諾年）。',
        'recommend' => '建議設定為 2030 年（中期目標）或 2050 年（淨零排放 Net-Zero 目標）。'
    ],
    'target_reduction_pct' => [
        'explain' => '相較於基準年必須降低的溫室氣體排放百分比，直接決定戰情室減碳目標線與差距警報。',
        'recommend' => '建議設定 25.0% ~ 42.0%（符合 SBTi 1.5°C 升溫路徑之建議減量幅度）。'
    ],
    'default_gwp_version' => [
        'explain' => '聯合國政府間氣候變遷專門委員會 (IPCC) 全球暖化潛勢評估版本。甲烷與冷媒等氣體當量在不同報告版本有微幅差異。',
        'recommend' => '建議設定為 IPCC_AR5（目前台灣環境部與多數查證機構主流標準）或 IPCC_AR6。'
    ],
    'carbon_tax_rate_per_ton' => [
        'explain' => '內部碳定價 (ICP) 或台灣環境部預計開徵之每公噸碳費單價 (NTD/tCO2e)，用於評估潛在環境財務風險。',
        'recommend' => '建議設定 300 ~ 500 元/噸（符合環境部碳費費率審議委員會預擬區間）。'
    ],
    'inventory_boundary' => [
        'explain' => '定義企業界定溫室氣體排放責任之邊界原則。',
        'recommend' => '建議選擇「Operational_Control (營運控制權法)」，為 ISO 14064-1 與 GHG Protocol 最普遍採用之原則。'
    ],
    'annual_revenue_millions' => [
        'explain' => '全集團年度合併營業額（百萬新台幣），用於計算 GRI 305-4 碳排放密集度指標 (tCO₂e / 百萬元)。',
        'recommend' => '建議於每年度審計財報公告後，由管理員即時同步更新為最新查核數值。'
    ],
    'total_employee_count' => [
        'explain' => '全集團受僱員工人數（包含常態約聘人員），用於計算人均碳排放強度 (tCO₂e / 人)。',
        'recommend' => '建議依據人資部門最新第四季底或年平均員工人數定期校正。'
    ],
    'revenue_currency' => [
        'explain' => '財務報告營收統計之計價基準幣別。',
        'recommend' => '台灣本土營運建議維持 TWD，若對外跨國揭露可選擇 USD。'
    ],
    'monthly_submission_deadline_day' => [
        'explain' => '各廠區各部門每月填報前月水電與燃油用量之截止日（日曆日）。',
        'recommend' => '建議設定為每月 10 號或 15 號（保留足夠時間取得台電電單與中油發票）。'
    ],
    'approval_sla_alert_days' => [
        'explain' => '審核主管接獲待審通知後，若超過指定天數未完成簽核，系統標示逾期提醒。',
        'recommend' => '建議設定 3 ~ 5 天，落實企業內部控制與簽核效率。'
    ],
    'enforce_attachment_upload' => [
        'explain' => '是否強制要求填報人員在送審時必須上傳佐證單據憑證（發票、收據、檢驗單等）。',
        'recommend' => '建議設定為「1 (強制)」，以滿足 ISO 14064-1 外部稽核查證必須具備完整單據軌跡之要求。'
    ],
    'workflow_approval_levels' => [
        'explain' => '活動數據送審後的簽核層級數。2級代表部門初審後直接進委員會；3級代表增加廠長層級。',
        'recommend' => '中大型多廠區企業建議選擇「2 (部門初審+委員會複核)」或「3 (增設廠長)」'
    ],
    'max_failed_login_attempts' => [
        'explain' => '同一帳號連續密碼輸入錯誤達到此門檻時，系統將自動啟動安全防禦鎖定。',
        'recommend' => '建議設定 5 次（兼顧使用者防呆與防字典檔暴力猜解攻擊）。'
    ],
    'account_lockout_duration_mins' => [
        'explain' => '帳號觸發鎖定後的持續禁止登入時間（分鐘）。',
        'recommend' => '建議設定 15 分鐘，達到有效防禦阻斷且不至於過度影響業務日常操作。'
    ],
    'session_timeout_mins' => [
        'explain' => '使用者登入後若完全無任何點擊操作，Session 連線閒置逾時自動登出時間。',
        'recommend' => '建議設定 30 ~ 60 分鐘，符合 ISO 27001 資訊安全控制規範。'
    ],
    'max_upload_size_mb' => [
        'explain' => '允許單一上傳佐證憑證（PDF 或相片）之最大檔案容量限制。',
        'recommend' => '建議設定 20 MB（足以涵蓋多頁高解析度委外檢測報告與電單掃描檔）。'
    ],
    'allowed_upload_extensions' => [
        'explain' => '上傳檔案之安全白名單副檔名，嚴禁上傳可執行檔案 (.php, .exe, .sh 等)。',
        'recommend' => '建議維持「pdf, jpg, jpeg, png, xlsx, docx」。'
    ],
    'enable_email_notifications' => [
        'explain' => '系統是否在任務派發、初審退件、委員會核准時自動發送 HTML 電子郵件通知。',
        'recommend' => '正式營運環境強烈建議開啟「1 (啟用)」，以自動化推動填報進度。'
    ],
    'smtp_host' => [
        'explain' => '企業郵件伺服器之連線主機位址 (SMTP Host)。',
        'recommend' => '可填寫公司內部 Exchange/Postfix 主機或第三方 SMTP 服務。'
    ],
    'smtp_port' => [
        'explain' => 'SMTP 伺服器連線傳輸埠 (Port)。',
        'recommend' => 'STARTTLS 加密建議埠號 587 或 2525；SSL/TLS 建議 465；未加密建議 25。'
    ],
    'system_name' => [
        'explain' => '顯示於系統全站頂部導航列、登入畫面與瀏覽器頁籤之系統名稱。',
        'recommend' => '可依據企業內部專案專有代號自訂。'
    ],
    'company_name' => [
        'explain' => '企業法人全名，自動映射於 ISO 14064 盤查清冊、GRI 索引表與永續總結報告之表頭。',
        'recommend' => '請填寫公司登記之官方繁體中文全稱。'
    ],
    'industry_sector' => [
        'explain' => '企業所屬行業類別，用於比對主管機關碳排盤查指定範疇與同業基準。',
        'recommend' => '請選擇符合企業主要營業項目之行業別。'
    ]
];
?>

<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <div class="d-flex align-items-center">
            <h4 class="fw-bold mb-1 text-dark me-2">全域可延展性與可調整參數控制台</h4>
            <span class="badge bg-danger shadow-sm"><i class="fa-solid fa-lock me-1"></i>僅限 Super Admin 調整</span>
        </div>
        <p class="text-muted small mb-0">因應國際永續法規 (IFRS/GRI/TCFD/CBAM) 變動、企業組織併購擴張與資安原則之全域延展性中心</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
            <a href="/esg/" class="btn btn-outline-secondary btn-sm shadow-sm text-nowrap">
                <i class="fa-solid fa-arrow-left me-1"></i>返回戰情室
            </a>
            <button type="submit" form="settingsForm" class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold text-nowrap">
                <i class="fa-solid fa-floppy-disk me-1"></i>儲存所有變更
            </button>
        </div>
    </div>
</div>

<!-- Super Admin Notice Alert -->
<div class="alert alert-dark border-start border-4 border-warning shadow-sm mb-4">
    <div class="d-flex align-items-start">
        <i class="fa-solid fa-triangle-exclamation text-warning fs-4 me-3 mt-1"></i>
        <div>
            <h6 class="fw-bold mb-1 text-warning">最高管理員延展性維護注意事項</h6>
            <div class="small opacity-75">
                此控制台所有參數均直接連動全集團之<strong>溫室氣體計算引擎、SBTi 減碳目標線、財務碳排強度分母、工作流狀態機與資安防護策略</strong>。
                變更後將即時寫入系統設定並同步留存於<strong>安全操作審計日誌 (Audit Log)</strong>，請審慎配置。
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<form action="/esg/admin/settings/store" method="POST" id="settingsForm">
    <?= Security::csrfField() ?>

    <!-- Category Tabs with Horizontal Scroll for Mobile -->
    <ul class="nav nav-pills bg-white p-2 rounded-3 shadow-sm mb-4 flex-nowrap overflow-x-auto text-nowrap" id="settingsTab" role="tablist">
        <?php $first = true; foreach ($groupMeta as $gKey => $gInfo): ?>
            <li class="nav-item">
                <button class="nav-link <?= $first ? 'active' : '' ?> fw-semibold" data-bs-toggle="pill" data-bs-target="#tab-<?= $gKey ?>">
                    <i class="<?= $gInfo['icon'] ?> me-2"></i><?= $gInfo['title'] ?>
                </button>
            </li>
        <?php $first = false; endforeach; ?>
    </ul>

    <!-- Tab Panes -->
    <div class="tab-content" id="settingsTabContent">
        <?php $first = true; foreach ($groupMeta as $gKey => $gInfo): ?>
            <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="tab-<?= $gKey ?>">
                <div class="card p-4 shadow-sm mb-4">
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="fw-bold mb-1 text-dark">
                            <i class="<?= $gInfo['icon'] ?> me-2"></i><?= $gInfo['title'] ?>
                        </h5>
                        <p class="text-muted small mb-0"><?= $gInfo['desc'] ?></p>
                    </div>

                    <?php if (empty($groupedSettings[$gKey])): ?>
                        <div class="text-muted text-center py-4">此群組目前無配置參數</div>
                    <?php else: ?>
                        <div class="row g-4">
                            <?php foreach ($groupedSettings[$gKey] as $item): 
                                $key = $item['setting_key'];
                                $val = $item['setting_value'];
                                $type = $item['input_type'] ?? 'text';
                                $options = !empty($item['options_json']) ? json_decode($item['options_json'], true) : [];
                                $guide = $itemGuidance[$key] ?? ['explain' => $item['description'], 'recommend' => '無特殊建議'];
                            ?>
                                <div class="col-lg-6">
                                    <div class="border rounded-3 p-3 bg-light h-100 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <label class="form-label fw-bold text-dark mb-0">
                                                <?= Security::e($item['description']) ?>
                                            </label>
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.72rem;">
                                                <?= Security::e($key) ?>
                                            </span>
                                        </div>

                                        <!-- Parameter Guidance & Explanation -->
                                        <div class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.4;">
                                            <div><i class="fa-solid fa-circle-info text-info me-1"></i><strong>說明：</strong><?= Security::e($guide['explain']) ?></div>
                                            <div class="text-success mt-1"><i class="fa-solid fa-lightbulb text-warning me-1"></i><strong>建議配置：</strong><?= Security::e($guide['recommend']) ?></div>
                                        </div>

                                        <!-- Input Control -->
                                        <div class="mt-auto">
                                            <?php if ($type === 'select'): ?>
                                                <select name="settings[<?= Security::e($key) ?>]" class="form-select bg-white">
                                                    <?php foreach ($options as $opt): ?>
                                                        <option value="<?= Security::e($opt) ?>" <?= $val === $opt ? 'selected' : '' ?>>
                                                            <?= Security::e($opt) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            <?php elseif ($type === 'switch'): ?>
                                                <div class="form-check form-switch fs-5 pt-1">
                                                    <input type="hidden" name="settings[<?= Security::e($key) ?>]" value="0">
                                                    <input class="form-check-input" type="checkbox" name="settings[<?= Security::e($key) ?>]" value="1" <?= $val == '1' ? 'checked' : '' ?>>
                                                    <label class="form-check-label fs-6 fw-semibold ms-2 text-dark">
                                                        <?= $val == '1' ? '已啟用 (ON)' : '已停用 (OFF)' ?>
                                                    </label>
                                                </div>

                                            <?php elseif ($type === 'number'): ?>
                                                <input type="number" step="any" name="settings[<?= Security::e($key) ?>]" class="form-control bg-white fw-bold fs-6" value="<?= Security::e($val) ?>" required>

                                            <?php else: ?>
                                                <input type="<?= $key === 'smtp_pass' ? 'password' : 'text' ?>" name="settings[<?= Security::e($key) ?>]" class="form-control bg-white" value="<?= Security::e($val) ?>" required autocomplete="<?= $key === 'smtp_pass' ? 'new-password' : 'off' ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php $first = false; endforeach; ?>
    </div>

    <!-- Bottom Action Bar -->
    <div class="card p-3 shadow-sm bg-white border-top border-4 border-primary">
        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                <i class="fa-solid fa-shield-halved text-success me-1"></i>系統延展性變更將記錄操作者 IP、時間戳記與參數新舊對比至 <code>sys_audit_logs</code>。
            </div>
            <div>
                <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-1"></i>確認儲存所有延展性參數配置
                </button>
            </div>
        </div>
    </div>
</form>
