<?php

namespace App\Filament\Pages;

use App\Models\MonthlyLoeReport;
use BackedEnum;
use Illuminate\Support\Collection;

class OverallocatedEmployeesPage extends ReportingSnapshotPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Overallocated Employees';

    protected static ?int $navigationSort = 12;

    protected static ?string $slug = 'overallocated-employees';

    protected static ?string $title = 'Overallocated Employees';

    protected string $view = 'filament.pages.reporting-snapshot-page';

    public function summaryCards(): array
    {
        $reports = $this->overallocatedReports();

        return [
            [
                'label' => 'Overallocated employees',
                'value' => (string) $reports->count(),
                'description' => 'Employees reporting above 100% total allocation.',
            ],
            [
                'label' => 'Highest allocation',
                'value' => $this->formatPercentage((float) $reports->max('total_percentage')),
                'description' => 'Peak allocation inside the selected scope.',
            ],
            [
                'label' => 'Average time off',
                'value' => $this->formatPercentage((float) $reports->avg('time_off_percentage')),
                'description' => 'Reported time off among overloaded employees.',
            ],
        ];
    }

    public function tableColumns(): array
    {
        return [
            'Employee',
            'Department',
            'Allocation',
            'Time off',
            'Open capacity',
        ];
    }

    public function tableEmptyState(): string
    {
        return 'No employees are over 100% allocation for the selected reporting scope.';
    }

    public function tableHeading(): string
    {
        return 'Overallocation detail';
    }

    public function tableSubheading(): string
    {
        return 'Employees whose reported total allocation exceeds available capacity.';
    }

    public function tableRows(): array
    {
        return $this->overallocatedReports()
            ->map(fn ($report): array => [
                $report->user?->name ?? 'Unknown employee',
                $report->department?->name ?? 'Unassigned',
                $this->formatPercentage((float) $report->total_percentage),
                $this->formatPercentage((float) $report->time_off_percentage),
                $this->formatPercentage((float) $report->open_to_new_projects_percentage),
            ])
            ->all();
    }

    /**
     * @return Collection<int, MonthlyLoeReport>
     */
    protected function overallocatedReports(): Collection
    {
        return $this->reportsQuery()
            ->with(['department:id,name', 'user:id,name'])
            ->where('total_percentage', '>', 100)
            ->orderByDesc('total_percentage')
            ->get();
    }

    protected function heroBody(): string
    {
        return 'Investigate employees carrying more than full capacity';
    }
}
