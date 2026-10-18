<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user instanceof User, 403);

        return view('dashboard', [
            'title' => 'Dashboard',
            'metrics' => [
                'transaction_records' => $user->transactions()->count(),
                'products' => $user->products()->count(),
                'units_in_stock' => $user->products()->sum('stock'),
                'units_sold' => $user->transactions()->sum('quantity_sold'),
            ],
        ]);
    }
}
