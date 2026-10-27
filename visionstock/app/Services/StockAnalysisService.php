<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class StockAnalysisService
{
    /**
     * @return Collection<int, Product>
     */
    public function forUser(User $user, StockRecommendationService $recommendations): Collection
    {
        $periodDays = (int) config('visionstock.recommendation_period_days', 30);
        $periodStart = now()->subDays($periodDays);

        return $user->products()
            ->withSum([
                'transactions as units_sold_in_period' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where('sold_at', '>=', $periodStart),
            ], 'quantity_sold')
            ->orderBy('name')
            ->get()
            ->map(function (Product $product) use ($recommendations): Product {
                $soldInPeriod = (int) ($product->units_sold_in_period ?? 0);
                $product->recommended_stock = $recommendations->calculate($product->stock, $soldInPeriod);
                $product->has_insufficient_history = $soldInPeriod === 0;

                return $product;
            });
    }
}
