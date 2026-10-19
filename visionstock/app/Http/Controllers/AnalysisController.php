<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StockRecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function index(Request $request, StockRecommendationService $recommendations): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $periodDays = (int) config('visionstock.recommendation_period_days', 30);
        $periodStart = now()->subDays($periodDays);
        $products = $user->products()
            ->withSum([
                'transactions as units_sold_in_period' => fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where('sold_at', '>=', $periodStart),
            ], 'quantity_sold')
            ->orderBy('name')
            ->get()
            ->map(function ($product) use ($recommendations) {
                $soldInPeriod = (int) ($product->units_sold_in_period ?? 0);
                $product->recommended_stock = $recommendations->calculate($product->stock, $soldInPeriod);
                $product->has_insufficient_history = $soldInPeriod === 0;

                return $product;
            });

        return view('analisis.index', [
            'title' => 'Analisis Stok',
            'products' => $products,
        ]);
    }
}
