<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class AdminDashboardHero extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.admin-dashboard-hero';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        $reportMonth = (int) ($this->pageFilters['report_month'] ?? now()->month);
        $reportYear = (int) ($this->pageFilters['report_year'] ?? now()->year);
        $departmentId = $this->pageFilters['department_id'] ?? null;
        $department = $departmentId ? Department::query()->find($departmentId) : null;

        $reportsQuery = MonthlyLoeReport::query()
            ->where('report_month', $reportMonth)
            ->where('report_year', $reportYear)
            ->when($departmentId, fn ($query, $departmentId) => $query->where('department_id', $departmentId));

        $employeesQuery = User::query()
            ->where('is_active', true)
            ->when($departmentId, fn ($query, $departmentId) => $query->where('department_id', $departmentId));

        return [
            'departmentId' => $department?->id,
            'departmentName' => $department?->name,
            'employeesCount' => $employeesQuery->count(),
            'reportMonth' => $reportMonth,
            'monthLabel' => CarbonImmutable::create($reportYear, $reportMonth, 1)->format('F Y'),
            'projectsCount' => Project::query()->where('status', 'active')->count(),
            'reportsCount' => $reportsQuery->count(),
            'reportYear' => $reportYear,
        ];
    }
}
