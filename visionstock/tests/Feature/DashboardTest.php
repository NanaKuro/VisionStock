<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_zero_metrics_without_inventory_data(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('metrics', [
                'transaction_records' => 0,
                'products' => 0,
                'units_in_stock' => 0,
                'units_sold' => 0,
            ]);
    }

    public function test_dashboard_metrics_only_include_the_authenticated_users_data(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownerProduct = Product::create([
            'user_id' => $owner->id,
            'name' => 'Beras',
            'stock' => 12,
        ]);

        Product::create([
            'user_id' => $owner->id,
            'name' => 'Susu',
            'stock' => 8,
        ]);

        $otherProduct = Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Telur',
            'stock' => 100,
        ]);

        Transaction::create([
            'user_id' => $owner->id,
            'product_id' => $ownerProduct->id,
            'quantity_sold' => 3,
            'sold_at' => now(),
        ]);

        Transaction::create([
            'user_id' => $owner->id,
            'product_id' => $ownerProduct->id,
            'quantity_sold' => 5,
            'sold_at' => now(),
        ]);

        Transaction::create([
            'user_id' => $otherUser->id,
            'product_id' => $otherProduct->id,
            'quantity_sold' => 20,
            'sold_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('metrics', [
                'transaction_records' => 2,
                'products' => 2,
                'units_in_stock' => 20,
                'units_sold' => 8,
            ]);
    }
}
