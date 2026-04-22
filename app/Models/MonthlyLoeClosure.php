<?php

namespace App\Models;

use App\MonthlyLoeClosureType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'closure_year',
    'closure_month',
    'closed_by_user_id',
    'closure_type',
    'closed_at',
    'notes',
])]
class MonthlyLoeClosure extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'closure_month' => 'integer',
            'closure_type' => MonthlyLoeClosureType::class,
            'closure_year' => 'integer',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}
