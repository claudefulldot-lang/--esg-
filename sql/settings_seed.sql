USE `esg_db`;

SET NAMES utf8mb4;

-- 清空並重建結構化的全域參數表
TRUNCATE TABLE `sys_settings`;

INSERT INTO `sys_settings` (`setting_key`, `setting_group`, `setting_value`, `input_type`, `options_json`, `description`) VALUES
-- 1. 企業主體與系統基底 (general)
('system_name', 'general', '企業級 ESG 智慧管理與碳盤查系統', 'text', NULL, '系統全域名稱 (顯示於頂部與登入頁)'),
('company_name', 'general', '台灣晶綠科技股份有限公司', 'text', NULL, '企業抬頭全名 (印製於正式報告書)'),
('industry_sector', 'general', '半導體與電子零組件製造業', 'select', '["半導體與電子零組件製造業", "光電面板製造業", "石化與塑膠工業", "鋼鐵金屬製造業", "金融保險與服務業", "生技醫療業"]', '企業所屬產業類別 (用於同業基準比對)'),

-- 2. 溫室氣體與環境基準 (environmental)
('base_year', 'environmental', '2022', 'number', NULL, '溫室氣體基準年 (Base Year，減碳起點年)'),
('target_year', 'environmental', '2030', 'number', NULL, 'SBTi / NDC 中長期減碳承諾目標年'),
('target_reduction_pct', 'environmental', '30.0', 'number', NULL, '減碳承諾目標比例 (%)，相較於基準年需減少之比例'),
('default_gwp_version', 'environmental', 'IPCC_AR5', 'select', '["IPCC_AR5", "IPCC_AR6", "IPCC_AR4"]', '全球暖化潛勢 (GWP) 評估版本 (AR5 甲烷=28; AR6 甲烷=27.9)'),
('carbon_tax_rate_per_ton', 'environmental', '300', 'number', NULL, '每公噸碳費預估單價 (NTD/tCO2e，計算內部碳定價財務衝擊)'),
('inventory_boundary', 'environmental', 'Operational_Control', 'select', '["Operational_Control", "Financial_Control", "Equity_Share"]', '碳盤查組織邊界方法 (營運控制權法 / 財務控制權法 / 股權比例法)'),

-- 3. 財務營收與強度基準 (finance)
('annual_revenue_millions', 'finance', '3850', 'number', NULL, '年度合併營業額 (百萬新台幣，計算營收碳排強度分母)'),
('total_employee_count', 'finance', '520', 'number', NULL, '全集團受僱員工人數 (計算人均碳排強度分母)'),
('revenue_currency', 'finance', 'TWD', 'select', '["TWD", "USD", "EUR", "CNY"]', '財務營收計價幣別'),

-- 4. 工作流程與審查期限 SLA (workflow)
('monthly_submission_deadline_day', 'workflow', '10', 'number', NULL, '每月活動數據填報截止日 (每月幾號前須完成送審)'),
('approval_sla_alert_days', 'workflow', '3', 'number', NULL, '主管審核逾期警示天數 (SLA，逾期標示提醒)'),
('enforce_attachment_upload', 'workflow', '1', 'switch', NULL, '強制檢附佐證憑證開關 (1: 送審必須上傳單據, 0: 允許無附件送審)'),
('workflow_approval_levels', 'workflow', '2', 'select', '["2", "3"]', '審批簽核層級數 (2: 部門初審+委員會複核; 3: 部門初審+廠長審查+委員會複核)'),

-- 5. 系統資安與防護政策 (security)
('max_failed_login_attempts', 'security', '5', 'number', NULL, '連續密碼錯誤次數上限 (觸發帳號鎖定門檻)'),
('account_lockout_duration_mins', 'security', '15', 'number', NULL, '帳號鎖定持續時間 (分鐘，防範字典檔暴力破解)'),
('session_timeout_mins', 'security', '60', 'number', NULL, '登入閒置逾時自動登出時間 (分鐘)'),
('max_upload_size_mb', 'security', '20', 'number', NULL, '憑證檔案上傳大小上限 (MB)'),
('allowed_upload_extensions', 'security', 'pdf, jpg, jpeg, png, xlsx, docx', 'text', NULL, '允許上傳檔案副檔名白名單 (逗號分隔)'),

-- 6. 通知推播與 SMTP 郵件 (notification)
('enable_email_notifications', 'notification', '1', 'switch', NULL, '是否啟用系統 Email 自動通知 (每月催填與退件提醒)'),
('smtp_host', 'notification', 'smtp.mailtrap.io', 'text', NULL, 'SMTP 郵件主機位址 (如: smtp.gmail.com 或企業內部 Exchange)'),
('smtp_port', 'notification', '2525', 'number', NULL, 'SMTP 伺服器通訊傳輸埠 (Port)'),
('smtp_user', 'notification', 'esg_system@esg-pro.local', 'text', NULL, 'SMTP 認證帳號'),
('smtp_pass', 'notification', '', 'text', NULL, 'SMTP 認證密碼 / 應用程式密碼'),
('smtp_from_name', 'notification', 'ESG-Pro 智慧永續管理平台', 'text', NULL, '系統通知信寄件人名稱抬頭'),

-- 7. AI 客服小編（API 金鑰實際儲存時使用 AES-256-GCM 加密）
('ai_assistant_enabled', 'ai_assistant', '1', 'switch', NULL, '啟用 AI 客服小編'),
('openai_model', 'ai_assistant', 'gpt-6.1-sol', 'select', '["gpt-6.1-sol", "gpt-6-astra", "gpt-6-luna"]', 'OpenAI Responses API 模型'),
('openai_api_key', 'ai_assistant', '', 'password', NULL, 'OpenAI API 金鑰（加密保存）');
