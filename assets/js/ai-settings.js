document.addEventListener('DOMContentLoaded', () => {
    const testButton = document.getElementById('testAiConnection');
    if (!testButton) return;
    const keyInput = document.getElementById('openaiApiKey');
    const modelInput = document.getElementById('openaiModel');
    const result = document.getElementById('aiTestResult');
    const toggle = document.getElementById('toggleApiKey');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    toggle.addEventListener('click', () => {
        const reveal = keyInput.type === 'password';
        keyInput.type = reveal ? 'text' : 'password';
        toggle.querySelector('i').className = reveal ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    });

    testButton.addEventListener('click', async () => {
        testButton.disabled = true;
        result.className = 'alert alert-secondary py-2';
        result.textContent = '正在透過伺服器測試 OpenAI Responses API…';
        try {
            const response = await fetch('/esg/admin/ai-assistant/test', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ api_key: keyInput.value, model: modelInput.value }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || '測試失敗');
            result.className = 'alert alert-success py-2';
            result.textContent = `${data.message} 模型：${data.data.model}，耗時：${data.data.latency_ms} ms。`;
        } catch (error) {
            result.className = 'alert alert-danger py-2';
            result.textContent = error.message || '連線測試失敗。';
        } finally {
            testButton.disabled = false;
        }
    });
});
