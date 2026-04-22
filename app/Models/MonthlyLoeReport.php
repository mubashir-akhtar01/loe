<?php

namespace App\Models;

use App\MonthlyLoeReportStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'department_id',
    'report_year',
    'report_month',
    'status',
    'report_notes',
    'submitted_at',
    'closed_at',
    'total_hours',
    'total_days',
    'total_percentage',
    'time_off_hours',
    'time_off_percentage',
    'open_to_new_projects_hours',
    'open_to_new_projects_percentage',
])]
class MonthlyLoeReport extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
            'open_to_new_projects_hours' => 'decimal:2',
            'open_to_new_projects_percentage' => 'decimal:2',
            'report_month' => 'integer',
            'report_year' => 'integer',
            'status' => MonthlyLoeReportStatus::class,
            'submitted_at' => 'datetime',
            'time_off_hours' => 'decimal:2',
            'time_off_percentage' => 'decimal:2',
            'total_days' => 'decimal:2',
            'total_hours' => 'decimal:2',
            'total_percentage' => 'decimal:2',
        ];
    }

    public function activities(): HasMany
    {
        return $this->hasMany(MonthlyLoeReportActivity::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(MonthlyLoeReportLine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
