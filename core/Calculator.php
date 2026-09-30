<?php
namespace App;

class Calculator {
    /**
     * Calculate carbon emissions in tCO2e
     * Formula: (activity_amount * factor_kg_co2e) / 1000
     */
    public static function calculateGhg(float $activityAmount, float $factorKgCo2e): float {
        if ($activityAmount <= 0 || $factorKgCo2e <= 0) {
            return 0.0000;
        }
        $tco2e = ($activityAmount * $factorKgCo2e) / 1000.0;
        return round($tco2e, 4);
    }

    /**
     * Calculate Disabling Injury Frequency Rate (FR)
     * Formula: (LTI Count * 10^6) / Total Working Hours
     */
    public static function calculateFR(int $ltiCount, float $totalHours): float {
        if ($totalHours <= 0) {
            return 0.00;
        }
        return round(($ltiCount * 1000000.0) / $totalHours, 2);
    }

    /**
     * Calculate Disabling Injury Severity Rate (SR)
     * Formula: (Lost Days * 10^6) / Total Working Hours
     */
    public static function calculateSR(int $lostDays, float $totalHours): float {
        if ($totalHours <= 0) {
            return 0.00;
        }
        return round(($lostDays * 1000000.0) / $totalHours, 2);
    }

    /**
     * Calculate Carbon Emission Intensity per Revenue (tCO2e / Million TWD)
     */
    public static function calculateIntensityRevenue(float $totalTco2e, float $revenueMillions): float {
        if ($revenueMillions <= 0) {
            return 0.0000;
        }
        return round($totalTco2e / $revenueMillions, 4);
    }

    /**
     * Calculate Carbon Emission Intensity per Capita (tCO2e / Person)
     */
    public static function calculateIntensityCapita(float $totalTco2e, int $employeeCount): float {
        if ($employeeCount <= 0) {
            return 0.0000;
        }
        return round($totalTco2e / $employeeCount, 4);
    }

    /**
     * Calculate Reduction Target Progress
     */
    public static function calculateReductionProgress(float $baseEmission, float $currentEmission, float $targetReductionPct): array {
        if ($baseEmission <= 0) {
            return [
                'target_emission' => 0.0,
                'reduction_achieved' => 0.0,
                'reduction_achieved_pct' => 0.0,
                'target_gap' => 0.0,
                'is_on_track' => true
            ];
        }

        $targetEmission = $baseEmission * (1 - ($targetReductionPct / 100.0));
        $reductionAchieved = $baseEmission - $currentEmission;
        $reductionAchievedPct = ($reductionAchieved / $baseEmission) * 100.0;
        $gap = $currentEmission - $targetEmission;

        return [
            'target_emission'        => round($targetEmission, 2),
            'reduction_achieved'     => round($reductionAchieved, 2),
            'reduction_achieved_pct' => round($reductionAchievedPct, 2),
            'target_gap'             => round($gap, 2),
            'is_on_track'            => $currentEmission <= $targetEmission
        ];
    }
}
