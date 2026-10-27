<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\StockAnalysisService;
use App\Services\StockRecommendationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalysisHistoryController extends Controller
{
    public function store(
        Request $request,
        StockAnalysisService $analysis,
        StockRecommendationService $recommendations,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $products = $analysis->forUser($user, $recommendations);
        if ($products->isEmpty()) {
            return redirect()
                ->route('stok.index')
                ->with('analysis_history_error', 'Belum ada data stok untuk disimpan sebagai riwayat analisis.');
        }

        $periodDays = (int) config('visionstock.recommendation_period_days', 30);
        $analyzedAt = now();

        try {
            DB::transaction(function () use ($user, $products, $periodDays, $analyzedAt): void {
                $history = $user->analysisHistories()->create([
                    'analyzed_at' => $analyzedAt,
                    'period_days' => $periodDays,
                    'coverage_days' => $periodDays,
                ]);

                foreach ($products as $product) {
                    $history->items()->create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'stock' => $product->stock,
                        'recommended_stock' => $product->recommended_stock,
                    ]);
                }
            });
        } catch (QueryException $exception) {
            Log::error('Riwayat analisis gagal disimpan.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return redirect()
                ->route('analisis.index')
                ->with('analysis_history_error', 'Riwayat analisis gagal disimpan. Coba lagi.');
        }

        return redirect()
            ->route('analisis.index')
            ->with('analysis_history_success', 'Riwayat analisis berhasil disimpan');
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return view('pengaturan.riwayat-analisis.index', [
            'title' => 'Pengaturan',
            'histories' => $user->analysisHistories()
                ->withCount('items')
                ->orderByDesc('analyzed_at')
                ->orderByDesc('id')
                ->paginate(12),
        ]);
    }

    public function show(Request $request, int $history): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $history = $user->analysisHistories()
            ->with('items')
            ->findOrFail($history);

        return view('pengaturan.riwayat-analisis.show', [
            'title' => 'Pengaturan',
            'history' => $history,
        ]);
    }
}
