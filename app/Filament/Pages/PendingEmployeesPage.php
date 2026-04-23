<?php

namespace App\Filament\Pages;

use App\MonthlyLoeReportStatus;
use BackedEnum;

class PendingEmployeesPage extends ReportingSnapshotPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Pending Employees';

    protected static ?int $navigationSort = 11;

    protected static ?string $slug = 'pending-employees';

    protected static ?string $title = 'Pending Employees';

    protected string $view = 'filament.pages.reporting-snapshot-page';

    public function summaryCards(): array
    {
        $reports = $this->reportsQuery()->get();
        $submittedCount = $reports->where('status', MonthlyLoeReportStatus::Submitted)->count();
        $activeEmployeesCount = $this->activeEmployeesCount();
        $pendingCount = max($activeEmployeesCount - $submittedCount, 0);

        return [
            [
                'label' => 'Pending employees',
                'value' => (string) $pendingCount,
                'description' => 'Active employees who still have not submitted for this scope.',
            ],
            [
                'label' => 'Submitted reports',
                'value' => (string) $submittedCount,
                'description' => 'Completed submissions already captured this month.',
            ],
            [
                'label' => 'Coverage',
                'value' => $this->averageCoveragePercentage($submittedCount, $activeEmployeesCount),
                'description' => sprintf('%d active employees are expected to report.', $activeEmployeesCount),
            ],
        ];
    }

    public function tableColumns(): array
    {
        return [
            'Employee',
            'Department',
            'Current report status',
            'Allocation',
            'Open capacity',
        ];
    }

    public function tableEmptyState(): string
    {
        return 'Everyone in scope has already submitted their LoE report.';
    }

    public function tableHeading(): string
    {
        return 'Pending employee follow-up';
    }

    public function tableSubheading(): string
    {
        return 'Employees still needing a submitted report for the selected reporting window.';
    }

    public function tableRows(): array
    {
        $reports = $this->reportsQuery()
            ->with('department:id,name')
            ->get();
        $submittedUserIds = $reports
            ->where('status', MonthlyLoeReportStatus::Submitted)
            ->pluck('user_id')
            ->all();
        $reportsByUserId = $this->reportsByUserId($reports);

        return $this->activeEmployeesQuery()
            ->with('department:id,name')
            ->orderBy('name')
            ->get()
            ->reject(fn ($employee): bool => in_array($employee->id, $submittedUserIds, true))
            ->map(function ($employee) use ($reportsByUserId): array {
                $report = $reportsByUserId[$employee->id] ?? null;

                return [
                    $employee->name,
                    $employee->department?->name ?? 'Unassigned',
                    $report?->status ? ucfirst($report->status->value) : 'No report started',
                    $report ? $this->formatPercentage((float) $report->total_percentage) : '0.00%',
                    $report ? $this->formatPercentage((float) $report->open_to_new_projects_percentage) : '0.00%',
                ];
            })
            ->values()
            ->all();
    }

    protected function heroBody(): string
    {
        return 'Spot the people still waiting to submit';
    }
}
