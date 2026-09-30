-- ESG-Pro System Database Schema
-- Charset: utf8mb4, Collation: utf8mb4_unicode_ci, Engine: InnoDB

CREATE DATABASE IF NOT EXISTS `esg_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `esg_db`;

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 1. 組織架構表 (sys_organizations)
CREATE TABLE IF NOT EXISTS `sys_organizations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `org_code` VARCHAR(50) NOT NULL UNIQUE COMMENT '組織代碼',
    `org_name` VARCHAR(100) NOT NULL COMMENT '組織名稱',
    `org_type` ENUM('group', 'company', 'plant', 'dept') NOT NULL DEFAULT 'dept' COMMENT '層級類型',
    `parent_id` INT UNSIGNED NULL DEFAULT NULL COMMENT '父組織ID',
    `leader_name` VARCHAR(50) NULL DEFAULT NULL COMMENT '負責人姓名',
    `contact_phone` VARCHAR(30) NULL DEFAULT NULL COMMENT '聯絡電話',
    `contact_email` VARCHAR(100) NULL DEFAULT NULL COMMENT '聯絡信箱',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT '排序',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_parent` (`parent_id`),
    CONSTRAINT `fk_org_parent` FOREIGN KEY (`parent_id`) REFERENCES `sys_organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='企業組織機構表';

-- 2. 系統角色表 (sys_roles)
CREATE TABLE IF NOT EXISTS `sys_roles` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `role_key` VARCHAR(50) NOT NULL UNIQUE COMMENT '角色識別代碼',
    `role_name` VARCHAR(50) NOT NULL COMMENT '角色中文名稱',
    `description` VARCHAR(255) NULL COMMENT '角色權責說明',
    `permissions` TEXT NULL COMMENT '功能權限集合(JSON格式)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系統角色定義表';

-- 3. 使用者帳號表 (sys_users)
CREATE TABLE IF NOT EXISTS `sys_users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE COMMENT '登入帳號',
    `password_hash` VARCHAR(255) NOT NULL COMMENT 'Bcrypt加密雜湊密碼',
    `real_name` VARCHAR(50) NOT NULL COMMENT '真實中文姓名',
    `email` VARCHAR(100) NOT NULL COMMENT '電子信箱',
    `phone` VARCHAR(30) NULL COMMENT '分機或手機',
    `org_id` INT UNSIGNED NOT NULL COMMENT '所屬組織/部門',
    `role_id` INT UNSIGNED NOT NULL COMMENT '指派角色',
    `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '帳號狀態 (1:啟用, 0:停用)',
    `failed_attempts` INT NOT NULL DEFAULT 0 COMMENT '連續密碼錯誤次數',
    `locked_until` DATETIME NULL DEFAULT NULL COMMENT '帳號鎖定截止時間',
    `last_login_at` DATETIME NULL COMMENT '最後登入時間',
    `last_login_ip` VARCHAR(45) NULL COMMENT '最後登入IP',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_org` (`org_id`),
    INDEX `idx_role` (`role_id`),
    CONSTRAINT `fk_user_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `sys_roles` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='使用者帳號表';

-- 4. 碳排放係數庫表 (esg_emission_factors)
CREATE TABLE IF NOT EXISTS `esg_emission_factors` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(50) NOT NULL COMMENT '類別 (Scope1_Fuel, Scope1_Mobile, Scope1_Fugitive, Scope2_Electricity, Scope2_Steam, Scope3)',
    `fuel_name` VARCHAR(100) NOT NULL COMMENT '排放源名稱 (如: 柴油-固定源、車用汽油、台電電力)',
    `activity_unit` VARCHAR(20) NOT NULL COMMENT '活動數據單位 (L, m3, kWh, ton, kg, km)',
    `co2_factor` DECIMAL(12,6) NOT NULL DEFAULT 0.000000 COMMENT 'CO2 排放係數 (kg CO2 / 單位)',
    `ch4_factor` DECIMAL(12,6) NOT NULL DEFAULT 0.000000 COMMENT 'CH4 排放係數 (kg CH4 / 單位)',
    `n2o_factor` DECIMAL(12,6) NOT NULL DEFAULT 0.000000 COMMENT 'N2O 排放係數 (kg N2O / 單位)',
    `total_factor_co2e` DECIMAL(12,6) NOT NULL DEFAULT 0.000000 COMMENT '綜合當量係數 (kg CO2e / 單位)',
    `source_org` VARCHAR(100) NOT NULL COMMENT '係數來源機構 (環境部/能源局/IPCC/EPA)',
    `applicable_year` INT(4) NOT NULL DEFAULT 2024 COMMENT '適用年度',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '是否有效啟用 (1:有效, 0:停用)',
    `created_by` INT UNSIGNED NULL COMMENT '建立人員',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_category` (`category`),
    INDEX `idx_year` (`applicable_year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='碳排放係數庫表';

-- 5. 溫室氣體活動數據與碳排主表 (esg_ghg_records)
CREATE TABLE IF NOT EXISTS `esg_ghg_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `org_id` INT UNSIGNED NOT NULL COMMENT '所屬廠區/部門',
    `record_year` INT(4) NOT NULL COMMENT '盤查年度',
    `record_month` TINYINT(2) NOT NULL COMMENT '盤查月份 (1-12)',
    `scope` TINYINT(1) NOT NULL COMMENT '範疇分類 (1:範疇一, 2:範疇二, 3:範疇三)',
    `source_type` VARCHAR(50) NOT NULL COMMENT '排放源細項 (Stationary, Mobile, Fugitive, Electric, Steam, ValueChain)',
    `factor_id` INT UNSIGNED NOT NULL COMMENT '使用的係數 ID',
    `activity_amount` DECIMAL(14,4) NOT NULL DEFAULT 0.0000 COMMENT '活動數據用量',
    `calculated_co2e` DECIMAL(14,4) NOT NULL DEFAULT 0.0000 COMMENT '自動試算碳排量 (公噸 tCO2e)',
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT '狀態 (draft, submitted, approved_l1, approved_final, rejected)',
    `user_id` INT UNSIGNED NOT NULL COMMENT '填報人員 ID',
    `approver_id` INT UNSIGNED NULL DEFAULT NULL COMMENT '最後審核主管 ID',
    `reject_reason` TEXT NULL COMMENT '退件原因備註',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_org_year` (`org_id`, `record_year`),
    INDEX `idx_scope` (`scope`),
    INDEX `idx_status` (`status`),
    CONSTRAINT `fk_ghg_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_ghg_factor` FOREIGN KEY (`factor_id`) REFERENCES `esg_emission_factors` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_ghg_user` FOREIGN KEY (`user_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='溫室氣體活動數據與碳排計算表';

-- 6. 能源與水資源消耗表 (esg_energy_water_records)
CREATE TABLE IF NOT EXISTS `esg_energy_water_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `org_id` INT UNSIGNED NOT NULL COMMENT '廠區/部門',
    `record_year` INT(4) NOT NULL COMMENT '年度',
    `record_month` TINYINT(2) NOT NULL COMMENT '月份 (1-12)',
    `record_type` ENUM('tap_water', 'ground_water', 'recycled_water', 'wastewater', 'solar_power', 'green_electricity') NOT NULL COMMENT '類別',
    `amount` DECIMAL(14,4) NOT NULL DEFAULT 0.0000 COMMENT '消耗或產出數量',
    `unit` VARCHAR(20) NOT NULL COMMENT '單位 (度/m3/kWh)',
    `cod_val` DECIMAL(10,2) NULL DEFAULT NULL COMMENT '放流水質 COD (mg/L)',
    `bod_val` DECIMAL(10,2) NULL DEFAULT NULL COMMENT '放流水質 BOD (mg/L)',
    `ss_val` DECIMAL(10,2) NULL DEFAULT NULL COMMENT '放流水質 SS (mg/L)',
    `notes` VARCHAR(255) NULL COMMENT '備註說明',
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT '狀態',
    `user_id` INT UNSIGNED NOT NULL COMMENT '填報人',
    `approver_id` INT UNSIGNED NULL DEFAULT NULL COMMENT '審核人',
    `reject_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ew_org_year` (`org_id`, `record_year`),
    CONSTRAINT `fk_ew_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_ew_user` FOREIGN KEY (`user_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='水資源與再生能源監控記錄表';

-- 7. 廢棄物產出與清運表 (esg_waste_records)
CREATE TABLE IF NOT EXISTS `esg_waste_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `org_id` INT UNSIGNED NOT NULL COMMENT '廠區/部門',
    `record_year` INT(4) NOT NULL COMMENT '年度',
    `record_month` TINYINT(2) NOT NULL COMMENT '月份 (1-12)',
    `waste_category` ENUM('general_D', 'hazardous_C') NOT NULL DEFAULT 'general_D' COMMENT '廢棄物類別(D類一般/C類有害)',
    `waste_name` VARCHAR(100) NOT NULL COMMENT '廢棄物名稱 (如: 生活垃圾、廢金屬、廢有機溶劑)',
    `amount` DECIMAL(12,4) NOT NULL DEFAULT 0.0000 COMMENT '數量',
    `unit` VARCHAR(20) NOT NULL DEFAULT 'ton' COMMENT '單位 (ton/kg)',
    `disposal_method` ENUM('incineration', 'landfill', 'physical', 'recycling') NOT NULL COMMENT '處置方式 (焚化/掩埋/物理/再利用)',
    `tracking_number` VARCHAR(100) NULL COMMENT '環保署清運三聯單號',
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft' COMMENT '狀態',
    `user_id` INT UNSIGNED NOT NULL COMMENT '填報人',
    `approver_id` INT UNSIGNED NULL DEFAULT NULL COMMENT '審核人',
    `reject_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_waste_org_year` (`org_id`, `record_year`),
    CONSTRAINT `fk_waste_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_waste_user` FOREIGN KEY (`user_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='廢棄物產出與處置清運表';

-- 8. 社會責任 (S) 數據表 (esg_social_records)
CREATE TABLE IF NOT EXISTS `esg_social_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `org_id` INT UNSIGNED NOT NULL COMMENT '廠區/部門',
    `record_year` INT(4) NOT NULL COMMENT '年度',
    `record_quarter` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '季度 (1-4 或 0代表全年)',
    `metric_category` VARCHAR(50) NOT NULL COMMENT '構面代碼 (diversity, salary, ehs, training, supply_chain, community)',
    `data_json` LONGTEXT NOT NULL COMMENT '量化指標JSON資料',
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `user_id` INT UNSIGNED NOT NULL,
    `approver_id` INT UNSIGNED NULL DEFAULT NULL,
    `reject_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_soc_org_year` (`org_id`, `record_year`, `metric_category`),
    CONSTRAINT `fk_soc_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_soc_user` FOREIGN KEY (`user_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='社會責任(S)數據表';

-- 9. 公司治理 (G) 數據表 (esg_governance_records)
CREATE TABLE IF NOT EXISTS `esg_governance_records` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `org_id` INT UNSIGNED NOT NULL COMMENT '公司/部門',
    `record_year` INT(4) NOT NULL COMMENT '年度',
    `record_quarter` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '季度 (1-4 或 0代表全年)',
    `metric_category` VARCHAR(50) NOT NULL COMMENT '構面代碼 (board, integrity, whistleblower, infosec, tcfd_risk)',
    `data_json` LONGTEXT NOT NULL COMMENT '治理數據JSON',
    `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
    `user_id` INT UNSIGNED NOT NULL,
    `approver_id` INT UNSIGNED NULL DEFAULT NULL,
    `reject_reason` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_gov_org_year` (`org_id`, `record_year`, `metric_category`),
    CONSTRAINT `fk_gov_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_gov_user` FOREIGN KEY (`user_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='公司治理(G)數據表';

-- 10. 填報任務與工作流狀態表 (esg_workflow_tasks)
CREATE TABLE IF NOT EXISTS `esg_workflow_tasks` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_title` VARCHAR(200) NOT NULL COMMENT '工作任務名稱',
    `org_id` INT UNSIGNED NOT NULL COMMENT '指派部門/廠區',
    `assigned_user_id` INT UNSIGNED NOT NULL COMMENT '指派填報人員',
    `period_year` INT(4) NOT NULL COMMENT '期別年度',
    `period_month` TINYINT(2) NOT NULL COMMENT '期別月份 (1-12)',
    `module_type` VARCHAR(30) NOT NULL COMMENT '模組類型 (ghg, energy, waste, social, gov)',
    `status` ENUM('pending', 'draft', 'submitted', 'approved_l1', 'approved_final', 'rejected') NOT NULL DEFAULT 'pending',
    `due_date` DATE NOT NULL COMMENT '截止填報日期',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_task_user` (`assigned_user_id`, `status`),
    CONSTRAINT `fk_task_org` FOREIGN KEY (`org_id`) REFERENCES `sys_organizations` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_task_user` FOREIGN KEY (`assigned_user_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='填報任務工作流表';

-- 11. 審批簽核歷程軌跡表 (esg_workflow_logs)
CREATE TABLE IF NOT EXISTS `esg_workflow_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `task_id` BIGINT UNSIGNED NULL COMMENT '工作任務ID',
    `record_type` VARCHAR(30) NOT NULL COMMENT '關聯模組 (ghg, energy, waste, social, gov)',
    `record_id` BIGINT UNSIGNED NOT NULL COMMENT '業務記錄ID',
    `action` VARCHAR(50) NOT NULL COMMENT '動作 (submit, approve_l1, approve_final, reject, resubmit)',
    `approver_id` INT UNSIGNED NOT NULL COMMENT '操作人ID',
    `comments` TEXT NULL COMMENT '審批批註或退回說明',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_log_record` (`record_type`, `record_id`),
    CONSTRAINT `fk_wlog_user` FOREIGN KEY (`approver_id`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='審批簽核歷程軌跡表';

-- 12. 佐證憑證與附件檔案表 (esg_attachments)
CREATE TABLE IF NOT EXISTS `esg_attachments` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `record_type` VARCHAR(30) NOT NULL COMMENT '關聯模組 (ghg, energy, waste, social, gov)',
    `record_id` BIGINT UNSIGNED NOT NULL COMMENT '關聯主鍵ID',
    `file_name` VARCHAR(255) NOT NULL COMMENT '原始檔案名稱',
    `file_path` VARCHAR(500) NOT NULL COMMENT '伺服器實體儲存相對路徑',
    `file_size` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '檔案大小(Bytes)',
    `file_ext` VARCHAR(10) NOT NULL COMMENT '副檔名 (pdf, jpg, png, xlsx, docx)',
    `uploaded_by` INT UNSIGNED NOT NULL COMMENT '上傳者ID',
    `uploaded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_att_record` (`record_type`, `record_id`),
    CONSTRAINT `fk_att_user` FOREIGN KEY (`uploaded_by`) REFERENCES `sys_users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='憑證佐證附件表';

-- 13. 系統安全操作與審計日誌表 (sys_audit_logs)
CREATE TABLE IF NOT EXISTS `sys_audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL COMMENT '操作使用者ID (未登入可為NULL)',
    `username` VARCHAR(50) NULL COMMENT '操作帳號',
    `action` VARCHAR(50) NOT NULL COMMENT '操作行為 (LOGIN, LOGOUT, CREATE, UPDATE, DELETE, EXPORT, APPROVE, REJECT)',
    `module` VARCHAR(50) NOT NULL COMMENT '所屬模組',
    `record_id` BIGINT UNSIGNED NULL COMMENT '被異動的資料ID',
    `ip_address` VARCHAR(45) NOT NULL COMMENT '操作者IP',
    `old_values` LONGTEXT NULL COMMENT '異動前內容 (JSON)',
    `new_values` LONGTEXT NULL COMMENT '異動後內容 (JSON)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系統稽核日誌表';

-- 14. 系統全域參數表 (sys_settings)
CREATE TABLE IF NOT EXISTS `sys_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE COMMENT '參數鍵',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT '參數群組',
    `setting_value` TEXT NULL COMMENT '參數值',
    `input_type` VARCHAR(20) NOT NULL DEFAULT 'text' COMMENT '後台輸入控制類型',
    `options_json` TEXT NULL COMMENT '選項 JSON',
    `description` VARCHAR(255) NULL COMMENT '參數說明',
    `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='系統參數配置表';
