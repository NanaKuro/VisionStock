<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['date_desc', 'date_asc', 'quantity_desc', 'quantity_asc'])],
        ], [
            'search.string' => 'Pencarian harus berupa teks.',
            'search.max' => 'Pencarian maksimal 255 karakter.',
            'sort.in' => 'Pilihan pengurutan tidak valid.',
        ]);

        $sort = $filters['sort'] ?? 'date_desc';
        [$sortColumn, $sortDirection] = match ($sort) {
            'date_asc' => ['sold_at', 'asc'],
            'quantity_desc' => ['quantity_sold', 'desc'],
            'quantity_asc' => ['quantity_sold', 'asc'],
            default => ['sold_at', 'desc'],
        };

        $transactions = $user->transactions()
            ->with('product')
            ->whereHas('product', fn ($query) => $query->where('user_id', $user->id))
            ->when(
                $filters['search'] ?? null,
                fn ($query, $search) => $query->whereHas(
                    'product',
                    fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"),
                ),
            )
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(12)
            ->withQueryString();

        return view('transaksi.index', [
            'title' => 'Riwayat Transaksi',
            'transactions' => $transactions,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'sort' => $sort,
            ],
        ]);
    }
}
