<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisHistoryItem extends Model
{
    protected $fillable = [
        'analysis_history_id',
        'product_id',
        'product_name',
        'stock',
        'recommended_stock',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'recommended_stock' => 'integer',
        ];
    }

    public function history(): BelongsTo
    {
        return $this->belongsTo(AnalysisHistory::class, 'analysis_history_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
