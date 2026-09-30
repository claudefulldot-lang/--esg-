USE `esg_db`;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 1. 系統角色
INSERT INTO `sys_roles` (`id`, `role_key`, `role_name`, `description`, `permissions`) VALUES
(1, 'super_admin', '系統最高管理員', '全系統維護、組織配置、角色指派、稽核日誌與系統參數設定', '{"all": true}'),
(2, 'cso', '永續長 / ESG推動委員', '制定減碳政策、監控戰情儀表板、複核數據、產出永續報告書', '{"dashboard": ["read"], "ghg": ["read", "approve"], "environment": ["read", "approve"], "social": ["read", "approve"], "governance": ["read", "approve"], "workflow": ["read", "approve_final"], "reports": ["read", "export"]}'),
(3, 'submitter', '廠區/部門填報人員', '活動數據填報、憑證附件上傳、暫存與送審', '{"dashboard": ["read"], "ghg": ["read", "create", "update"], "environment": ["read", "create", "update"], "social": ["read", "create", "update"], "governance": ["read", "create", "update"], "workflow": ["read", "submit"], "reports": ["read"]}'),
(4, 'approver', '部門初審主管', '初審部門活動數據、查驗憑證單據、審批通過或退回修正', '{"dashboard": ["read"], "ghg": ["read", "approve_l1", "reject"], "environment": ["read", "approve_l1", "reject"], "social": ["read", "approve_l1", "reject"], "governance": ["read", "approve_l1", "reject"], "workflow": ["read", "approve_l1", "reject"], "reports": ["read"]}'),
(5, 'auditor', '內部稽核 / 第三方驗證員', '唯讀權限、歷史軌跡查詢、公式歷程查核、附件單據抽查', '{"dashboard": ["read"], "ghg": ["read"], "environment": ["read"], "social": ["read"], "governance": ["read"], "workflow": ["read"], "reports": ["read", "export"], "audit_logs": ["read"]}');

-- 2. 組織架構
INSERT INTO `sys_organizations` (`id`, `org_code`, `org_name`, `org_type`, `parent_id`, `leader_name`, `contact_phone`, `contact_email`, `sort_order`) VALUES
(1, 'ORG_GRP', '永續綠能控股集團', 'group', NULL, '林董事長', '02-23456789', 'hq@esg-pro.local', 1),
(2, 'ORG_TW', '台灣晶綠科技股份有限公司', 'company', 1, '陳總經理', '02-23456780', 'taiwan@esg-pro.local', 2),
(3, 'PLANT_HC', '新竹一廠 (半導體製造廠)', 'plant', 2, '張廠長', '03-5781234', 'hc_plant@esg-pro.local', 3),
(4, 'PLANT_TN', '台南二廠 (封裝測試廠)', 'plant', 2, '李廠長', '06-5051234', 'tn_plant@esg-pro.local', 4),
(5, 'DEPT_EHS', '環境安全衛生部 (EHS)', 'dept', 2, '王經理', '03-5781235', 'ehs@esg-pro.local', 5),
(6, 'DEPT_FAC', '廠務工程部', 'dept', 3, '趙經理', '03-5781236', 'facility@esg-pro.local', 6),
(7, 'DEPT_HR', '人力資源部 (HR)', 'dept', 2, '孫經理', '02-23456785', 'hr@esg-pro.local', 7),
(8, 'DEPT_SEC', '董事會秘書室 / 永續推動小組', 'dept', 1, '周主任', '02-23456788', 'cso_office@esg-pro.local', 8);

-- 3. 使用者帳號 (預設密碼均為 admin123)
INSERT INTO `sys_users` (`id`, `username`, `password_hash`, `real_name`, `email`, `phone`, `org_id`, `role_id`, `status`) VALUES
(1, 'admin', '$2y$10$WST5aP3T.LPIZw7RCEkuAOwhTbYo4PtxUBVq.ZEahEuD1RiR0DJYO', '系統超級管理員', 'admin@esg-pro.local', '0912-345-678', 1, 1, 1),
(2, 'cso_chen', '$2y$10$WST5aP3T.LPIZw7RCEkuAOwhTbYo4PtxUBVq.ZEahEuD1RiR0DJYO', '陳永續長', 'cso@esg-pro.local', '0922-345-678', 8, 2, 1),
(3, 'dept_approver', '$2y$10$WST5aP3T.LPIZw7RCEkuAOwhTbYo4PtxUBVq.ZEahEuD1RiR0DJYO', '王審核主管 (EHS)', 'approver@esg-pro.local', '0933-345-678', 5, 4, 1),
(4, 'submitter_hc', '$2y$10$WST5aP3T.LPIZw7RCEkuAOwhTbYo4PtxUBVq.ZEahEuD1RiR0DJYO', '張填報員 (新竹廠務)', 'submitter_hc@esg-pro.local', '0944-345-678', 6, 3, 1),
(5, 'submitter_tn', '$2y$10$WST5aP3T.LPIZw7RCEkuAOwhTbYo4PtxUBVq.ZEahEuD1RiR0DJYO', '李填報員 (台南廠務)', 'submitter_tn@esg-pro.local', '0955-345-678', 4, 3, 1),
(6, 'auditor_wang', '$2y$10$WST5aP3T.LPIZw7RCEkuAOwhTbYo4PtxUBVq.ZEahEuD1RiR0DJYO', '黃稽核員 (第三方查證)', 'auditor@esg-pro.local', '0966-345-678', 1, 5, 1);

-- 4. 碳排放係數庫 (依據環境部、能源局與 IPCC 數據)
INSERT INTO `esg_emission_factors` (`id`, `category`, `fuel_name`, `activity_unit`, `co2_factor`, `ch4_factor`, `n2o_factor`, `total_factor_co2e`, `source_org`, `applicable_year`, `is_active`, `created_by`) VALUES
(1, 'Scope1_Fuel', '柴油 (固定發電機/鍋爐)', 'L', 2.606000, 0.000140, 0.000028, 2.614000, '台灣環境部 1.2版', 2024, 1, 1),
(2, 'Scope1_Fuel', '天然氣 (鍋爐/加熱爐)', 'm3', 2.090000, 0.000050, 0.000005, 2.091300, '台灣環境部 1.2版', 2024, 1, 1),
(3, 'Scope1_Fuel', '液化石油氣 (LPG)', 'kg', 3.001000, 0.000060, 0.000010, 3.004000, '台灣環境部 1.2版', 2024, 1, 1),
(4, 'Scope1_Fuel', '燃料重油', 'L', 3.110000, 0.000120, 0.000025, 3.117000, '台灣環境部 1.2版', 2024, 1, 1),
(5, 'Scope1_Mobile', '車用汽油 (公務車/客車)', 'L', 2.263100, 0.000250, 0.000080, 2.270000, '台灣環境部 1.2版', 2024, 1, 1),
(6, 'Scope1_Mobile', '車用柴油 (貨車/堆高機)', 'L', 2.606000, 0.000140, 0.000028, 2.614000, '台灣環境部 1.2版', 2024, 1, 1),
(7, 'Scope1_Fugitive', '冷媒 R-134a (空調逸散)', 'kg', 0.000000, 0.000000, 0.000000, 1430.000000, 'IPCC AR5 GWP', 2024, 1, 1),
(8, 'Scope1_Fugitive', '冷媒 R-410A (冰水主機)', 'kg', 0.000000, 0.000000, 0.000000, 2088.000000, 'IPCC AR5 GWP', 2024, 1, 1),
(9, 'Scope1_Fugitive', '化糞池逸散 (CH4)', '人年', 0.000000, 0.160000, 0.000000, 4.480000, '台灣環境部 1.2版', 2024, 1, 1),
(10, 'Scope1_Fugitive', '二氧化碳滅火器補充', 'kg', 1.000000, 0.000000, 0.000000, 1.000000, 'IPCC AR5 GWP', 2024, 1, 1),
(11, 'Scope2_Electricity', '台電外購一般電力', 'kWh', 0.495000, 0.000000, 0.000000, 0.495000, '經濟部能源局 2023公告', 2024, 1, 1),
(12, 'Scope2_Steam', '外購蒸氣 (園區管道供應)', 'ton', 320.000000, 0.000000, 0.000000, 320.000000, '台灣環境部 1.2版', 2024, 1, 1),
(13, 'Scope3', '航空公務商務差旅 (經濟艙)', '人公里', 0.150000, 0.000000, 0.000000, 0.150000, '英國 DEFRA / GHG Protocol', 2024, 1, 1),
(14, 'Scope3', '公路重型貨卡原物料運輸', '噸公里', 0.089000, 0.000000, 0.000000, 0.089000, '台灣環境部 1.2版', 2024, 1, 1),
(15, 'Scope3', '事業廢棄物委外掩埋處理', 'ton', 350.000000, 0.000000, 0.000000, 350.000000, '台灣環境部 1.2版', 2024, 1, 1);

-- 5. 溫室氣體活動數據紀錄 (示範歷史與當前盤查資料)
INSERT INTO `esg_ghg_records` (`id`, `org_id`, `record_year`, `record_month`, `scope`, `source_type`, `factor_id`, `activity_amount`, `calculated_co2e`, `status`, `user_id`, `approver_id`, `created_at`) VALUES
(1, 3, 2025, 1, 1, 'Stationary', 1, 1200.0000, 3.1368, 'approved_final', 4, 2, '2025-02-05 10:00:00'),
(2, 3, 2025, 1, 1, 'Mobile', 5, 850.0000, 1.9295, 'approved_final', 4, 2, '2025-02-05 10:15:00'),
(3, 3, 2025, 1, 2, 'Electric', 11, 250000.0000, 123.7500, 'approved_final', 4, 2, '2025-02-05 10:30:00'),
(4, 4, 2025, 1, 2, 'Electric', 11, 180000.0000, 89.1000, 'approved_final', 5, 2, '2025-02-06 11:00:00'),
(5, 3, 2025, 2, 2, 'Electric', 11, 260000.0000, 128.7000, 'approved_final', 4, 2, '2025-03-05 09:30:00'),
(6, 4, 2025, 2, 2, 'Electric', 11, 175000.0000, 86.6250, 'approved_final', 5, 2, '2025-03-06 14:00:00'),
(7, 3, 2025, 3, 1, 'Fugitive', 8, 25.0000, 52.2000, 'approved_final', 4, 2, '2025-04-02 16:00:00'),
(8, 3, 2026, 1, 1, 'Stationary', 1, 1150.0000, 3.0061, 'approved_final', 4, 2, '2026-02-05 09:00:00'),
(9, 3, 2026, 1, 2, 'Electric', 11, 240000.0000, 118.8000, 'approved_final', 4, 2, '2026-02-05 09:20:00'),
(10, 4, 2026, 1, 2, 'Electric', 11, 170000.0000, 84.1500, 'approved_final', 5, 2, '2026-02-06 10:00:00'),
(11, 3, 2026, 2, 2, 'Electric', 11, 235000.0000, 116.3250, 'approved_final', 4, 2, '2026-03-05 11:30:00'),
(12, 4, 2026, 2, 2, 'Electric', 11, 168000.0000, 83.1600, 'approved_final', 5, 2, '2026-03-06 13:45:00'),
(13, 3, 2026, 3, 1, 'Stationary', 2, 3500.0000, 7.3196, 'approved_l1', 4, 3, '2026-04-08 14:00:00'),
(14, 3, 2026, 3, 2, 'Electric', 11, 245000.0000, 121.2750, 'submitted', 4, NULL, '2026-04-10 15:30:00'),
(15, 4, 2026, 3, 3, 'ValueChain', 13, 32000.0000, 4.8000, 'draft', 5, NULL, '2026-04-12 17:00:00');

-- 6. 能源與水資源監控資料
INSERT INTO `esg_energy_water_records` (`id`, `org_id`, `record_year`, `record_month`, `record_type`, `amount`, `unit`, `cod_val`, `bod_val`, `ss_val`, `notes`, `status`, `user_id`, `approver_id`) VALUES
(1, 3, 2026, 1, 'tap_water', 4500.0000, '度', NULL, NULL, NULL, '自來水公司月結帳單', 'approved_final', 4, 2),
(2, 3, 2026, 1, 'recycled_water', 1850.0000, '度', NULL, NULL, NULL, '廠內中水回收再利用', 'approved_final', 4, 2),
(3, 3, 2026, 1, 'wastewater', 3800.0000, '度', 45.20, 18.50, 22.00, '放流水檢測完全合格(符合放流水標準)', 'approved_final', 4, 2),
(4, 4, 2026, 1, 'tap_water', 3200.0000, '度', NULL, NULL, NULL, '自來水收據', 'approved_final', 5, 2),
(5, 3, 2026, 2, 'tap_water', 4350.0000, '度', NULL, NULL, NULL, '2月份用水量', 'approved_final', 4, 2),
(6, 3, 2026, 2, 'solar_power', 12500.0000, 'kWh', NULL, NULL, NULL, '廠房屋頂太陽能自發自用發電度數', 'approved_final', 4, 2);

-- 7. 廢棄物產出紀錄
INSERT INTO `esg_waste_records` (`id`, `org_id`, `record_year`, `record_month`, `waste_category`, `waste_name`, `amount`, `unit`, `disposal_method`, `tracking_number`, `status`, `user_id`, `approver_id`) VALUES
(1, 3, 2026, 1, 'general_D', '事業一般廢棄物(生活廢棄物)', 12.5000, 'ton', 'incineration', 'EPA-202601-D001', 'approved_final', 4, 2),
(2, 3, 2026, 1, 'general_D', '廢紙箱與包裝塑料', 8.2000, 'ton', 'recycling', 'EPA-202601-D002', 'approved_final', 4, 2),
(3, 3, 2026, 1, 'hazardous_C', '廢有機溶劑 (C-0110)', 3.4000, 'ton', 'physical', 'EPA-202601-C001', 'approved_final', 4, 2),
(4, 4, 2026, 1, 'general_D', '廢晶圓邊角料(回收金屬)', 1.8000, 'ton', 'recycling', 'EPA-202601-D008', 'approved_final', 5, 2);

-- 8. 社會責任 (S) 數據
INSERT INTO `esg_social_records` (`id`, `org_id`, `record_year`, `record_quarter`, `metric_category`, `data_json`, `status`, `user_id`, `approver_id`) VALUES
(1, 2, 2025, 0, 'diversity', '{"total_employees": 520, "male_count": 310, "female_count": 210, "manager_male": 32, "manager_female": 18, "age_under_30": 135, "age_30_50": 320, "age_over_50": 65, "turnover_rate_pct": 7.5}', 'approved_final', 1, 2),
(2, 2, 2025, 0, 'salary', '{"avg_male_salary": 850000, "avg_female_salary": 820000, "ratio_female_to_male_salary": 0.965, "parental_leave_applied_male": 4, "parental_leave_applied_female": 12, "reinstated_count": 16, "reinstatement_rate_pct": 100}', 'approved_final', 1, 2),
(3, 2, 2025, 0, 'ehs', '{"total_working_hours": 1040000, "lti_count": 0, "fatalities": 0, "lost_days": 0, "fr": 0.00, "sr": 0.00, "near_miss_count": 3}', 'approved_final', 1, 2),
(4, 2, 2025, 0, 'training', '{"total_training_hours": 18200, "avg_hours_per_employee": 35.0, "esg_training_hours": 3200, "training_satisfaction_score": 4.6}', 'approved_final', 1, 2),
(5, 2, 2025, 0, 'supply_chain', '{"evaluated_suppliers_count": 85, "tier1_supplier_count": 92, "esg_audit_rate_pct": 92.4, "grade_a_count": 72, "grade_b_count": 11, "grade_c_count": 2, "volunteer_hours": 680, "donation_amount_twd": 1200000}', 'approved_final', 1, 2);

-- 9. 公司治理 (G) 數據
INSERT INTO `esg_governance_records` (`id`, `org_id`, `record_year`, `record_quarter`, `metric_category`, `data_json`, `status`, `user_id`, `approver_id`) VALUES
(1, 1, 2025, 0, 'board', '{"total_seats": 9, "independent_directors": 4, "female_directors": 3, "avg_attendance_rate_pct": 96.5, "board_meetings_count": 7, "eval_satisfaction": 4.8}', 'approved_final', 1, 2),
(2, 1, 2025, 0, 'integrity', '{"anti_corruption_signing_rate": 100, "ethical_training_coverage": 100, "conflict_of_interest_declarations": 48, "violations_count": 0}', 'approved_final', 1, 2),
(3, 1, 2025, 0, 'whistleblower', '{"cases_reported": 1, "cases_investigated": 1, "cases_closed": 1, "retaliation_cases": 0, "summary": "接獲匿名提報內部流程繁瑣建議，經查證無貪瀆違反道德情事，已結案"}', 'approved_final', 1, 2),
(4, 1, 2025, 0, 'infosec', '{"iso27001_cert_valid": 1, "phishing_drill_coverage_pct": 98.2, "phishing_click_rate_pct": 1.2, "major_incidents_count": 0, "privacy_complaints": 0}', 'approved_final', 1, 2),
(5, 1, 2025, 0, 'tcfd_risk', '{"physical_flooding_risk": {"prob": 2, "impact": 4, "mitigation": "設置防水閘門與地下蓄水池"}, "carbon_fee_transition_risk": {"prob": 5, "impact": 4, "mitigation": "引進高能效設備並簽訂綠電轉供CPPA契約"}, "opportunity_green_product": {"prob": 4, "impact": 5, "mitigation": "開發低碳半導體製程包材"}}', 'approved_final', 1, 2);

-- 10. 系統填報工作任務表
INSERT INTO `esg_workflow_tasks` (`id`, `task_title`, `org_id`, `assigned_user_id`, `period_year`, `period_month`, `module_type`, `status`, `due_date`) VALUES
(1, '新竹一廠 2026年3月份溫室氣體活動數據盤查', 3, 4, 2026, 3, 'ghg', 'submitted', '2026-04-15'),
(2, '台南二廠 2026年3月份能源與電力數據盤查', 4, 5, 2026, 3, 'ghg', 'draft', '2026-04-15'),
(3, '新竹一廠 2026年3月份水資源與放流水質填報', 3, 4, 2026, 3, 'energy', 'pending', '2026-04-15');

-- 11. 審批軌跡日誌
INSERT INTO `esg_workflow_logs` (`id`, `task_id`, `record_type`, `record_id`, `action`, `approver_id`, `comments`, `created_at`) VALUES
(1, 1, 'ghg', 13, 'approve_l1', 3, '固定源天然氣數據與欣竹瓦斯單據核對無誤，初審通過', '2026-04-08 14:00:00'),
(2, 1, 'ghg', 14, 'submit', 4, '三月份台電電力度數已輸入並附電費收據憑證，請審核', '2026-04-10 15:30:00');

-- 12. 系統設定
INSERT INTO `sys_settings` (`id`, `setting_key`, `setting_value`, `description`) VALUES
(1, 'system_name', '企業級 ESG 智慧管理與碳盤查系統', '系統對外顯示全名'),
(2, 'company_name', '台灣晶綠科技股份有限公司', '企業抬頭'),
(3, 'base_year', '2022', '溫室氣體基準年 (Base Year)'),
(4, 'target_reduction_pct', '30.0', '2030減碳目標比例 (%)'),
(5, 'annual_revenue_millions', '3850', '年度合併營收 (新台幣百萬元，計算強度用)'),
(6, 'total_employee_count', '520', '集團總員工人數 (計算人均碳排用)'),
(7, 'default_gwp_version', 'IPCC_AR5', '預設使用的全球暖化潛勢 (GWP) 版本'),
(8, 'smtp_host', 'smtp.mailtrap.io', 'SMTP 郵件主機'),
(9, 'smtp_port', '2525', 'SMTP 傳輸埠'),
(10, 'smtp_user', 'esg_system@esg-pro.local', 'SMTP 帳號'),
(11, 'smtp_pass', '', 'SMTP 密碼'),
(12, 'smtp_from_name', 'ESG-Pro 智慧永續管理平台', '發信人抬頭');

-- 13. 初步審計日誌
INSERT INTO `sys_audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `record_id`, `ip_address`, `old_values`, `new_values`, `created_at`) VALUES
(1, 1, 'admin', 'SYSTEM_INIT', 'SYSTEM', NULL, '127.0.0.1', NULL, '{"event": "ESG-Pro System Schema & Seed Data Initialized"}', CURRENT_TIMESTAMP);
