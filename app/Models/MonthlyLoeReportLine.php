<?php

namespace App\Models;

use App\MonthlyLoeReportLineType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'monthly_loe_report_id',
    'line_type',
    'project_id',
    'project_assignment_id',
    'entered_hours',
    'calculated_days',
    'calculated_percentage',
    'expected_percentage',
    'line_notes',
    'sort_order',
])]
class MonthlyLoeReportLine extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'calculated_days' => 'decimal:2',
            'calculated_percentage' => 'decimal:2',
            'entered_hours' => 'decimal:2',
            'expected_percentage' => 'decimal:2',
            'line_type' => MonthlyLoeReportLineType::class,
            'sort_order' => 'integer',
        ];
    }

    public function monthlyLoeReport(): BelongsTo
    {
        return $this->belongsTo(MonthlyLoeReport::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectAssignment(): BelongsTo
    {
        return $this->belongsTo(ProjectAssignment::class);
    }
}
