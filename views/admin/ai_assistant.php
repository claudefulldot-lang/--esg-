<?php use App\Helpers\Security; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="fa-solid fa-robot text-info me-2"></i>AI 客服小編設定</h4>
        <p class="text-muted mb-0">設定 OpenAI Responses API、模型與客服啟用狀態。僅限 Super Admin。</p>
    </div>
    <a href="/esg/manual" class="btn btn-outline-secondary"><i class="fa-solid fa-book-open me-1"></i>查看操作手冊</a>
</div>

<div class="alert alert-info border-start border-4 border-info shadow-sm">
    <strong>範圍防護已啟用：</strong>AI 只接收一般系統操作知識與資料庫結構摘要，不會取得密碼、API 金鑰、個資或任意資料列。資安問題立即斷線 3 分鐘；其他非本系統問題達 3 次後中止該登入 Session。
</div>

<form action="/esg/admin/ai-assistant/store" method="POST" id="aiSettingsForm">
    <?= Security::csrfField() ?>
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3 fw-bold"><i class="fa-solid fa-sliders me-2 text-primary"></i>API 與模型</div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-4">
                        <input type="hidden" name="ai_assistant_enabled" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="aiAssistantEnabled" name="ai_assistant_enabled" value="1" <?= ($settings['ai_assistant_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="aiAssistantEnabled">啟用全站 AI 客服小編</label>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="openaiModel">OpenAI 模型</label>
                        <select class="form-select" id="openaiModel" name="openai_model" required>
                            <?php foreach ($allowedModels as $modelId => $modelLabel): ?>
                                <option value="<?= Security::e($modelId) ?>" <?= ($settings['openai_model'] ?? '') === $modelId ? 'selected' : '' ?>>
                                    <?= Security::e($modelLabel) ?> — <?= Security::e($modelId) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">預設使用 gpt-6.1-sol；所有模型透過 Responses API 呼叫，且不啟用外部工具。</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="openaiApiKey">OpenAI API Key</label>
                        <div class="input-group">
                            <input type="password" class="form-control font-monospace" id="openaiApiKey" name="openai_api_key" autocomplete="new-password" placeholder="留空即保留目前金鑰">
                            <button class="btn btn-outline-secondary" type="button" id="toggleApiKey" aria-label="顯示或隱藏 API 金鑰"><i class="fa-solid fa-eye"></i></button>
                        </div>
                        <div class="form-text">目前狀態：<span class="fw-semibold"><?= Security::e($maskedApiKey) ?></span>。新金鑰會以 AES-256-GCM 加密保存，畫面不會回傳完整舊金鑰。</div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="clearApiKey" name="clear_api_key">
                        <label class="form-check-label text-danger" for="clearApiKey">清除已儲存的 API 金鑰並停用連線能力</label>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-primary" id="testAiConnection">
                            <i class="fa-solid fa-plug-circle-check me-1"></i>測試連線
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>安全儲存設定
                        </button>
                    </div>
                    <div class="mt-3" id="aiTestResult" role="status" aria-live="polite"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold"><i class="fa-solid fa-shield-halved text-success me-2"></i>安全控制</div>
                <div class="card-body">
                    <ul class="mb-0 small lh-lg">
                        <li>API 金鑰只在伺服器端解密，瀏覽器無法讀取。</li>
                        <li>請求設為 <code>store: false</code>，不使用 Web Search、File Search 或程式工具。</li>
                        <li>結構化輸出先判定是否屬於本系統；越界回答不會顯示。</li>
                        <li>資安關鍵詞由本機先攔截；隱晦資安意圖由模型分類後立即斷線 3 分鐘。</li>
                        <li>問題與回答不寫入資料庫；稽核日誌只保存事件、模型與字數。</li>
                        <li>客服無 SQL 執行能力，也無法修改任何 ESG 資料。</li>
                    </ul>
                </div>
            </div>
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 fw-bold"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>三次警告機制</div>
                <div class="card-body small">
                    <p>非 ESG-Pro 問題第一次及第二次會顯示警告；第三次會將目前登入 Session 標記為斷線。</p>
                    <p class="mb-0">重新整理不會解除；使用者必須安全登出並重新登入。正常的系統問題不會增加警告次數。</p>
                </div>
            </div>
            <div class="alert alert-danger mt-4 mb-0 shadow-sm">
                <strong><i class="fa-solid fa-ban me-1"></i>資安問題零容忍：</strong>詢問攻擊、漏洞、入侵、惡意程式、憑證破解、防護繞過或資安事件時，不累計一般警告，直接嚴重警告並斷線 180 秒。
            </div>
        </div>
    </div>
</form>
