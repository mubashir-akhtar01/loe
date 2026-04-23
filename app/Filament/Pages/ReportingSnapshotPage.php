<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use UnitEnum;

abstract class ReportingSnapshotPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?int $departmentId = null;

    public int $reportMonth;

    public int $reportYear;

    public function mount(): void
    {
        $this->reportMonth = request()->integer('report_month') ?: now()->month;
        $this->reportYear = request()->integer('report_year') ?: now()->year;
        $this->departmentId = request()->integer('department_id') ?: null;
    }

    public function activeEmployeesCount(): int
    {
        return $this->activeEmployeesQuery()->count();
    }

    public function backToDashboardUrl(): string
    {
        return Dashboard::getUrl([
            'filters' => $this->scopeParameters(),
        ]);
    }

    public function departmentOptions(): array
    {
        return Department::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public function formatPercentage(float $value): string
    {
        return number_format($value, 2).'%';
    }

    public function heroDescription(): string
    {
        return sprintf(
            '%s for %s%s.',
            $this->heroBody(),
            $this->monthLabel(),
            $this->selectedDepartmentName() ? ' in '.$this->selectedDepartmentName() : ' across all departments',
        );
    }

    public function monthLabel(): string
    {
        return CarbonImmutable::create($this->reportYear, $this->reportMonth, 1)->format('F Y');
    }

    public function scopeParameters(): array
    {
        return array_filter([
            'department_id' => $this->departmentId,
            'report_month' => $this->reportMonth,
            'report_year' => $this->reportYear,
        ], fn (mixed $value): bool => $value !== null);
    }

    public function scopePill(): string
    {
        return $this->selectedDepartmentName()
            ? $this->selectedDepartmentName().' · '.$this->monthLabel()
            : 'All departments · '.$this->monthLabel();
    }

    public function selectedDepartmentName(): ?string
    {
        if (! $this->departmentId) {
            return null;
        }

        return Department::query()->find($this->departmentId)?->name;
    }

    /**
     * @return array<int, array{description: string, label: string, value: string}>
     */
    abstract public function summaryCards(): array;

    /**
     * @return array<int, string>
     */
    abstract public function tableColumns(): array;

    public function tableEmptyState(): string
    {
        return 'No matching records were found for the selected reporting scope.';
    }

    public function tableHeading(): string
    {
        return 'Records in scope';
    }

    public function tableSubheading(): string
    {
        return 'Detailed rows for the current reporting signal.';
    }

    /**
     * @return array<int, array<int, string>>
     */
    abstract public function tableRows(): array;

    protected function activeEmployeesQuery(): Builder
    {
        return User::query()
            ->where('is_active', true)
            ->when($this->departmentId, fn (Builder $query): Builder => $query->where('department_id', $this->departmentId));
    }

    protected function averageCoveragePercentage(int $submittedCount, int $activeEmployeesCount): string
    {
        if ($activeEmployeesCount === 0) {
            return '0.00%';
        }

        return $this->formatPercentage(($submittedCount / $activeEmployeesCount) * 100);
    }

    protected function reportsQuery(): Builder
    {
        return MonthlyLoeReport::query()
            ->where('report_year', $this->reportYear)
            ->where('report_month', $this->reportMonth)
            ->when($this->departmentId, fn (Builder $query): Builder => $query->where('department_id', $this->departmentId));
    }

    /**
     * @param  Collection<int, MonthlyLoeReport>  $reports
     * @return array<int, MonthlyLoeReport>
     */
    protected function reportsByUserId(Collection $reports): array
    {
        return $reports
            ->keyBy('user_id')
            ->all();
    }

    abstract protected function heroBody(): string;
}
