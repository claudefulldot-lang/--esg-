<?php
namespace App\Controllers;

use App\Controller;
use App\Auth;
use App\Models\GhgRecord;
use App\Models\EmissionFactor;
use App\Models\Organization;
use App\Models\SocialRecord;
use App\Models\GovernanceRecord;
use App\Helpers\AuditLogger;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller {
    public function index(): void {
        Auth::requirePermission('reports', 'read');

        $selectedYear = (int)($_GET['year'] ?? date('Y'));
        $records = GhgRecord::all(['year' => $selectedYear, 'status' => 'approved_final']);
        $scopeTotals = GhgRecord::getScopeTotals($selectedYear);

        $this->render('reports/index', [
            'pageTitle'    => '永續報告書與報表中心',
            'activeNav'    => 'reports',
            'selectedYear' => $selectedYear,
            'records'      => $records,
            'scopeTotals'  => $scopeTotals
        ]);
    }

    public function exportIso14064(): void {
        Auth::requirePermission('reports', 'export');

        $year = (int)($_GET['year'] ?? date('Y'));
        $records = GhgRecord::all(['year' => $year, 'status' => 'approved_final']);

        AuditLogger::log('EXPORT_ISO14064_EXCEL', 'REPORT', null, null, ['year' => $year, 'count' => count($records)]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("ISO14064-1 溫室氣體盤查清冊");

        // Document Title
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', "企業級溫室氣體排放量盤查清冊 ({$year} 年度 - ISO 14064-1:2018 標準)");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('1E392A');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Header Columns
        $headers = [
            '項次', '所屬廠區/部門', '盤查年月', '盤查範疇', '排放源分類', '活動項目名稱', '活動用量', '單位', '排放係數 (kgCO2e/單位)', '計算碳排量 (tCO2e)', '狀態'
        ];

        $col = 'A';
        $row = 3;
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }

        // Style header row
        $sheet->getStyle("A3:K3")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A3:K3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('2D5A27');
        $sheet->getStyle("A3:K3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data Rows
        $currentRow = 4;
        $totalTco2e = 0.0;
        foreach ($records as $index => $r) {
            $scopeText = match((int)$r['scope']) {
                1 => '範疇一 (直接排放)',
                2 => '範疇二 (能源間接)',
                3 => '範疇三 (其他間接)',
                default => '其他'
            };

            $statusText = match($r['status']) {
                'approved_final' => '已核准封存',
                'approved_l1'    => '初審通過',
                'submitted'      => '已提交待審',
                'draft'          => '草稿暫存',
                'rejected'       => '退回修正',
                default          => $r['status']
            };

            $sheet->setCellValue("A{$currentRow}", $index + 1);
            $sheet->setCellValue("B{$currentRow}", $r['org_name']);
            $sheet->setCellValue("C{$currentRow}", "{$r['record_year']}年{$r['record_month']}月");
            $sheet->setCellValue("D{$currentRow}", $scopeText);
            $sheet->setCellValue("E{$currentRow}", $r['source_type']);
            $sheet->setCellValue("F{$currentRow}", $r['fuel_name']);
            $sheet->setCellValue("G{$currentRow}", (float)$r['activity_amount']);
            $sheet->setCellValue("H{$currentRow}", $r['activity_unit']);
            $sheet->setCellValue("I{$currentRow}", (float)$r['total_factor_co2e']);
            $sheet->setCellValue("J{$currentRow}", (float)$r['calculated_co2e']);
            $sheet->setCellValue("K{$currentRow}", $statusText);

            $totalTco2e += (float)$r['calculated_co2e'];
            $currentRow++;
        }

        // Summary Row
        $sheet->mergeCells("A{$currentRow}:I{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", '合計總排放量 (tCO2e)');
        $sheet->setCellValue("J{$currentRow}", $totalTco2e);
        $sheet->getStyle("A{$currentRow}:K{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$currentRow}:K{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F5E9');

        // Auto width
        foreach (range('A', 'K') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Output to browser
        $filename = "ISO14064_GHG_Inventory_{$year}_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportGri(): void {
        Auth::requirePermission('reports', 'export');

        $year = (int)($_GET['year'] ?? date('Y'));
        AuditLogger::log('EXPORT_GRI_INDEX', 'REPORT', null, null, ['year' => $year]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("GRI Standards 2021 索引");

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', "全球報告倡議組織 (GRI Standards 2021) 永續指標對應索引表 - {$year}年度");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0D47A1');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['GRI 構面代碼', 'GRI 準則項目名稱', '對應核心管理主題', '揭露數值 / 執行成果摘要', '對應本系統資料源'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '3', $h);
            $col++;
        }
        $sheet->getStyle("A3:E3")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle("A3:E3")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1565C0');

        $griRows = [
            ['GRI 302-1', '組織內部的能源消耗量', '能源管理與節能', '電力度數監控與太陽能綠電自發自用', 'esg_energy_water_records'],
            ['GRI 303-3', '取水量與放流水排放', '水資源循環管理', '自來水、回收中水與放流水質檢測(COD/BOD/SS)', 'esg_energy_water_records'],
            ['GRI 305-1', '直接 (範疇一) 溫室氣體排放', '氣候變遷與碳管理', '固定燃燒、移動燃燒源與冷媒逸散當量試算', 'esg_ghg_records (Scope 1)'],
            ['GRI 305-2', '能源間接 (範疇二) 溫室氣體排放', '外購電力與蒸氣碳盤查', '台電電力排碳係數與外購能源碳排', 'esg_ghg_records (Scope 2)'],
            ['GRI 305-3', '其他間接 (範疇三) 溫室氣體排放', '價值鏈碳排放', '員工航空公務差旅、原物料運輸與委外處置', 'esg_ghg_records (Scope 3)'],
            ['GRI 305-4', '溫室氣體排放強度', '每單位營收與人均碳排', '碳排/營業額(tCO2e/百萬)、碳排/員工(tCO2e/人)', 'Calculator Engine'],
            ['GRI 306-3', '產生的廢棄物與清運', '廢棄物與循環經濟', '一般廢棄物(D類)、有害廢棄物(C類)清運三聯單', 'esg_waste_records'],
            ['GRI 401-1', '新進員工與員工流動率', '員工雇用與留任', '年齡層分佈統計與年度離職率監測', 'esg_social_records (diversity)'],
            ['GRI 403-9', '職業傷害與工傷統計', '職業安全衛生 EHS', '失能傷害頻率 (FR) 與失能傷害嚴重率 (SR)', 'esg_social_records (ehs)'],
            ['GRI 404-1', '平均每名員工的培訓時數', '人才培訓與發展', '人均年教育訓練時數及 ESG 專題培訓', 'esg_social_records (training)'],
            ['GRI 405-1', '管理機構與員工多元化', '多元與平等機會', '董事會女性席次比例與女性主管比例', 'esg_governance_records (board)'],
            ['GRI 205-2', '反貪腐政策與培訓宣導', '誠信經營與商業道德', '全員道德培訓覆蓋率與承諾書簽署率 100%', 'esg_governance_records (integrity)']
        ];

        $rIndex = 4;
        foreach ($griRows as $row) {
            $sheet->setCellValue("A{$rIndex}", $row[0]);
            $sheet->setCellValue("B{$rIndex}", $row[1]);
            $sheet->setCellValue("C{$rIndex}", $row[2]);
            $sheet->setCellValue("D{$rIndex}", $row[3]);
            $sheet->setCellValue("E{$rIndex}", $row[4]);
            $rIndex++;
        }

        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $filename = "GRI_Content_Index_{$year}_" . date('Ymd_His') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function printSummary(): void {
        Auth::requirePermission('reports', 'read');

        $year = (int)($_GET['year'] ?? date('Y'));
        $records = GhgRecord::all(['year' => $year, 'status' => 'approved_final']);
        $scopeTotals = GhgRecord::getScopeTotals($year);
        $monthly = GhgRecord::getMonthlyTrends($year);
        $plants = GhgRecord::getPlantEmissions($year);

        $this->render('reports/print_summary', [
            'pageTitle'   => "{$year} 年度企業 ESG 與溫室氣體盤查總結報告",
            'year'        => $year,
            'records'     => $records,
            'scopeTotals' => $scopeTotals,
            'monthly'     => $monthly,
            'plants'      => $plants
        ], 'none');
    }
}
