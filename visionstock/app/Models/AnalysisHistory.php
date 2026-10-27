<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalysisHistory extends Model
{
    protected $fillable = [
        'user_id',
        'analyzed_at',
        'period_days',
        'coverage_days',
    ];

    protected function casts(): array
    {
        return [
            'analyzed_at' => 'datetime',
            'period_days' => 'integer',
            'coverage_days' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(AnalysisHistoryItem::class);
    }
}
