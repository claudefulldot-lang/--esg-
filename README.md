# ESG-Pro 企業級智慧管理與碳盤查系統
> **Enterprise ESG Sustainability Management & GHG Inventory System**  
> 符合國際標準：**ISO 14064-1:2018** / **GHG Protocol** / **GRI Standards 2021** / **TCFD 氣候相關財務揭露**

---

## 📖 系統簡介 (System Overview)

**ESG-Pro** 是一套專為大型企業集團、跨國製造廠區與公發/上市櫃企業量身打造的雲端企業永續智慧管理與溫室氣體碳盤查系統。系統涵蓋 ESG (環境、社會、公司治理) 三大維度之完整指標填報、動態係數庫運算、多階簽核工作流、安全操作稽核軌跡，並具備即時視覺化決策戰情室與一鍵導出標準格式之 ISO 14064 及 GRI Excel 報告書。

本系統全面導入 **RWD (Responsive Web Design，自適應響應式設計)**，支援電腦桌機、iPad/平板以及智慧型手機（具備行動端抽屜式導航與觸控橫向滾動表格），讓各廠區第一線人員與主管隨時隨地完成填報與審批。

---

## 🛠️ 技術架構與套件規格 (Technology Stack)

- **後端架構**：PHP 8.0+ / 8.2 原生輕量級物件導向 MVC 架構 (Model-View-Controller)
- **資料庫**：MySQL 8.0 / MariaDB 10.4+（全面採用 `utf8mb4_unicode_ci` 字元編碼）
- **前端介面**：
  - **Bootstrap 5.3.2**：現代化 UI 與自適應網格排版
  - **Chart.js 4.4.1**：流體縮放動態圖表（月度堆疊柱狀圖、範疇環形圖、廠區碳排長條圖）
  - **DataTables 1.13.7**：繁體中文分頁、即時搜尋與排序
  - **FontAwesome 6.4.2**：向量圖示庫
- **報表匯出引擎**：`phpoffice/phpspreadsheet` (實體產出專業樣式 Excel 清冊)
- **資訊安全防護**：
  - **CSRF Token** 全面跨站請求偽造防禦
  - **Bcrypt** 密碼加密雜湊儲存
  - **XSS** 輸出轉義與 HTML 注入過濾
  - **SQL Injection** 全站 PDO Prepared Statements 參數化查詢
  - **暴力破解防護**：連續 5 次密碼錯誤自動鎖定帳號 15 分鐘
  - **完整安全審計日誌** (Audit Log)：留存使用者 IP 與前後數值 JSON 差異

---

## 📦 六大核心模組 (Core Modules)

### 1. 系統管理與權限模組 (ADM & RBAC)
- **多層級組織樹架構** (`/admin/orgs`)：集團 $\rightarrow$ 子公司 $\rightarrow$ 廠區 $\rightarrow$ 部門父子關聯。
- **使用者與權限矩陣** (`/admin/users`)：細緻角色授權（Super Admin, CSO, Dept Approver, Submitter, Auditor）。
- **安全操作審計日誌** (`/admin/logs`)：記錄登入、填報、初審、複核與匯出等所有敏感行為。
- **全域延展性參數控制台** (`/admin/settings`)：最高管理員可即時調整基準年、2030 減碳承諾比例、內部碳費單價、SLA 審核天數等 27 項全域參數。

### 2. 環境保護管理模組 (E - Environmental)
- **溫室氣體碳盤查 (GHG Inventory)** (`/ghg`)：
  - **範疇一 (Scope 1)**：固定燃燒源 (鍋爐/柴油/天然氣)、移動燃燒源 (公務汽機車/堆高機)、逸散源 (空調冷媒 R-134a/R-410A、化糞池、滅火器)。
  - **範疇二 (Scope 2)**：外購一般電力 (台電最新公告排碳係數 0.495 kgCO₂e/kWh)、管道蒸氣。
  - **範疇三 (Scope 3)**：原物料運輸、員工商務差旅、委外廢棄物掩埋。
- **動態排放係數庫** (`/factors`)：內建環境部 1.2 版、經濟部能源局與 IPCC AR5/AR6 評估數據。
- **智慧活動數據填報** (`/ghg/create`)：選擇排放源自動下拉活動單位，並即時運算排碳當量 ($tCO_2e$)，支援上傳發票與電費單憑證。
- **水資源與放流水質** (`/environment/water`)：追蹤用水度數、中水回收率、放流水質 (COD, BOD, SS) 與太陽能綠電發電量。
- **事業廢棄物處置** (`/environment/waste`)：記錄一般/有害事業廢棄物清運聯單號與處置方式（焚化/掩埋/循環再利用）。

### 3. 社會責任管理模組 (S - Social)
- **人力資本與多元化 (GRI 401/405)**：性別比例、年齡層分佈、女性主管席次與年度員工離職率。
- **男女薪酬平權與育嬰留停 (GRI 401-3, 405-2)**：平均薪資比例、留停申請與期滿 100% 復職率追蹤。
- **職業安全衛生 (EHS - GRI 403-9)**：工作場所總經歷工時、失能次數與損失日數，系統自動計算：
  - **失能傷害頻率 (FR)**：$(LTI \times 10^6) \div \text{總經歷工時}$
  - **失能傷害嚴重率 (SR)**：$(\text{損失日數} \times 10^6) \div \text{總經歷工時}$
- **員工發展與永續供應鏈 (GRI 404, 308, 414)**：人均培訓時數、供應商 ESG 評鑑率與公益捐贈。

### 4. 公司治理管理模組 (G - Governance)
- **董事會結構與運作**：獨立董事席次佔比（自動標籤符合 $\ge 1/3$ 法規標準）、董事平均出席率。
- **商業道德與誠信經營 (GRI 205)**：誠信簽署率、道德培訓覆蓋率與利益衝突申報。
- **吹哨者獨立檢舉機制**：案件受理解密、調查狀態與零報復防護。
- **資訊安全與隱私**：ISO 27001 證書效期、釣魚郵件演練點擊率與零重大事件宣告。
- **TCFD 氣候風險評估矩陣**：實體風險（暴雨淹水）與轉型風險（碳費與 CBAM）之機率、衝擊與減緩對策。

### 5. 多級審批工作流狀態機 (Workflow Engine)
- **嚴謹狀態流轉**：
  $$\text{Draft (草稿暫存)} \xrightarrow{\text{送審}} \text{Submitted (待部門初審)} \xrightarrow{\text{初審通過}} \text{Approved L1 (待委員會複核)} \xrightarrow{\text{最終核准}} \text{Approved Final (鎖定防篡改)}$$
- **退回修正機制**：審核主管或 ESG 委員會若發現數據異常，強制輸入退回原因退件，自動變更為 `Rejected` 並保留完整歷程軌跡。
- **單據憑證查核**：線上檢視發票、電費單等佐證憑證，確保盤查數據真實合規。

### 6. ESG 戰情室與報表匯出 (Dashboard & Reporting)
- **視覺化戰情室** (`/`)：三大範疇比例環形圖、月度碳排趨勢堆疊圖、各廠區貢獻橫條圖、營收碳排強度 ($tCO_2e/\text{百萬元}$) 與人均碳排強度 ($tCO_2e/\text{人}$)、2030 減碳目標進度超前警報。
- **國際報表匯出中心** (`/reports`)：
  - **ISO 14064-1 溫室氣體盤查清冊 Excel**
  - **GRI Content Index 2021 永續準則索引表 Excel**
  - **ESG 總結報告列印底稿** (`/reports/print_summary`)：具備製表人、初審主管與 CSO 簽核欄位，支援「另存為 PDF」。

---

## 🚀 快速安裝與本機啟動 (Getting Started)

### 1. 環境需求
- Web 伺服器：Apache 2.4+ (啟用 `mod_rewrite`)
- PHP：8.0 ~ 8.2+（需啟用 `pdo_mysql`, `mbstring`, `openssl`, `gd`, `zip` 擴展）
- 資料庫：MySQL 8.0+ 或 MariaDB 10.4+

### 2. 部署路徑
將本專案目錄放置於 Web 根目錄下（例如 XAMPP 環境）：
```bash
c:\xampp\htdocs\esg\
```

### 3. 資料庫建立與匯入
建立名為 `esg_db` 的資料庫（編碼請務必選擇 `utf8mb4`）：
```sql
CREATE DATABASE IF NOT EXISTS `esg_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
依序匯入 `sql/` 目錄下的 SQL 腳本：
1. `sql/schema.sql` (系統 14 張資料表結構)
2. `sql/seed_data.sql` (預設角色、組織、係數庫、示範活動數據)
3. `sql/settings_seed.sql` (全域可延展性參數表)

### 4. 資料庫連線配置
如資料庫帳號或密碼不同，請修改 `config/database.php`：
```php
return [
    'host' => '127.0.0.1',
    'port' => 3306,
    'dbname' => 'esg_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];
```

### 5. 瀏覽器開啟系統
- **系統網址**：[http://localhost/esg/](http://localhost/esg/)
- **登入頁面**：[http://localhost/esg/login](http://localhost/esg/login)

---

## 👥 示範測試帳號 (預設密碼均為 `admin123`)

登入畫面已內建「**快速填入按鈕**」，可一鍵自動填入帳號密碼進行體驗：

| 角色名稱 | 登入帳號 | 預設密碼 | 職責與操作權限 |
| :--- | :--- | :--- | :--- |
| **系統最高管理員** | `admin` | `admin123` | 全系統管理、組織樹維護、使用者權限矩陣、安全審計日誌、全域延展性參數調整 |
| **永續長 / ESG 委員** | `cso_chen` | `admin123` | 全集團戰情室監控、跨部門數據複核、數據最終封存防篡改、匯出 ISO 14064 與 GRI 報告書 |
| **部門審核主管** | `dept_approver` | `admin123` | 部門活動數據初審（核准通過或輸入原因退件）、查核佐證發票與單據 |
| **新竹廠填報人員** | `submitter_hc` | `admin123` | 活動數據填報、即時碳排運算、上傳佐證憑證單據、暫存草稿或送審 |
| **台南廠填報人員** | `submitter_tn` | `admin123` | 台南廠活動數據填報與憑證上傳 |
| **第三方查證稽核員** | `auditor_wang` | `admin123` | 唯讀查詢權限、安全審計軌跡查核、計算公式與抽核佐證憑證 |

---

## 🧪 自動化測試與驗證 (Verification Tests)

本系統內建完整之端對端業務場景測試與 RWD 自適應測試腳本：

```bash
# 執行 6 大角色業務場景端對端整合測試 (26 項業務場景測試)
python tests/e2e_scenario_test.py

# 執行全系統 RWD 自適應響應式設計測試 (17 項全模組斷點與 Viewport 測試)
python scratch/test_rwd.py
```

---

## 📄 授權條款 (License)

Copyright &copy; 2026 ESG-Pro System. All rights reserved.
