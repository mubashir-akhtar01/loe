<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\OpenCapacityPage;
use App\Filament\Pages\OverallocatedEmployeesPage;
use App\Filament\Pages\PendingEmployeesPage;
use App\Filament\Pages\SubmittedReportsPage;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeReportStatus;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoeAdminStatsOverview extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Reporting snapshot';

    protected ?string $description = 'Submission health and staffing pressure for the selected reporting scope.';

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $today = CarbonImmutable::create(
            year: (int) ($this->pageFilters['report_year'] ?? now()->year),
            month: (int) ($this->pageFilters['report_month'] ?? now()->month),
            day: 1,
        );
        $departmentId = $this->pageFilters['department_id'] ?? null;

        $reports = MonthlyLoeReport::query()
            ->where('report_year', $today->year)
            ->where('report_month', $today->month)
            ->when($departmentId, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->get();

        $activeEmployees = User::query()
            ->where('is_active', true)
            ->when($departmentId, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->count();

        $submittedCount = $reports
            ->where('status', MonthlyLoeReportStatus::Submitted)
            ->count();

        $pendingCount = max($activeEmployees - $submittedCount, 0);
        $overallocatedCount = $reports->filter(fn (MonthlyLoeReport $report): bool => (float) $report->total_percentage > 100)->count();
        $availableCapacity = $reports->sum(fn (MonthlyLoeReport $report): float => (float) $report->open_to_new_projects_percentage);
        $scopeParameters = array_filter([
            'department_id' => $departmentId,
            'report_month' => $today->month,
            'report_year' => $today->year,
        ], fn (mixed $value): bool => $value !== null);

        return [
            Stat::make('Submitted reports', (string) $submittedCount)
                ->description($today->format('F Y'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->chart([$submittedCount, max($submittedCount - 1, 0), $submittedCount, $submittedCount + 1])
                ->color('success')
                ->extraAttributes($this->linkAttributes(SubmittedReportsPage::getUrl($scopeParameters))),
            Stat::make('Pending employees', (string) $pendingCount)
                ->description('Still need to submit')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([$pendingCount + 2, $pendingCount + 1, $pendingCount, $pendingCount])
                ->color($pendingCount > 0 ? 'warning' : 'success')
                ->extraAttributes($this->linkAttributes(PendingEmployeesPage::getUrl($scopeParameters))),
            Stat::make('Overallocated employees', (string) $overallocatedCount)
                ->description('Above 100% total allocation')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->chart([max($overallocatedCount - 1, 0), $overallocatedCount, $overallocatedCount, $overallocatedCount + 1])
                ->color($overallocatedCount > 0 ? 'danger' : 'success')
                ->extraAttributes($this->linkAttributes(OverallocatedEmployeesPage::getUrl($scopeParameters))),
            Stat::make('Open capacity', number_format($availableCapacity, 2).'%')
                ->description('Across all current reports')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->chart([
                    round(max($availableCapacity - 10, 0), 2),
                    round(max($availableCapacity - 5, 0), 2),
                    round($availableCapacity, 2),
                    round($availableCapacity + 5, 2),
                ])
                ->color('info')
                ->extraAttributes($this->linkAttributes(OpenCapacityPage::getUrl($scopeParameters))),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function linkAttributes(string $url): array
    {
        return [
            'class' => 'cursor-pointer',
            'onclick' => "window.location = '{$url}'",
            'title' => 'Open detailed page',
        ];
    }
}
