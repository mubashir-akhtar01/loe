<?php

namespace App\Models;

use App\ProjectStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class Project extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
        ];
    }

    public function monthlyLoeReportLines(): HasMany
    {
        return $this->hasMany(MonthlyLoeReportLine::class);
    }

    public function projectAssignments(): HasMany
    {
        return $this->hasMany(ProjectAssignment::class);
    }
}
