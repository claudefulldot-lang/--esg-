document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('aiSupport');
    if (!root) return;

    const launcher = document.getElementById('aiSupportLauncher');
    const panel = document.getElementById('aiSupportPanel');
    const close = document.getElementById('aiSupportClose');
    const form = document.getElementById('aiSupportForm');
    const input = document.getElementById('aiSupportInput');
    const send = document.getElementById('aiSupportSend');
    const messages = document.getElementById('aiSupportMessages');
    const warning = document.getElementById('aiSupportWarning');
    const dot = document.getElementById('aiSupportDot');
    const statusText = document.getElementById('aiSupportStatusText');
    const quick = document.getElementById('aiSupportQuick');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    let state = { enabled: true, configured: false, warnings: 0, max_warnings: 3, disconnected: false, disconnect_reason: null, disconnect_until: null };
    let busy = false;
    let reconnectTimer = null;

    const appendMessage = (text, type = 'assistant') => {
        const item = document.createElement('div');
        item.className = `ai-message ai-message--${type}`;
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
        return item;
    };

    const renderState = () => {
        warning.innerHTML = `警告次數：<strong>${state.warnings || 0}</strong> / ${state.max_warnings || 3}`;
        warning.classList.toggle('is-active', (state.warnings || 0) > 0);
        const unavailable = !state.enabled || !state.configured || state.disconnected;
        input.disabled = unavailable || busy;
        send.disabled = unavailable || busy;
        quick.querySelectorAll('button').forEach(button => { button.disabled = unavailable || busy; });
        dot.classList.toggle('is-offline', unavailable);
        if (state.disconnected && state.disconnect_reason === 'security') {
            const remaining = Math.max(0, (state.disconnect_until || 0) - Math.floor(Date.now() / 1000));
            const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
            const seconds = String(remaining % 60).padStart(2, '0');
            statusText.textContent = `嚴重資安警告 · ${minutes}:${seconds} 後恢復`;
        }
        else if (state.disconnected) statusText.textContent = '已達 3 次警告，連線中止';
        else if (!state.enabled) statusText.textContent = '客服目前已停用';
        else if (!state.configured) statusText.textContent = '尚未完成 API 設定';
        else statusText.textContent = `連線中 · ${state.model || 'OpenAI'}`;
    };

    const scheduleReconnect = () => {
        if (reconnectTimer) clearInterval(reconnectTimer);
        reconnectTimer = null;
        if (!state.disconnected || state.disconnect_reason !== 'security' || !state.disconnect_until) return;
        reconnectTimer = setInterval(async () => {
            const remaining = state.disconnect_until - Math.floor(Date.now() / 1000);
            if (remaining <= 0) {
                clearInterval(reconnectTimer);
                reconnectTimer = null;
                await loadStatus();
                appendMessage('3 分鐘資安管制已結束，AI 客服連線已恢復。請僅詢問本系統的一般操作與說明。', 'assistant');
                return;
            }
            renderState();
        }, 1000);
    };

    const loadStatus = async () => {
        try {
            const baseUrl = window.ESG_BASE_URL || '/esg';
            const response = await fetch(baseUrl + '/api/ai-assistant/status', { credentials: 'same-origin' });
            const data = await response.json();
            if (data.success) state = data.data;
        } catch (_) {
            state.enabled = false;
        }
        renderState();
        scheduleReconnect();
    };

    const setOpen = (open) => {
        panel.hidden = !open;
        launcher.setAttribute('aria-expanded', String(open));
        if (open && !input.disabled) setTimeout(() => input.focus(), 80);
    };

    launcher.addEventListener('click', () => setOpen(panel.hidden));
    close.addEventListener('click', () => setOpen(false));

    const ask = async (question) => {
        const text = question.trim();
        if (!text || busy || input.disabled) return;
        appendMessage(text, 'user');
        input.value = '';
        busy = true;
        renderState();
        const typing = appendMessage('正在查詢本系統操作知識…', 'typing');
        try {
            const baseUrl = window.ESG_BASE_URL || '/esg';
            const response = await fetch(baseUrl + '/api/ai-assistant/chat', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ message: text }),
            });
            const data = await response.json();
            typing.remove();
            if (typeof data.warnings === 'number') state.warnings = data.warnings;
            if (data.disconnected) {
                state.disconnected = true;
                state.disconnect_reason = data.disconnect_reason || 'scope';
                state.disconnect_until = data.disconnect_until || null;
            }
            const messageType = data.category === 'security' ? 'error' : (data.in_scope === false ? 'warning' : (data.success ? 'assistant' : 'error'));
            appendMessage(data.message || '客服暫時無法回答，請稍後再試。', messageType);
            scheduleReconnect();
        } catch (_) {
            typing.remove();
            appendMessage('連線失敗，請檢查網路或通知系統管理員。', 'error');
        } finally {
            busy = false;
            renderState();
        }
    };

    form.addEventListener('submit', event => {
        event.preventDefault();
        ask(input.value);
    });
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    quick.addEventListener('click', event => {
        const button = event.target.closest('[data-ai-question]');
        if (button) ask(button.dataset.aiQuestion || '');
    });

    loadStatus();
});
