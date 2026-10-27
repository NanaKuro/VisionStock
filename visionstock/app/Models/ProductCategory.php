<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductCategory extends Model
{
    public const PREDEFINED_NAMES = [
        'Alat Tulis',
        'Bahan Baku Makanan',
        'Buku',
        'Minuman',
        'Makanan',
        'Perlengkapan Rumah Tangga',
        'Barang lainnya',
    ];

    protected $fillable = [
        'user_id',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
