<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\MonthlyLoeReportLine;
use App\Models\Project;
use Carbon\CarbonImmutable;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

class ProjectAnalytics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?string $navigationLabel = 'Project Analytics';

    protected static ?string $title = 'Project Analytics';

    protected string $view = 'filament.pages.project-analytics';

    protected Width|string|null $maxContentWidth = Width::Full;

    /**
     * @var array<int, int>
     */
    public array $departmentIds = [];

    public ?int $projectId = null;

    public int $reportMonth;

    public int $reportYear;

    public function mount(): void
    {
        $this->projectId = request()->integer('project_id') ?: null;
        $this->reportMonth = request()->integer('report_month') ?: now()->month;
        $this->reportYear = request()->integer('report_year') ?: now()->year;
        $this->departmentIds = $this->resolveDepartmentIds();
    }

    public function contributionLines(): Collection
    {
        return $this->lineQuery()
            ->with([
                'monthlyLoeReport.department:id,name',
                'monthlyLoeReport.user:id,name',
                'project:id,name',
            ])
            ->get();
    }

    public function contributorBreakdown(): Collection
    {
        return MonthlyLoeReportLine::query()
            ->selectRaw('monthly_loe_reports.user_id, users.name as user_name, departments.name as department_name, SUM(monthly_loe_report_lines.entered_hours) as total_hours, AVG(monthly_loe_report_lines.calculated_percentage) as average_percentage')
            ->join('monthly_loe_reports', 'monthly_loe_reports.id', '=', 'monthly_loe_report_lines.monthly_loe_report_id')
            ->join('users', 'users.id', '=', 'monthly_loe_reports.user_id')
            ->leftJoin('departments', 'departments.id', '=', 'monthly_loe_reports.department_id')
            ->where('monthly_loe_report_lines.line_type', 'project')
            ->when($this->projectId, fn (Builder $query): Builder => $query->where('monthly_loe_report_lines.project_id', $this->projectId))
            ->where('monthly_loe_reports.report_year', $this->reportYear)
            ->where('monthly_loe_reports.report_month', $this->reportMonth)
            ->when(
                $this->departmentIds !== [],
                fn (Builder $query): Builder => $query->whereIn('monthly_loe_reports.department_id', $this->departmentIds),
            )
            ->groupBy('monthly_loe_reports.user_id', 'users.name', 'departments.name')
            ->orderByDesc('total_hours')
            ->limit(8)
            ->get();
    }

    public function departmentOptions(): array
    {
        return Department::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function monthLabel(): string
    {
        return CarbonImmutable::create($this->reportYear, $this->reportMonth, 1)->format('F Y');
    }

    public function projectOptions(): array
    {
        return Project::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function projectOverview(): Collection
    {
        return Project::query()
            ->withCount('projectAssignments')
            ->withSum(['monthlyLoeReportLines as allocation_hours_sum' => function (Builder $query): void {
                $query->where('line_type', 'project')
                    ->whereHas('monthlyLoeReport', function (Builder $reportQuery): void {
                        $reportQuery->where('report_year', $this->reportYear)
                            ->where('report_month', $this->reportMonth)
                            ->when(
                                $this->departmentIds !== [],
                                fn (Builder $departmentQuery): Builder => $departmentQuery->whereIn('department_id', $this->departmentIds),
                            );
                    });
            }], 'entered_hours')
            ->orderBy('name')
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

    public function selectedProject(): ?Project
    {
        if (! $this->projectId) {
            return null;
        }

        return Project::query()->find($this->projectId);
    }

    /**
     * @return array<string, float|int|string|array<int, string>|null>
     */
    public function summary(): array
    {
        $lines = $this->lineQuery()->get();
        $selectedProject = $this->selectedProject();

        return [
            'average_allocation' => round((float) $lines->avg('calculated_percentage'), 2),
            'contributors' => $lines->pluck('monthly_loe_report_id')->unique()->count(),
            'department_names' => $this->selectedDepartments()->pluck('name')->values()->all(),
            'expected_allocation' => round((float) $lines->avg('expected_percentage'), 2),
            'project_name' => $selectedProject?->name,
            'total_hours' => round((float) $lines->sum('entered_hours'), 2),
            'variance_count' => $lines->filter(
                fn (MonthlyLoeReportLine $line): bool => $line->expected_percentage !== null
                    && (float) $line->calculated_percentage > (float) $line->expected_percentage
            )->count(),
        ];
    }

    protected function lineQuery(): Builder
    {
        return MonthlyLoeReportLine::query()
            ->where('line_type', 'project')
            ->when($this->projectId, fn (Builder $query): Builder => $query->where('project_id', $this->projectId))
            ->whereHas('monthlyLoeReport', function (Builder $query): void {
                $query->where('report_year', $this->reportYear)
                    ->where('report_month', $this->reportMonth)
                    ->when(
                        $this->departmentIds !== [],
                        fn (Builder $departmentQuery): Builder => $departmentQuery->whereIn('department_id', $this->departmentIds),
                    );
            })
            ->orderByDesc('monthly_loe_report_id');
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
