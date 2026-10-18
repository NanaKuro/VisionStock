<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_page_lists_only_products_owned_by_the_authenticated_user(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        Product::create([
            'user_id' => $owner->id,
            'name' => 'Beras',
            'stock' => 12,
        ]);

        Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Produk Rahasia',
            'stock' => 99,
        ]);

        $this->actingAs($owner)
            ->get(route('stok.index'))
            ->assertOk()
            ->assertSee('Beras')
            ->assertDontSee('Produk Rahasia');
    }

    public function test_empty_stock_page_shows_manual_input_action(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('stok.index'))
            ->assertOk()
            ->assertSee('Data stok belum ada')
            ->assertSee('Input Manual');
    }

    public function test_user_can_add_a_product_with_a_valid_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $category = ProductCategory::create([
            'user_id' => $user->id,
            'name' => 'Sembako',
        ]);

        $this->actingAs($user)
            ->post(route('stok.store'), [
                'name' => 'Beras',
                'description' => 'Beras kualitas premium',
                'stock' => 15,
                'category_id' => $category->id,
                'image' => UploadedFile::fake()->image('beras.jpg'),
            ])
            ->assertRedirect(route('stok.index'))
            ->assertSessionHas('status', 'Produk berhasil ditambahkan.');

        $product = Product::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Beras', $product->name);
        $this->assertSame(15, $product->stock);
        $this->assertSame($category->id, $product->category_id);
        $this->assertNotNull($product->image_path);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_user_can_update_stock_and_product_details(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 10,
        ]);

        $this->actingAs($user)
            ->put(route('stok.update', $product->id), [
                'product_form' => '1',
                'product_id' => $product->id,
                'name' => 'Beras Premium',
                'description' => 'Kemasan baru',
                'stock' => 18,
            ])
            ->assertRedirect(route('stok.index'))
            ->assertSessionHas('status', 'Stok berhasil diperbarui.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $user->id,
            'name' => 'Beras Premium',
            'description' => 'Kemasan baru',
            'stock' => 18,
        ]);
    }

    public function test_user_cannot_update_another_users_product(): void
    {
        $otherUser = User::factory()->create();
        $product = Product::create([
            'user_id' => $otherUser->id,
            'name' => 'Milik Pengguna Lain',
            'stock' => 5,
        ]);

        $this->actingAs(User::factory()->create())
            ->put(route('stok.update', $product->id), [
                'name' => 'Diubah',
                'stock' => 0,
            ])
            ->assertNotFound();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Milik Pengguna Lain',
            'stock' => 5,
        ]);
    }

    public function test_product_validation_rejects_negative_stock_and_duplicate_names(): void
    {
        $user = User::factory()->create();
        Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 5,
        ]);

        $this->actingAs($user)
            ->from(route('stok.index'))
            ->post(route('stok.store'), [
                'product_form' => '1',
                'name' => 'Beras',
                'stock' => -1,
            ])
            ->assertRedirect(route('stok.index'))
            ->assertSessionHasErrors([
                'name' => 'Nama produk tersebut sudah digunakan.',
                'stock' => 'Stok produk tidak boleh negatif.',
            ]);

        $this->assertDatabaseCount('products', 1);
    }

    public function test_stock_page_filters_products_by_search_and_sort(): void
    {
        $user = User::factory()->create();

        Product::create([
            'user_id' => $user->id,
            'name' => 'Susu',
            'stock' => 4,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 20,
        ]);

        $this->actingAs($user)
            ->get(route('stok.index', ['search' => 'Beras', 'sort' => 'stock_desc']))
            ->assertOk()
            ->assertSee('Beras')
            ->assertDontSee('Susu');
    }

    public function test_stock_page_orders_products_by_stock_quantity(): void
    {
        $user = User::factory()->create();

        Product::create([
            'user_id' => $user->id,
            'name' => 'Beras',
            'stock' => 4,
        ]);

        Product::create([
            'user_id' => $user->id,
            'name' => 'Susu',
            'stock' => 20,
        ]);

        $this->actingAs($user)
            ->get(route('stok.index', ['sort' => 'stock_desc']))
            ->assertOk()
            ->assertSeeInOrder(['Susu', 'Beras']);
    }
}
