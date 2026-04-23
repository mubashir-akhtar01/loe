<?php

namespace App\Filament\Pages;

use App\MonthlyLoeReportStatus;
use BackedEnum;

class SubmittedReportsPage extends ReportingSnapshotPage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationLabel = 'Submitted Reports';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'submitted-reports';

    protected static ?string $title = 'Submitted Reports';

    protected string $view = 'filament.pages.reporting-snapshot-page';

    public function summaryCards(): array
    {
        $reports = $this->reportsQuery()
            ->where('status', MonthlyLoeReportStatus::Submitted)
            ->get();
        $activeEmployeesCount = $this->activeEmployeesCount();

        return [
            [
                'label' => 'Submitted reports',
                'value' => (string) $reports->count(),
                'description' => 'LoE reports submitted inside the current scope.',
            ],
            [
                'label' => 'Coverage',
                'value' => $this->averageCoveragePercentage($reports->count(), $activeEmployeesCount),
                'description' => sprintf('%d active employees are in scope.', $activeEmployeesCount),
            ],
            [
                'label' => 'Average allocation',
                'value' => $this->formatPercentage((float) $reports->avg('total_percentage')),
                'description' => 'Average submitted allocation across these reports.',
            ],
        ];
    }

    public function tableColumns(): array
    {
        return [
            'Employee',
            'Department',
            'Submitted at',
            'Allocation',
            'Open capacity',
        ];
    }

    public function tableEmptyState(): string
    {
        return 'No submitted reports are available for the selected month and department scope.';
    }

    public function tableHeading(): string
    {
        return 'Submitted report detail';
    }

    public function tableSubheading(): string
    {
        return 'Every successfully submitted LoE report for the selected reporting window.';
    }

    public function tableRows(): array
    {
        return $this->reportsQuery()
            ->with(['department:id,name', 'user:id,name'])
            ->where('status', MonthlyLoeReportStatus::Submitted)
            ->orderBy('user_id')
            ->get()
            ->map(fn ($report): array => [
                $report->user?->name ?? 'Unknown employee',
                $report->department?->name ?? 'Unassigned',
                $report->submitted_at?->format('M d, Y H:i') ?? 'Not recorded',
                $this->formatPercentage((float) $report->total_percentage),
                $this->formatPercentage((float) $report->open_to_new_projects_percentage),
            ])
            ->all();
    }

    protected function heroBody(): string
    {
        return 'Review confirmed submissions';
    }
}
