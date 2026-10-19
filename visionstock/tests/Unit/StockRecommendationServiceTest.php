<?php

namespace Tests\Unit;

use App\Services\StockRecommendationService;
use Tests\TestCase;

class StockRecommendationServiceTest extends TestCase
{
    public function test_recommends_enough_stock_to_cover_recent_sales(): void
    {
        $service = new StockRecommendationService;

        $this->assertSame(25, $service->calculate(20, 45));
    }

    public function test_does_not_recommend_stock_when_current_stock_exceeds_sales(): void
    {
        $service = new StockRecommendationService;

        $this->assertSame(0, $service->calculate(50, 12));
    }

    public function test_products_without_sales_in_the_period_have_zero_recommendation(): void
    {
        $service = new StockRecommendationService;

        $this->assertSame(0, $service->calculate(0, 0));
    }
}
