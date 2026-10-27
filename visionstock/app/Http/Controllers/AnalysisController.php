<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StockAnalysisService;
use App\Services\StockRecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function index(
        Request $request,
        StockAnalysisService $analysis,
        StockRecommendationService $recommendations,
    ): View {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $products = $analysis->forUser($user, $recommendations);

        return view('analisis.index', [
            'title' => 'Analisis Stok',
            'products' => $products,
            'periodDays' => (int) config('visionstock.recommendation_period_days', 30),
        ]);
    }
}
