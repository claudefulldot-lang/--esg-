<?php
use App\Auth;
use App\Helpers\Security;

$user = Auth::user();
$roleName = $user['role_name'] ?? '系統使用者';
?>

<section class="manual-hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <div class="manual-kicker fw-bold small text-uppercase mb-2">ESG-PRO OPERATION GUIDE</div>
            <h1 class="h2 fw-bold mb-2">系統操作手冊與問題排解中心</h1>
            <p class="mb-4 opacity-75">第一次操作也能快速上手。輸入功能名稱、畫面文字、錯誤訊息或問題，例如「怎麼送審」、「附件上傳失敗」、「匯出 Excel」。</p>
            <div class="manual-search-wrap">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input id="manualSearch" class="form-control manual-search" type="search" placeholder="搜尋手冊與常見問題…" autocomplete="off" aria-label="搜尋操作手冊">
                <span id="manualSearchCount" class="manual-search-count" aria-live="polite"></span>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <div class="d-inline-block text-start bg-white bg-opacity-10 rounded-4 p-3">
                <div class="small opacity-75">目前登入身分</div>
                <div class="fw-bold"><i class="fa-solid fa-user-shield me-2"></i><?= Security::e($roleName) ?></div>
                <div class="small mt-2">手冊版本：1.1／適用系統 1.0.0</div>
            </div>
        </div>
    </div>
</section>

<div class="d-flex flex-wrap gap-2 mb-4">
    <span class="small text-muted align-self-center me-1">快速找答案：</span>
    <button class="btn btn-sm btn-outline-success" data-manual-query="登入 密碼">登入問題</button>
    <button class="btn btn-sm btn-outline-success" data-manual-query="GHG 填報 送審">GHG 填報</button>
    <button class="btn btn-sm btn-outline-success" data-manual-query="簽核 退回">簽核退回</button>
    <button class="btn btn-sm btn-outline-success" data-manual-query="附件 上傳">附件上傳</button>
    <button class="btn btn-sm btn-outline-success" data-manual-query="Excel 報表 匯出">報表匯出</button>
    <button class="btn btn-sm btn-outline-success" data-manual-query="403 權限">權限問題</button>
    <button type="button" class="btn btn-sm btn-outline-secondary manual-print ms-auto" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>列印手冊</button>
</div>

<div class="manual-layout">
    <aside class="manual-toc card border-0 shadow-sm p-2" aria-label="手冊章節">
        <div class="px-2 pt-2 pb-1 small fw-bold text-muted">章節導覽</div>
        <a href="#manual-start">1. 第一次使用</a>
        <a href="#manual-roles">2. 角色與權限</a>
        <a href="#manual-dashboard">3. 戰情室儀表板</a>
        <a href="#manual-ghg">4. GHG 碳盤查</a>
        <a href="#manual-environment">5. 水資源與廢棄物</a>
        <a href="#manual-social">6. 社會與治理</a>
        <a href="#manual-workflow">7. 審批工作流</a>
        <a href="#manual-reports">8. 報表與匯出</a>
        <a href="#manual-admin">9. 系統管理</a>
        <a href="#manual-security">10. 安全操作</a>
        <a href="#manual-ai">11. AI 客服小編</a>
        <a href="#manual-faq">12. 常見問題</a>
    </aside>

    <main>
        <div id="manualEmpty" class="manual-empty mb-4">
            <i class="fa-solid fa-magnifying-glass fs-2 text-muted mb-3"></i>
            <h5>找不到完全相符的主題</h5>
            <p class="text-muted mb-0">請改用較短的關鍵字，例如「送審」、「403」、「附件」或「Excel」。</p>
        </div>

        <section id="manual-start" class="manual-section" data-search="新手 第一次 登入 登出 導覽 快速開始 帳號 密碼 鎖定">
            <header class="manual-section-header">
                <span class="manual-badge"><i class="fa-solid fa-flag-checkered"></i>新手必讀</span>
                <h2 class="h4 fw-bold mt-2 mb-1">1. 第一次使用：五分鐘快速開始</h2>
                <p class="text-muted mb-0">先確認登入身分，再依「查詢、填報、送審、核准、匯出」的順序操作。</p>
            </header>
            <div class="manual-section-body">
                <div class="manual-step"><div class="manual-step-number">1</div><div><strong>登入系統</strong><br><span class="text-muted">由管理者提供帳號與初始密碼。登入後請確認右上角顯示的姓名、組織與角色正確。</span></div></div>
                <div class="manual-step"><div class="manual-step-number">2</div><div><strong>認識左側選單</strong><br><span class="text-muted">功能依 E（環境）、S（社會）、G（治理）、工作流、報表、系統管理分組；看不到的項目代表目前角色沒有權限。</span></div></div>
                <div class="manual-step"><div class="manual-step-number">3</div><div><strong>先用查詢再新增</strong><br><span class="text-muted">輸入資料前先在清冊用年度、月份、組織或狀態篩選，避免重複填報。</span></div></div>
                <div class="manual-step"><div class="manual-step-number">4</div><div><strong>保存或送審</strong><br><span class="text-muted">GHG 可暫存草稿或直接送審。正式數據須經初審與最終核准後，才會計入 Dashboard 與正式報表。</span></div></div>
                <div class="manual-step"><div class="manual-step-number">5</div><div><strong>安全登出</strong><br><span class="text-muted">完成操作後，從右上角使用者選單選擇「安全登出」。請勿直接共用瀏覽器登入狀態。</span></div></div>
                <div class="manual-note manual-warning"><strong>帳號鎖定：</strong>目前連續輸入錯誤密碼 5 次會鎖定 15 分鐘。請等待倒數結束或聯絡 Super Admin，切勿持續重試。</div>
            </div>
        </section>

        <section id="manual-roles" class="manual-section" data-search="角色 權限 super admin cso submitter approver auditor 403 禁止">
            <header class="manual-section-header">
                <span class="manual-badge"><i class="fa-solid fa-users-gear"></i>RBAC</span>
                <h2 class="h4 fw-bold mt-2 mb-1">2. 角色與權限怎麼分</h2>
            </header>
            <div class="manual-section-body">
                <div class="row g-3">
                    <div class="col-md-6 col-xl"><div class="manual-role"><h6 class="fw-bold">系統最高管理員</h6><p class="small mb-0">帳號、角色、組織、稽核日誌與全域參數；也能使用全部業務功能。</p></div></div>
                    <div class="col-md-6 col-xl"><div class="manual-role"><h6 class="fw-bold">永續長／CSO</h6><p class="small mb-0">監控 Dashboard、最終複核、查看與匯出正式報表。</p></div></div>
                    <div class="col-md-6 col-xl"><div class="manual-role"><h6 class="fw-bold">填報人員</h6><p class="small mb-0">新增活動數據、上傳憑證、暫存與送審，只能管理自己的 GHG 草稿。</p></div></div>
                    <div class="col-md-6 col-xl"><div class="manual-role"><h6 class="fw-bold">初審主管</h6><p class="small mb-0">核對憑證、初審通過或填寫原因退回補正。</p></div></div>
                    <div class="col-md-6 col-xl"><div class="manual-role"><h6 class="fw-bold">稽核／查證員</h6><p class="small mb-0">唯讀查核歷程、計算結果、附件與報表，不可修改業務資料。</p></div></div>
                </div>
                <div class="manual-note manual-info mt-3"><strong>看到 403 Forbidden：</strong>表示帳號已登入，但角色沒有該操作權限。這不是系統故障，請向 Super Admin 確認角色指派。</div>
            </div>
        </section>

        <section id="manual-dashboard" class="manual-section" data-search="dashboard 戰情室 儀表板 scope 範疇 排放 強度 減碳 圖表 年度">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-chart-pie"></i>總覽</span><h2 class="h4 fw-bold mt-2 mb-1">3. 戰情室儀表板</h2></header>
            <div class="manual-section-body">
                <figure class="manual-shot mb-4">
                    <button type="button" data-manual-image="/esg/assets/manual/dashboard.png" data-manual-alt="ESG 戰情室儀表板完整畫面"><img src="/esg/assets/manual/dashboard.png" loading="lazy" alt="ESG 戰情室儀表板"></button>
                    <figcaption>實際畫面：上方是三大範疇與總排放量，中段是強度、減碳目標與趨勢，下方是廠區貢獻和待辦任務。點圖可放大。</figcaption>
                </figure>
                <div class="row g-3">
                    <div class="col-md-6"><h6 class="fw-bold">年度切換</h6><p class="small text-muted">右上「盤查年度」會同步更新排放總量、月趨勢、廠區比較與減碳進度。</p></div>
                    <div class="col-md-6"><h6 class="fw-bold">數字來源</h6><p class="small text-muted">正式 GHG 統計只採計最終核准的 <code>approved_final</code> 記錄，草稿、待審與退回資料不列入。</p></div>
                    <div class="col-md-6"><h6 class="fw-bold">排放強度</h6><p class="small text-muted">營收強度＝總排放量／年度營收；人均強度＝總排放量／員工數。分母由全域設定維護。</p></div>
                    <div class="col-md-6"><h6 class="fw-bold">報表捷徑</h6><p class="small text-muted">「列印總結」開啟列印版；「匯出盤查清冊」下載當年度 Excel。</p></div>
                </div>
            </div>
        </section>

        <section id="manual-ghg" class="manual-section" data-search="GHG 溫室氣體 碳盤查 活動數據 排放係數 scope 清冊 新增 暫存 送審 附件">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-smog"></i>環境 E</span><h2 class="h4 fw-bold mt-2 mb-1">4. GHG 碳盤查與活動數據填報</h2></header>
            <div class="manual-section-body">
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/ghg-list.png" data-manual-alt="溫室氣體碳盤查清冊"><img src="/esg/assets/manual/ghg-list.png" loading="lazy" alt="溫室氣體碳盤查清冊"></button><figcaption>GHG 清冊：使用年度、月份、範疇、組織與狀態縮小範圍；右側可檢視查核歷程，草稿／退回資料可由原填報者刪除。</figcaption></figure>
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/ghg-create.png" data-manual-alt="新增 GHG 活動數據表單"><img src="/esg/assets/manual/ghg-create.png" loading="lazy" alt="新增 GHG 活動數據表單"></button><figcaption>活動數據填報：先選範疇，再選對應排放源與係數，輸入用量後由後端計算 tCO₂e。</figcaption></figure>
                <h5 class="fw-bold">標準填報步驟</h5>
                <ol class="small lh-lg">
                    <li>選擇所屬廠區、年度和月份。</li>
                    <li>選擇 Scope 1、2 或 3；排放係數必須屬於相同 Scope。</li>
                    <li>選擇來源類型與排放係數，確認單位和係數來源。</li>
                    <li>輸入大於 0 的活動用量；系統以「用量 × kgCO₂e 係數 ÷ 1000」計算 tCO₂e。</li>
                    <li>需要時上傳 PDF、JPG、PNG、XLSX 或 DOCX 憑證。</li>
                    <li>尚未確認請選「暫存草稿」；確認完成才選「提交送審」。</li>
                </ol>
                <div class="manual-note"><strong>排放係數庫：</strong>用來維護燃料、電力、冷媒、運輸等係數。更新前須核對適用年度、單位、來源機構與啟用狀態；已建立的記錄會保存當時計算結果。</div>
            </div>
        </section>

        <section id="manual-environment" class="manual-section" data-search="水資源 自來水 回收水 廢水 COD BOD SS 廢棄物 有害 清運 聯單 環境">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-faucet-drip"></i>環境資料</span><h2 class="h4 fw-bold mt-2 mb-1">5. 水資源、放流水質與廢棄物</h2></header>
            <div class="manual-section-body">
                <div class="row g-4">
                    <div class="col-lg-6"><h5 class="fw-bold">水資源與能源</h5><ul class="small lh-lg"><li>選擇年度、月份、組織與類型：自來水、地下水、回收水、廢水、太陽能或綠電。</li><li>輸入數量與正確單位；廢水可補充 COD、BOD、SS。</li><li>備註請寫明帳單、抄表或檢測來源，並附上佐證。</li></ul></div>
                    <div class="col-lg-6"><h5 class="fw-bold">事業廢棄物</h5><ul class="small lh-lg"><li>選擇一般 D 類或有害 C 類，填寫廢棄物名稱與重量。</li><li>選擇焚化、掩埋、物理處理或回收再利用。</li><li>清運三聯單號應與附件一致；有害廢棄物尤其需要複核。</li></ul></div>
                </div>
                <div class="manual-note manual-warning"><strong>目前版本注意：</strong>水資源與廢棄物表單儲存後即完成登記；送出前請再次核對組織、期別、單位與數值。</div>
            </div>
        </section>

        <section id="manual-social" class="manual-section" data-search="social governance 社會 治理 員工 多元 薪資 EHS FR SR 訓練 供應鏈 董事會 誠信 吹哨 資安 TCFD">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-people-group"></i>S 與 G</span><h2 class="h4 fw-bold mt-2 mb-1">6. 社會責任與公司治理指標</h2></header>
            <div class="manual-section-body">
                <div class="row g-4">
                    <div class="col-lg-6"><h5 class="fw-bold">社會責任（S）</h5><p class="small">按年度維護多元平等、薪酬、職業安全、教育訓練及供應鏈。FR＝失能傷害次數 × 1,000,000／工時；SR＝損失日數 × 1,000,000／工時，由系統自動計算。</p></div>
                    <div class="col-lg-6"><h5 class="fw-bold">公司治理（G）</h5><p class="small">維護董事會組成與出席率、反貪腐、吹哨案件、ISO 27001／釣魚演練，以及 TCFD 實體風險、轉型風險和綠色產品機會。</p></div>
                </div>
                <div class="manual-note"><strong>避免覆蓋錯誤：</strong>相同組織、年度與指標分類會更新既有資料，不會新增另一筆；請先確認年度和組織。</div>
            </div>
        </section>

        <section id="manual-workflow" class="manual-section" data-search="workflow 工作流 簽核 初審 最終核准 退回 補正 submitted approved_l1 approved_final rejected 狀態">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-list-check"></i>審批</span><h2 class="h4 fw-bold mt-2 mb-1">7. GHG 審批工作流</h2></header>
            <div class="manual-section-body">
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/workflow.png" data-manual-alt="待辦與審批簽核中心"><img src="/esg/assets/manual/workflow.png" loading="lazy" alt="待辦與審批簽核中心"></button><figcaption>審批中心分別列出待初審與待最終複核的 GHG 記錄；填報者只會看到自己的工作任務。</figcaption></figure>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                    <span class="manual-status status-draft">draft 草稿</span><i class="fa-solid fa-arrow-right text-muted"></i>
                    <span class="manual-status status-submitted">submitted 待初審</span><i class="fa-solid fa-arrow-right text-muted"></i>
                    <span class="manual-status status-l1">approved_l1 待複核</span><i class="fa-solid fa-arrow-right text-muted"></i>
                    <span class="manual-status status-final">approved_final 已封存</span>
                    <span class="ms-2 text-muted small">退件時轉為</span><span class="manual-status status-rejected">rejected</span>
                </div>
                <h6 class="fw-bold">初審主管</h6><p class="small">開啟「審核」，核對活動用量、排放係數與憑證。正確則輸入批註並核准；不完整則輸入具體退件原因。</p>
                <h6 class="fw-bold">永續長／CSO</h6><p class="small">只處理 approved_l1。最終核准後記錄封存並納入 Dashboard 與正式報表；後端會阻擋跳級或重複簽核。</p>
                <h6 class="fw-bold">填報者補正</h6><p class="small mb-0">查看退件原因，重新確認憑證及數值。若目前畫面沒有重送按鈕，請由管理者依內部流程協助重新建立或處理補正資料。</p>
            </div>
        </section>

        <section id="manual-reports" class="manual-section" data-search="report 報表 ISO 14064 GRI Excel XLSX 匯出 下載 列印 PDF 年度">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-file-excel"></i>輸出</span><h2 class="h4 fw-bold mt-2 mb-1">8. 報表、Excel 與列印總結</h2></header>
            <div class="manual-section-body">
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/reports.png" data-manual-alt="智慧報表中心"><img src="/esg/assets/manual/reports.png" loading="lazy" alt="智慧報表中心"></button><figcaption>報表中心：先選年度，再下載 ISO 14064-1 清冊、GRI Content Index 或開啟列印總結。</figcaption></figure>
                <ul class="small lh-lg"><li><strong>ISO 14064-1 Excel：</strong>含廠區、期別、範疇、活動量、係數、計算結果與狀態，只輸出最終核准 GHG 資料。</li><li><strong>GRI Content Index：</strong>提供 GRI 302、303、305、306、401、403、404、405、205 等系統資料來源對照。</li><li><strong>年度總結：</strong>顯示三大範疇、月度與廠區統計及簽章欄；使用瀏覽器列印功能可另存 PDF。</li></ul>
                <div class="manual-note manual-info"><strong>下載沒反應：</strong>確認角色有 reports/export 權限、瀏覽器沒有阻擋下載，並重新選擇年度後再試一次。</div>
            </div>
        </section>

        <section id="manual-admin" class="manual-section" data-search="admin 系統管理 使用者 帳號 角色 組織 廠區 稽核 日誌 設定 SMTP 安全參數">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-screwdriver-wrench"></i>管理者</span><h2 class="h4 fw-bold mt-2 mb-1">9. 系統管理操作</h2></header>
            <div class="manual-section-body">
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/admin-users.png" data-manual-alt="帳號與角色管理"><img src="/esg/assets/manual/admin-users.png" loading="lazy" alt="帳號與角色管理"></button><figcaption>帳號管理：建立帳號時填寫登入帳號、至少 12 字元的密碼、姓名、Email、組織與角色；停用帳號可立即阻止登入。</figcaption></figure>
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/admin-settings.png" data-manual-alt="全域參數設定"><img src="/esg/assets/manual/admin-settings.png" loading="lazy" alt="全域參數設定"></button><figcaption>全域設定：只有 Super Admin 可修改。設定會影響減碳目標、強度分母、工作流程與通知，儲存前應依變更程序覆核。</figcaption></figure>
                <div class="row g-3">
                    <div class="col-lg-6"><h6 class="fw-bold">帳號與角色</h6><p class="small text-muted">建立後以最低必要權限為原則。人員離職或轉調時停用原帳號，不要多人共用同一帳號。</p></div>
                    <div class="col-lg-6"><h6 class="fw-bold">組織架構</h6><p class="small text-muted">組織代碼須唯一；建立父子層級時避免把自己設為父組織，並確認廠區、公司、部門類型。</p></div>
                    <div class="col-lg-6"><h6 class="fw-bold">安全審計日誌</h6><p class="small text-muted">可依模組和行為篩選登入、建立、更新、刪除、簽核與匯出。密碼、Token 與 SMTP 密碼會自動遮罩。</p></div>
                    <div class="col-lg-6"><h6 class="fw-bold">全域參數</h6><p class="small text-muted">修改基準年、目標比例、營收或員工數會立即影響 Dashboard；請記錄變更依據和核准人。</p></div>
                </div>
            </div>
        </section>

        <section id="manual-security" class="manual-section" data-search="安全 資安 木馬 惡意程式 病毒 附件 session csrf 密碼 登出 備份 權限">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-shield-halved"></i>安全</span><h2 class="h4 fw-bold mt-2 mb-1">10. 安全操作與附件規範</h2></header>
            <div class="manual-section-body">
                <ul class="small lh-lg"><li>密碼至少 12 字元，建議混合大小寫、數字與符號；不要使用公司名、姓名或共用密碼。</li><li>系統閒置過久會使 Session 失效；看到 CSRF 錯誤時重新整理並重新登入，不要重複送出舊表單。</li><li>附件只能經授權下載，直接 URL 會被伺服器拒絕；不要把附件網址轉傳給無權限人員。</li><li>上傳會檢查大小、MIME、圖片／PDF／Office 結構與 ZIP bomb；若伺服器設定 ClamAV，還會執行惡意程式掃描。</li><li>只上傳工作必要的發票、帳單、檢測報告和聯單；上傳前先在端點防毒軟體完成掃描。</li><li>完成操作後安全登出；公共電腦不要記住密碼，也不要保留下載的敏感報表。</li></ul>
                <div class="manual-note manual-warning"><strong>疑似遭入侵時：</strong>立即停止操作、保留畫面與時間、通知系統管理者；不要自行刪除可疑帳號、檔案或稽核紀錄，以免破壞調查證據。</div>
            </div>
        </section>

        <section id="manual-ai" class="manual-section" data-search="AI 客服 小編 OpenAI API 模型 警告 斷線 系統問題 資料庫">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-robot"></i>智慧支援</span><h2 class="h4 fw-bold mt-2 mb-1">11. AI 客服小編</h2></header>
            <div class="manual-section-body">
                <figure class="manual-shot mb-4"><button type="button" data-manual-image="/esg/assets/manual/ai-assistant-settings.png" data-manual-alt="AI 客服小編後台設定"><img src="/esg/assets/manual/ai-assistant-settings.png" loading="lazy" alt="AI 客服小編後台設定"></button><figcaption>實際畫面：Super Admin 可啟停客服、選擇模型、更新加密 API Key 並執行連線測試。點圖可放大。</figcaption></figure>
                <ol class="small lh-lg">
                    <li>登入後點選右下角「AI 客服」開啟對話視窗。</li>
                    <li>可詢問一般系統操作、權限、工作流、報表、非資安性錯誤排解，以及資料表與欄位用途。</li>
                    <li>AI 客服不能修改資料、執行 SQL，也不會取得密碼、API 金鑰或個別使用者資料。</li>
                    <li><strong class="text-danger">資安問題零容忍：</strong>詢問攻擊、漏洞、入侵、惡意程式、憑證破解、防護繞過或資安事件時，立即顯示嚴重警告並斷線 3 分鐘，倒數完成後自動恢復。</li>
                    <li>非本系統問題會收到警告；同一登入工作階段達 3 次時立即中止客服連線，重新整理不會解除。</li>
                    <li>Super Admin 可在「系統維護與管理 → AI 客服小編設定」選擇模型、更新 API Key、啟停客服及測試連線。</li>
                </ol>
                <div class="manual-note manual-info"><strong>建議提問：</strong>請描述所在頁面、角色、預期動作與畫面訊息，例如「我是填報人員，GHG 送審後為什麼 Dashboard 沒資料？」</div>
            </div>
        </section>

        <section id="manual-faq" class="manual-section manual-faq" data-search="FAQ 常見問題 錯誤 故障 排除 找不到 403 404 500 419 CSRF 空白 無法 上傳 下載 數字 AI 客服">
            <header class="manual-section-header"><span class="manual-badge"><i class="fa-solid fa-life-ring"></i>自助排解</span><h2 class="h4 fw-bold mt-2 mb-1">12. 常見問題與解決方式</h2></header>
            <div class="manual-section-body">
                <details><summary>為什麼 Dashboard 數字和清冊總和不同？</summary><p class="small text-muted">Dashboard 和正式報表只計入 approved_final。清冊若包含 draft、submitted、approved_l1 或 rejected，直接加總會不同。請把狀態篩選為「最終核准」。</p></details>
                <details><summary>為什麼我看不到新增、審核或系統管理選單？</summary><p class="small text-muted">左側選單會依角色隱藏未授權功能。請先確認右上角角色；若職務需要，請由 Super Admin 調整角色，不要借用他人帳號。</p></details>
                <details><summary>出現 403 Forbidden 怎麼辦？</summary><p class="small text-muted">表示角色沒有該頁或動作權限，或正在嘗試讀取不屬於自己的資料。返回上一頁並聯絡管理者確認角色及組織。</p></details>
                <details><summary>出現「CSRF 權杖已失效」怎麼辦？</summary><p class="small text-muted">通常是頁面開太久、Session 逾時或從舊頁籤送出。重新登入、重新開啟功能頁並再次輸入即可。</p></details>
                <details><summary>為什麼附件上傳失敗？</summary><p class="small text-muted">確認格式為 PDF、JPG、JPEG、PNG、XLSX 或 DOCX，檔案小於 20 MB，內容沒有損壞、加密或異常壓縮。疑似惡意檔案會被拒絕。</p></details>
                <details><summary>為什麼不能刪除 GHG 記錄？</summary><p class="small text-muted">只有原填報者可刪除自己的 draft 或 rejected；submitted、approved_l1、approved_final 均不可直接刪除，以保留簽核與查證軌跡。</p></details>
                <details><summary>排放量計算為 0 或係數選不到？</summary><p class="small text-muted">用量與係數必須大於 0，且係數需為啟用狀態並與 Scope 相符。請到係數庫確認類別、適用年度、單位與啟用開關。</p></details>
                <details><summary>Excel 下載後沒有資料？</summary><p class="small text-muted">確認選擇的年度確實有 approved_final GHG 記錄。若只有草稿或待審資料，正式 ISO 清冊會是空的。</p></details>
                <details><summary>登入密碼錯誤或帳號被鎖定？</summary><p class="small text-muted">檢查大小寫與輸入法。連續錯誤 5 次會鎖定 15 分鐘；等待後再試，或請 Super Admin 確認帳號是否停用。</p></details>
                <details><summary>系統顯示 500 或空白頁？</summary><p class="small text-muted">記錄發生時間、頁面網址和剛才的操作，通知技術管理者檢查 Apache／PHP 錯誤日誌與資料庫連線。請勿持續重複送出表單。</p></details>
                <details><summary>AI 客服顯示尚未設定或連線失敗？</summary><p class="small text-muted">請由 Super Admin 開啟「AI 客服小編設定」，確認已啟用、API Key 正確且所選模型有帳戶使用權限，再按「測試連線」。API Key 不會顯示給一般使用者。</p></details>
                <details><summary>AI 客服為什麼顯示嚴重資安警告並斷線？</summary><p class="small text-muted">系統偵測到資安相關詢問，依零容忍規則立即中止客服 3 分鐘。請等待畫面倒數完成；若確實發生資安事件，請停止操作並直接聯絡資安管理員，不要透過 AI 客服處理。</p></details>
                <details><summary>如何取得進一步協助？</summary><p class="small text-muted">提供帳號名稱、角色、功能頁、發生時間、錯誤訊息和重現步驟。不要透過 Email 或通訊軟體傳送密碼、Session Cookie 或 SMTP 密碼。</p></details>
            </div>
        </section>
    </main>
</div>

<dialog id="manualImageDialog" class="manual-image-dialog">
    <button id="manualDialogClose" type="button" class="manual-dialog-close" aria-label="關閉放大圖"><i class="fa-solid fa-xmark"></i></button>
    <img id="manualDialogImage" src="" alt="">
</dialog>
