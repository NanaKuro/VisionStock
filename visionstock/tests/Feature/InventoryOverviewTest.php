<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_analysis_shows_the_stock_empty_state_without_products(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('analisis.index'))
            ->assertOk()
            ->assertSee('Data stok belum ada nih')
            ->assertSee('Ayo lakukan input stok atau impor dari pencatatan sebelumnya.');
    }

    public function test_analysis_shows_recommendations_for_the_authenticated_users_products(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 8,
        ]);
        $otherProduct = Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Produk Rahasia',
            'stock' => 4,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity_sold' => 12,
            'sold_at' => now()->subDays(5),
        ]);
        Transaction::create([
            'user_id' => $otherUser->id,
            'product_id' => $otherProduct->id,
            'quantity_sold' => 50,
            'sold_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('analisis.index'))
            ->assertOk()
            ->assertSee('Beras')
            ->assertSee('4')
            ->assertDontSee('Produk Rahasia');
    }

    public function test_transactions_show_the_requested_empty_state_without_records(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('transaksi.index'))
            ->assertOk()
            ->assertSee('Riwayat transaksi belum ada nih')
            ->assertSee('Ayo lakukan input transaksi atau impor transaksi sebelumnya');
    }

    public function test_transactions_only_show_records_owned_by_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Tepung',
            'stock' => 10,
        ]);
        $otherProduct = Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Produk Rahasia',
            'stock' => 10,
        ]);

        Transaction::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity_sold' => 2,
            'sold_at' => now(),
        ]);
        Transaction::create([
            'user_id' => $otherUser->id,
            'product_id' => $otherProduct->id,
            'quantity_sold' => 7,
            'sold_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('transaksi.index'))
            ->assertOk()
            ->assertSee('Tepung')
            ->assertDontSee('Produk Rahasia');
    }
}
