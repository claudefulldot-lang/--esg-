<aside class="ai-support" id="aiSupport" aria-label="ESG-Pro AI 客服小編">
    <button type="button" class="ai-support__launcher" id="aiSupportLauncher" aria-expanded="false" aria-controls="aiSupportPanel">
        <i class="fa-solid fa-comments" aria-hidden="true"></i>
        <span>AI 客服</span>
        <span class="ai-support__status-dot" id="aiSupportDot" aria-hidden="true"></span>
    </button>

    <section class="ai-support__panel" id="aiSupportPanel" hidden>
        <header class="ai-support__header">
            <div>
                <div class="fw-bold"><i class="fa-solid fa-robot me-2"></i>AI 客服小編</div>
                <small id="aiSupportStatusText">僅回答本系統相關問題</small>
            </div>
            <button type="button" class="btn btn-sm text-white" id="aiSupportClose" aria-label="關閉 AI 客服">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </header>

        <div class="ai-support__scope">
            <i class="fa-solid fa-shield-halved"></i>
            僅限 ESG-Pro 一般操作、權限、報表與資料庫結構。資安問題會立即嚴重警告並斷線 3 分鐘；其他非本系統問題累計 3 次將中止連線。
        </div>

        <div class="ai-support__messages" id="aiSupportMessages" role="log" aria-live="polite">
            <div class="ai-message ai-message--assistant">
                您好，我是 ESG-Pro AI 客服。您可以詢問「如何新增 GHG 資料」、「為什麼報表沒有資料」或「資料表之間如何關聯」。
            </div>
        </div>

        <div class="ai-support__quick" id="aiSupportQuick">
            <button type="button" data-ai-question="如何新增並送審 GHG 活動數據？">GHG 填報</button>
            <button type="button" data-ai-question="為什麼 Dashboard 沒有顯示剛輸入的資料？">資料未顯示</button>
            <button type="button" data-ai-question="請說明系統主要資料表與關聯。">資料庫結構</button>
        </div>

        <div class="ai-support__warning" id="aiSupportWarning">警告次數：<strong>0</strong> / 3</div>
        <form class="ai-support__form" id="aiSupportForm">
            <label class="visually-hidden" for="aiSupportInput">輸入系統問題</label>
            <textarea id="aiSupportInput" rows="2" maxlength="800" placeholder="輸入 ESG-Pro 系統問題…" required></textarea>
            <button type="submit" id="aiSupportSend" aria-label="送出問題"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </section>
</aside>
