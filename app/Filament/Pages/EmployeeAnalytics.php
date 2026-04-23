<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\MonthlyLoeReportLine;
use App\Models\User;
use Carbon\CarbonImmutable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class EmployeeAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?string $navigationLabel = 'Employee Analytics';

    protected static ?string $title = 'Employee Analytics';

    protected string $view = 'filament.pages.employee-analytics';

    protected Width|string|null $maxContentWidth = Width::Full;

    /**
     * @var array<int, int>
     */
    public array $departmentIds = [];

    public ?int $employeeId = null;

    public int $reportMonth;

    public int $reportYear;

    public function mount(): void
    {
        $this->reportMonth = request()->integer('report_month') ?: now()->month;
        $this->reportYear = request()->integer('report_year') ?: now()->year;
        $this->departmentIds = $this->resolveDepartmentIds();
        $this->employeeId = request()->integer('employee_id') ?: null;
    }

    public function departmentOptions(): array
    {
        return Department::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function employeeBreakdown(): Collection
    {
        return $this->reports()
            ->sortByDesc(fn (MonthlyLoeReport $report): float => (float) $report->total_percentage)
            ->values();
    }

    public function employeeOptions(): array
    {
        return User::query()
            ->where('is_active', true)
            ->when(
                $this->departmentIds !== [],
                fn (Builder $query): Builder => $query->whereIn('department_id', $this->departmentIds),
            )
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function monthLabel(): string
    {
        return CarbonImmutable::create($this->reportYear, $this->reportMonth, 1)->format('F Y');
    }

    public function projectBreakdown(): Collection
    {
        return MonthlyLoeReportLine::query()
            ->selectRaw('project_id, SUM(entered_hours) as total_hours, AVG(calculated_percentage) as average_percentage')
            ->with('project:id,name')
            ->where('line_type', 'project')
            ->whereHas('monthlyLoeReport', function (Builder $query): void {
                $query->where('report_year', $this->reportYear)
                    ->where('report_month', $this->reportMonth)
                    ->when(
                        $this->departmentIds !== [],
                        fn (Builder $reportQuery): Builder => $reportQuery->whereIn('department_id', $this->departmentIds),
                    )
                    ->when(
                        $this->employeeId,
                        fn (Builder $reportQuery): Builder => $reportQuery->where('user_id', $this->employeeId),
                    );
            })
            ->groupBy('project_id')
            ->orderByDesc('total_hours')
            ->limit(6)
            ->get();
    }

    public function reports(): Collection
    {
        return $this->reportQuery()
            ->with(['department:id,name', 'user:id,name'])
            ->orderByDesc('total_percentage')
            ->orderBy('user_id')
            ->get();
    }

    public function selectedDepartments(): Collection
    {
        if ($this->departmentIds === []) {
            return collect();
        }

        return Department::query()
            ->whereIn('id', $this->departmentIds)
            ->orderBy('name')
            ->get();
    }

    public function selectedEmployee(): ?User
    {
        if (! $this->employeeId) {
            return null;
        }

        return User::query()
            ->when(
                $this->departmentIds !== [],
                fn (Builder $query): Builder => $query->whereIn('department_id', $this->departmentIds),
            )
            ->find($this->employeeId);
    }

    /**
     * @return array<string, float|int|array<int, string>>
     */
    public function summary(): array
    {
        $reports = $this->reportQuery()->get();
        $selectedEmployee = $this->selectedEmployee();
        $selectedEmployeeReport = $selectedEmployee
            ? $reports->firstWhere('user_id', $selectedEmployee->id)
            : null;

        return [
            'average_allocation' => round((float) $reports->avg('total_percentage'), 2),
            'average_open_capacity' => round((float) $reports->avg('open_to_new_projects_percentage'), 2),
            'average_time_off' => round((float) $reports->avg('time_off_percentage'), 2),
            'department_names' => $this->selectedDepartments()->pluck('name')->values()->all(),
            'employee_name' => $selectedEmployee?->name,
            'highest_allocation' => round((float) $reports->max('total_percentage'), 2),
            'overallocated_employees' => $reports->filter(fn (MonthlyLoeReport $report): bool => (float) $report->total_percentage > 100)->count(),
            'selected_employee_allocation' => round((float) ($selectedEmployeeReport?->total_percentage ?? 0), 2),
            'selected_employee_open_capacity' => round((float) ($selectedEmployeeReport?->open_to_new_projects_percentage ?? 0), 2),
            'selected_employee_time_off' => round((float) ($selectedEmployeeReport?->time_off_percentage ?? 0), 2),
            'submitted_reports' => $reports->where('status', 'submitted')->count(),
            'total_reports' => $reports->count(),
        ];
    }

    protected function reportQuery(): Builder
    {
        return MonthlyLoeReport::query()
            ->where('report_year', $this->reportYear)
            ->where('report_month', $this->reportMonth)
            ->when(
                $this->departmentIds !== [],
                fn (Builder $query): Builder => $query->whereIn('department_id', $this->departmentIds),
            )
            ->when($this->employeeId, fn (Builder $query): Builder => $query->where('user_id', $this->employeeId));
    }

    /**
     * @return array<int, int>
     */
    private function resolveDepartmentIds(): array
    {
        $departmentIds = collect((array) request()->query('department_ids', []))
            ->map(fn (mixed $departmentId): int => (int) $departmentId)
            ->filter(fn (int $departmentId): bool => $departmentId > 0)
            ->unique()
            ->values()
            ->all();

        if ($departmentIds !== []) {
            return $departmentIds;
        }

        $engineeringDepartmentId = Department::query()
            ->where('name', 'Engineering')
            ->value('id');

        if ($engineeringDepartmentId) {
            return [(int) $engineeringDepartmentId];
        }

        $firstDepartmentId = Department::query()
            ->orderBy('name')
            ->value('id');

        return $firstDepartmentId ? [(int) $firstDepartmentId] : [];
    }
}
