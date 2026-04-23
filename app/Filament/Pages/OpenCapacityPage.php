<?php

namespace App\Filament\Pages;

use App\Models\MonthlyLoeReport;
use BackedEnum;
use Illuminate\Support\Collection;

class OpenCapacityPage extends ReportingSnapshotPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Open Capacity';

    protected static ?int $navigationSort = 13;

    protected static ?string $slug = 'open-capacity';

    protected static ?string $title = 'Open Capacity';

    protected string $view = 'filament.pages.reporting-snapshot-page';

    public function summaryCards(): array
    {
        $reports = $this->capacityReports();

        return [
            [
                'label' => 'Open capacity',
                'value' => $this->formatPercentage((float) $reports->sum('open_to_new_projects_percentage')),
                'description' => 'Total reported bandwidth across all current reports.',
            ],
            [
                'label' => 'Average open capacity',
                'value' => $this->formatPercentage((float) $reports->avg('open_to_new_projects_percentage')),
                'description' => 'Average remaining bandwidth per employee report.',
            ],
            [
                'label' => 'Employees with capacity',
                'value' => (string) $reports->where('open_to_new_projects_percentage', '>', 0)->count(),
                'description' => 'Employees reporting at least some room for new work.',
            ],
        ];
    }

    public function tableColumns(): array
    {
        return [
            'Employee',
            'Department',
            'Open capacity',
            'Allocation',
            'Time off',
        ];
    }

    public function tableEmptyState(): string
    {
        return 'No report rows are available for the selected reporting scope.';
    }

    public function tableHeading(): string
    {
        return 'Capacity detail';
    }

    public function tableSubheading(): string
    {
        return 'Employees sorted by available open capacity for the current reporting window.';
    }

    public function tableRows(): array
    {
        return $this->capacityReports()
            ->map(fn ($report): array => [
                $report->user?->name ?? 'Unknown employee',
                $report->department?->name ?? 'Unassigned',
                $this->formatPercentage((float) $report->open_to_new_projects_percentage),
                $this->formatPercentage((float) $report->total_percentage),
                $this->formatPercentage((float) $report->time_off_percentage),
            ])
            ->all();
    }

    /**
     * @return Collection<int, MonthlyLoeReport>
     */
    protected function capacityReports(): Collection
    {
        return $this->reportsQuery()
            ->with(['department:id,name', 'user:id,name'])
            ->orderByDesc('open_to_new_projects_percentage')
            ->orderBy('user_id')
            ->get();
    }

    protected function heroBody(): string
    {
        return 'See where new work can still fit';
    }
}
