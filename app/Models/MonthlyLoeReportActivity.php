<?php

namespace App\Models;

use App\MonthlyLoeReportActivityAction;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'monthly_loe_report_id',
    'user_id',
    'action',
    'description',
    'meta',
])]
class MonthlyLoeReportActivity extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'action' => MonthlyLoeReportActivityAction::class,
            'meta' => 'array',
        ];
    }

    public function monthlyLoeReport(): BelongsTo
    {
        return $this->belongsTo(MonthlyLoeReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
