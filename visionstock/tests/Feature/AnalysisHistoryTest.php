<?php

namespace Tests\Feature;

use App\Models\AnalysisHistory;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysisHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_page_offers_save_history_with_confirmation(): void
    {
        $user = User::factory()->create();
        Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 5,
        ]);

        $this->actingAs($user)
            ->get(route('analisis.index'))
            ->assertOk()
            ->assertSee('Simpan Riwayat')
            ->assertSee('Ingin menyimpan analisis ini ke riwayat?')
            ->assertSee(route('analisis.riwayat.store'));
    }

    public function test_saving_analysis_stores_snapshot_and_history_details_are_immutable(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 5,
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity_sold' => 20,
            'sold_at' => now()->subDays(5),
        ]);
        $this->travelTo(now()->setDate(2026, 10, 27)->setTime(16, 0));

        $this->actingAs($user)
            ->post(route('analisis.riwayat.store'))
            ->assertRedirect(route('analisis.index'))
            ->assertSessionHas('analysis_history_success', 'Riwayat analisis berhasil disimpan');

        $history = AnalysisHistory::with('items')->firstOrFail();
        $this->assertSame($user->id, $history->user_id);
        $this->assertSame(30, $history->period_days);
        $this->assertSame(30, $history->coverage_days);
        $this->assertCount(1, $history->items);
        $this->assertSame('Beras', $history->items[0]->product_name);
        $this->assertSame(5, $history->items[0]->stock);
        $this->assertSame(15, $history->items[0]->recommended_stock);

        $product->update(['name' => 'Beras Baru', 'stock' => 2]);

        $this->get(route('pengaturan.riwayat-analisis.index'))
            ->assertOk()
            ->assertSee('27/10/2026 16:00')
            ->assertSee('1')
            ->assertSee(route('pengaturan.riwayat-analisis.show', $history));

        $this->get(route('pengaturan.riwayat-analisis.show', $history))
            ->assertOk()
            ->assertSee('Detail Riwayat Analisis')
            ->assertSee('Beras')
            ->assertSee('>5<', false)
            ->assertSee('>15<', false)
            ->assertDontSee('Beras Baru');
    }

    public function test_analysis_history_is_scoped_to_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Produk Privat',
            'stock' => 10,
        ]);
        $history = $otherUser->analysisHistories()->create([
            'analyzed_at' => now(),
            'period_days' => 30,
            'coverage_days' => 30,
        ]);
        $history->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock' => 10,
            'recommended_stock' => 0,
        ]);

        $this->actingAs($owner)
            ->get(route('pengaturan.riwayat-analisis.index'))
            ->assertOk()
            ->assertSee('Riwayat analisis belum ada')
            ->assertDontSee('Produk Privat');

        $this->get(route('pengaturan.riwayat-analisis.show', $history))
            ->assertNotFound();
    }

    public function test_analysis_history_handles_no_products_without_saving_a_partial_history(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('analisis.riwayat.store'))
            ->assertRedirect(route('stok.index'))
            ->assertSessionHas('analysis_history_error');

        $this->assertDatabaseCount('analysis_histories', 0);
        $this->assertDatabaseCount('analysis_history_items', 0);
    }

    public function test_deleted_product_keeps_its_snapshot_item(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Produk Lama',
            'stock' => 4,
        ]);
        $history = $user->analysisHistories()->create([
            'analyzed_at' => now(),
            'period_days' => 30,
            'coverage_days' => 30,
        ]);
        $item = $history->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock' => $product->stock,
            'recommended_stock' => 0,
        ]);

        $product->delete();

        $this->assertNull($item->fresh()->product_id);
        $this->assertSame('Produk Lama', $item->fresh()->product_name);
    }
}
