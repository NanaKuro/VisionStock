<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['name_asc', 'name_desc', 'stock_desc', 'stock_asc'])],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('product_categories', 'id')->where('user_id', $user->id),
            ],
        ], [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 255 karakter.',
            'sort.in' => 'Pilihan pengurutan tidak valid.',
            'category_id.integer' => 'Jenis produk yang dipilih tidak valid.',
            'category_id.exists' => 'Jenis produk yang dipilih tidak tersedia.',
        ]);

        $sort = $filters['sort'] ?? 'name_asc';
        [$sortColumn, $sortDirection] = match ($sort) {
            'name_desc' => ['name', 'desc'],
            'stock_desc' => ['stock', 'desc'],
            'stock_asc' => ['stock', 'asc'],
            default => ['name', 'asc'],
        };

        $products = $user->products()
            ->with('category')
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->where('name', 'like', "%{$search}%"),
            )
            ->when(
                $filters['category_id'] ?? null,
                fn ($query, $categoryId) => $query->where('category_id', $categoryId),
            )
            ->orderBy($sortColumn, $sortDirection)
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('stok.index', [
            'products' => $products,
            'categories' => $user->productCategories()->orderBy('name')->get(),
            'categoryNames' => ProductCategory::PREDEFINED_NAMES,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'sort' => $sort,
                'category_id' => $filters['category_id'] ?? '',
            ],
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $data = $request->validated();

        if (isset($data['image'])) {
            $data['image_path'] = $data['image']->store(
                "users/{$user->id}/products",
                'public',
            );
        }

        $categoryName = trim((string) ($data['category_name'] ?? ''));
        unset($data['category_name']);

        if ($categoryName !== '') {
            $data['category_id'] = $user->productCategories()
                ->firstOrCreate(['name' => $categoryName])
                ->id;
        }

        unset($data['image']);
        $user->products()->create($data);

        $destination = $request->routeIs('stok.import.manual')
            ? 'stok.import'
            : 'stok.index';

        return redirect()->route($destination)
            ->with('status', 'Produk berhasil ditambahkan.');
    }

    public function update(ProductRequest $request, int $product): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $product = $user->products()->findOrFail($product);
        $data = $request->validated();
        $previousImage = $product->image_path;

        if (isset($data['image'])) {
            $data['image_path'] = $data['image']->store(
                "users/{$user->id}/products",
                'public',
            );
        }

        $categoryName = trim((string) ($data['category_name'] ?? ''));
        $hasCategoryName = array_key_exists('category_name', $data);
        unset($data['category_name']);

        if ($categoryName !== '') {
            $data['category_id'] = $user->productCategories()
                ->firstOrCreate(['name' => $categoryName])
                ->id;
        } elseif ($hasCategoryName) {
            $data['category_id'] = null;
        }

        unset($data['image']);
        $product->update($data);

        if (isset($data['image_path']) && $previousImage !== null) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()
            ->route('stok.index')
            ->with('status', 'Stok berhasil diperbarui.');
    }
}
