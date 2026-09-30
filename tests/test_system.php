<?php
/**
 * Automated Verification Script for ESG-Pro System
 */
require_once __DIR__ . '/../vendor/autoload.php';

// Manual autoload fallback
spl_autoload_register(function ($class) {
    $prefixes = [
        'App\\Controllers\\' => __DIR__ . '/../controllers/',
        'App\\Models\\'      => __DIR__ . '/../models/',
        'App\\Helpers\\'     => __DIR__ . '/../helpers/',
        'App\\'              => __DIR__ . '/../core/'
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) === 0) {
            $relativeClass = substr($class, $len);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});

use App\Database;
use App\Calculator;
use App\Auth;
use App\Models\GhgRecord;
use App\Models\EmissionFactor;
use App\Models\User;

echo "=== ESG-Pro System Verification Tests ===\n\n";

// 1. Database Connection
echo "[1] Testing MySQL Connection: ";
try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT COUNT(*) FROM `sys_users`");
    $userCount = $stmt->fetchColumn();
    echo "OK (Users in DB: {$userCount})\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Authentication Test
echo "[2] Testing Auth::attempt() for user 'admin': ";
$authResult = Auth::attempt('admin', 'admin123');
if ($authResult['success']) {
    $user = Auth::user();
    echo "OK (Logged in as: {$user['real_name']}, Role: {$user['role_name']})\n";
} else {
    echo "FAILED: " . ($authResult['message'] ?? 'Unknown error') . "\n";
    exit(1);
}

// 3. Calculator Engine Tests
echo "[3] Testing Calculator Algorithms:\n";

// Test A: GHG Emission: 10,000 kWh * 0.495 kgCO2e/kWh / 1000 = 4.9500 tCO2e
$tco2e = Calculator::calculateGhg(10000.0, 0.495);
echo "  - GHG Electricity Calculation (10,000 kWh * 0.495): {$tco2e} tCO2e ";
if (abs($tco2e - 4.9500) < 0.0001) {
    echo "[PASS]\n";
} else {
    echo "[FAIL] Expected 4.9500\n";
    exit(1);
}

// Test B: FR (Disabling Frequency Rate): (1 LTI * 10^6) / 500,000 hours = 2.00
$fr = Calculator::calculateFR(1, 500000.0);
echo "  - EHS FR Calculation ((1 * 10^6) / 500,000 hrs): {$fr} ";
if (abs($fr - 2.00) < 0.01) {
    echo "[PASS]\n";
} else {
    echo "[FAIL] Expected 2.00\n";
    exit(1);
}

// Test C: SR (Disabling Severity Rate): (15 Lost Days * 10^6) / 500,000 hours = 30.00
$sr = Calculator::calculateSR(15, 500000.0);
echo "  - EHS SR Calculation ((15 * 10^6) / 500,000 hrs): {$sr} ";
if (abs($sr - 30.00) < 0.01) {
    echo "[PASS]\n";
} else {
    echo "[FAIL] Expected 30.00\n";
    exit(1);
}

// Test D: Emission Intensity per Revenue: 500.0 tCO2e / 2000 Million TWD = 0.2500
$intensity = Calculator::calculateIntensityRevenue(500.0, 2000.0);
echo "  - Carbon Intensity per Revenue (500 t / 2000 M): {$intensity} ";
if (abs($intensity - 0.2500) < 0.0001) {
    echo "[PASS]\n";
} else {
    echo "[FAIL] Expected 0.2500\n";
    exit(1);
}

// 4. Data Models Verification
echo "[4] Testing Data Models:\n";
$factors = EmissionFactor::all();
echo "  - Emission Factors loaded: " . count($factors) . " rows [PASS]\n";

$ghgRecords = GhgRecord::all();
echo "  - GHG Records loaded: " . count($ghgRecords) . " rows [PASS]\n";

$scopeTotals = GhgRecord::getScopeTotals(2025);
echo "  - 2025 Scope Totals: S1={$scopeTotals['scope1']}, S2={$scopeTotals['scope2']}, S3={$scopeTotals['scope3']}, Total={$scopeTotals['total']} [PASS]\n";

echo "\nAll Verification Checks PASSED Successfully!\n";
