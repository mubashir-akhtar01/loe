<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'project_id',
    'expected_percentage',
    'starts_on',
    'ends_on',
    'is_active',
])]
class ProjectAssignment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'ends_on' => 'date',
            'expected_percentage' => 'decimal:2',
            'is_active' => 'boolean',
            'starts_on' => 'date',
        ];
    }

    public function monthlyLoeReportLines(): HasMany
    {
        return $this->hasMany(MonthlyLoeReportLine::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
