<?php

namespace App\Services;

class StockRecommendationService
{
    public function calculate(int $currentStock, int $soldInPeriod): int
    {
        if ($soldInPeriod <= 0) {
            return 0;
        }

        $periodDays = max(1, (int) config('visionstock.recommendation_period_days', 30));
        $targetStock = (int) ceil(($soldInPeriod / $periodDays) * $periodDays);

        return max(0, $targetStock - $currentStock);
    }
}
