<?php

namespace App\Filament\Widgets;

use App\Models\MonthlyLoeReport;
use Carbon\CarbonImmutable;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\ChartWidget;

class LoeUtilizationChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected int|string|array $columnSpan = [
        'xl' => 8,
    ];

    protected ?string $heading = 'Utilization trend';

    protected ?string $description = 'Average allocation and available bandwidth for the last six months.';

    protected ?string $pollingInterval = null;

    protected function getData(): array
    {
        $anchorMonth = CarbonImmutable::create(
            year: (int) ($this->pageFilters['report_year'] ?? now()->year),
            month: (int) ($this->pageFilters['report_month'] ?? now()->month),
            day: 1,
        );
        $departmentId = $this->pageFilters['department_id'] ?? null;

        $months = collect(range(5, 0))->map(fn (int $monthsAgo): CarbonImmutable => $anchorMonth->subMonths($monthsAgo))
            ->push($anchorMonth)
            ->values();

        $reports = MonthlyLoeReport::query()
            ->when($departmentId, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->where(function ($query) use ($months): void {
                foreach ($months as $month) {
                    $query->orWhere(function ($nested) use ($month): void {
                        $nested
                            ->where('report_year', $month->year)
                            ->where('report_month', $month->month);
                    });
                }
            })
            ->get()
            ->groupBy(fn (MonthlyLoeReport $report): string => sprintf('%04d-%02d', $report->report_year, $report->report_month));

        $labels = [];
        $allocationData = [];
        $openCapacityData = [];

        foreach ($months as $month) {
            $key = $month->format('Y-m');
            $monthReports = $reports->get($key, collect());

            $labels[] = $month->format('M Y');
            $allocationData[] = round($monthReports->avg(fn (MonthlyLoeReport $report): float => (float) $report->total_percentage) ?? 0, 2);
            $openCapacityData[] = round($monthReports->avg(fn (MonthlyLoeReport $report): float => (float) $report->open_to_new_projects_percentage) ?? 0, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Average allocation %',
                    'data' => $allocationData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.16)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
                [
                    'label' => 'Average open capacity %',
                    'data' => $openCapacityData,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => 100,
                ],
            ],
        ];
    }
}
