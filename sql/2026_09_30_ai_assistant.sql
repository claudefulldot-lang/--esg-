USE `esg_db`;
SET NAMES utf8mb4;

INSERT IGNORE INTO `sys_settings`
    (`setting_key`, `setting_group`, `setting_value`, `input_type`, `options_json`, `description`)
VALUES
    ('ai_assistant_enabled', 'ai_assistant', '1', 'switch', NULL, '啟用 AI 客服小編'),
    ('openai_model', 'ai_assistant', 'gpt-6.1-sol', 'select', '["gpt-6.1-sol", "gpt-6-astra", "gpt-6-luna"]', 'OpenAI Responses API 模型'),
    ('openai_api_key', 'ai_assistant', '', 'password', NULL, 'OpenAI API 金鑰（AES-256-GCM 加密保存）');
