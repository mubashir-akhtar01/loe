<?php

namespace App\Filament\Employee\Pages;

use App\MonthlyLoeReportLineType;
use App\Models\MonthlyLoeReport;
use Carbon\CarbonImmutable;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class MyAnalytics extends Page
{
    protected static ?string $title = 'My Analytics';

    protected static ?string $navigationLabel = 'My Analytics';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    protected static ?string $slug = 'analytics';

    protected string $view = 'filament.employee.pages.my-analytics';

    public int $reportYear;

    public ?int $reportMonth = null;

    protected ?Collection $reportsCache = null;

    public function mount(): void
    {
        $this->reportYear = request()->integer('report_year') ?: now()->year;
        $requestedMonth = request()->integer('report_month');

        $this->reportMonth = ($requestedMonth >= 1 && $requestedMonth <= 12) ? $requestedMonth : null;
    }

    public function latestReport(): ?MonthlyLoeReport
    {
        return $this->filteredReports()->sortByDesc('report_month')->first();
    }

    /** @return array<int, array<string, float|int|string|array<int, array<string, float|int|string|null>>>> */
    public function monthlyTrend(): array
    {
        $months = $this->reportMonth !== null ? [$this->reportMonth] : range(1, 12);
        $reports = $this->reports();

        return collect($months)
            ->map(function (int $month) use ($reports): ?array {
                /** @var MonthlyLoeReport|null $report */
                $report = $reports->firstWhere('report_month', $month);

                if (($report === null) && ($this->reportMonth !== null)) {
                    return null;
                }

                return [
                    'allocation' => (float) ($report?->total_percentage ?? 0),
                    'label' => CarbonImmutable::create($this->reportYear, $month, 1)->format('M'),
                    'month' => $month,
                    'month_label' => CarbonImmutable::create($this->reportYear, $month, 1)->format('F Y'),
                    'open' => (float) ($report?->open_to_new_projects_percentage ?? 0),
                    'projects' => $report ? $this->projectDetailsForReport($report) : [],
                    'time_off' => (float) ($report?->time_off_percentage ?? 0),
                ];
            })
            ->filter()
            ->all();
    }

    public function projectBreakdown(): Collection
    {
        return $this->filteredReports()
            ->flatMap(fn (MonthlyLoeReport $report): Collection => $report->lines)
            ->filter(fn ($line): bool => $line->line_type === MonthlyLoeReportLineType::Project)
            ->groupBy('project_id')
            ->map(function (Collection $lines) {
                $firstLine = $lines->first();

                return (object) [
                    'average_percentage' => round((float) $lines->avg('calculated_percentage'), 2),
                    'project' => $firstLine?->project,
                    'project_id' => $firstLine?->project_id,
                    'total_hours' => round((float) $lines->sum('entered_hours'), 2),
                ];
            })
            ->sortByDesc('total_hours')
            ->values();
    }

    public function reports(): Collection
    {
        if ($this->reportsCache instanceof Collection) {
            return $this->reportsCache;
        }

        $this->reportsCache = MonthlyLoeReport::query()
            ->with([
                'lines' => fn ($query) => $query
                    ->where('line_type', MonthlyLoeReportLineType::Project)
                    ->with('project:id,name'),
            ])
            ->where('user_id', auth()->id())
            ->where('report_year', $this->reportYear)
            ->orderByDesc('report_month')
            ->get();

        return $this->reportsCache;
    }

    public function selectedPeriodLabel(): string
    {
        if ($this->reportMonth === null) {
            return (string) $this->reportYear;
        }

        return CarbonImmutable::create($this->reportYear, $this->reportMonth, 1)->format('F Y');
    }

    /** @return array<string, float|int> */
    public function summary(): array
    {
        $reports = $this->filteredReports();

        return [
            'average_allocation' => round((float) $reports->avg('total_percentage'), 2),
            'average_open' => round((float) $reports->avg('open_to_new_projects_percentage'), 2),
            'average_time_off' => round((float) $reports->avg('time_off_percentage'), 2),
            'highest_allocation' => round((float) $reports->max('total_percentage'), 2),
            'reports_submitted' => $reports->where('status', 'submitted')->count(),
            'tracked_months' => $reports->count(),
        ];
    }

    /** @return array<int, string> */
    public function monthOptions(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn (int $month): array => [
                $month => CarbonImmutable::create($this->reportYear, $month, 1)->format('F'),
            ])
            ->all();
    }

    protected function filteredReports(): Collection
    {
        return $this->reports()
            ->when(
                $this->reportMonth !== null,
                fn (Collection $reports): Collection => $reports->where('report_month', $this->reportMonth)->values(),
            );
    }

    /** @return array<int, array<string, float|int|string|null>> */
    protected function projectDetailsForReport(MonthlyLoeReport $report): array
    {
        return $report->lines
            ->filter(fn ($line): bool => $line->line_type === MonthlyLoeReportLineType::Project)
            ->sortByDesc('entered_hours')
            ->map(fn ($line): array => [
                'hours' => round((float) $line->entered_hours, 2),
                'percentage' => round((float) $line->calculated_percentage, 2),
                'project_id' => $line->project_id,
                'project_name' => $line->project?->name ?? 'Unknown project',
            ])
            ->values()
            ->all();
    }
}
