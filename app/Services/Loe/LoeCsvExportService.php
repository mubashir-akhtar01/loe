<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeReport;
use App\Models\MonthlyLoeReportLine;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoeCsvExportService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function stream(string $export, array $filters = []): StreamedResponse
    {
        return match ($export) {
            'dashboard-summary' => $this->streamDashboardSummary($filters),
            'project-allocations' => $this->streamProjectAllocations($filters),
            'monthly-reports' => $this->streamMonthlyReports($filters),
            default => abort(404),
        };
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function streamDashboardSummary(array $filters = []): StreamedResponse
    {
        $reports = $this->monthlyReportQuery($filters)->get();
        $departmentFilter = $filters['department_ids'] ?? $filters['department_id'] ?? 'All';

        $rows = [[
            'Year',
            'Month',
            'Department Filter',
            'Employee ID',
            'Project ID',
            'Status',
            'Total Reports',
            'Submitted Reports',
            'Pending Reports',
            'Average Allocation %',
            'Average Time Off %',
            'Average Open Capacity %',
            'Overallocated Reports',
            'Underallocated Reports',
        ], [
            $filters['report_year'] ?? 'All',
            $filters['report_month'] ?? 'All',
            is_array($departmentFilter) ? implode('|', $departmentFilter) : $departmentFilter,
            $filters['user_id'] ?? 'All',
            $filters['project_id'] ?? 'All',
            $filters['status'] ?? 'All',
            $reports->count(),
            $reports->where('status', 'submitted')->count(),
            $reports->where('status', '!=', 'submitted')->count(),
            number_format((float) $reports->avg('total_percentage'), 2, '.', ''),
            number_format((float) $reports->avg('time_off_percentage'), 2, '.', ''),
            number_format((float) $reports->avg('open_to_new_projects_percentage'), 2, '.', ''),
            $reports->filter(fn (MonthlyLoeReport $report): bool => (float) $report->total_percentage > 100)->count(),
            $reports->filter(fn (MonthlyLoeReport $report): bool => (float) $report->total_percentage < 95)->count(),
        ]];

        return $this->streamRows('dashboard-summary', $rows);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function streamMonthlyReports(array $filters = []): StreamedResponse
    {
        $rows = [[
            'Employee',
            'Email',
            'Department',
            'Year',
            'Month',
            'Status',
            'Total Hours',
            'Total Days',
            'Total Allocation %',
            'Time Off Hours',
            'Time Off %',
            'Open To New Projects Hours',
            'Open To New Projects %',
            'Submitted At',
            'Closed At',
            'Report Notes',
        ]];

        foreach ($this->monthlyReportQuery($filters)->cursor() as $report) {
            $rows[] = [
                $report->user?->name,
                $report->user?->email,
                $report->department?->name,
                $report->report_year,
                $report->report_month,
                $report->status->value,
                $report->total_hours,
                $report->total_days,
                $report->total_percentage,
                $report->time_off_hours,
                $report->time_off_percentage,
                $report->open_to_new_projects_hours,
                $report->open_to_new_projects_percentage,
                $report->submitted_at?->toDateTimeString(),
                $report->closed_at?->toDateTimeString(),
                $report->report_notes,
            ];
        }

        return $this->streamRows('monthly-loe-reports', $rows);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function streamProjectAllocations(array $filters = []): StreamedResponse
    {
        $rows = [[
            'Project',
            'Employee',
            'Department',
            'Year',
            'Month',
            'Entered Hours',
            'Calculated Days',
            'Calculated Allocation %',
            'Expected Allocation %',
            'Status',
            'Line Notes',
        ]];

        foreach ($this->projectAllocationQuery($filters)->cursor() as $line) {
            $rows[] = [
                $line->project?->name,
                $line->monthlyLoeReport?->user?->name,
                $line->monthlyLoeReport?->department?->name,
                $line->monthlyLoeReport?->report_year,
                $line->monthlyLoeReport?->report_month,
                $line->entered_hours,
                $line->calculated_days,
                $line->calculated_percentage,
                $line->expected_percentage,
                $line->monthlyLoeReport?->status?->value,
                $line->line_notes,
            ];
        }

        return $this->streamRows('project-allocations', $rows);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function monthlyReportQuery(array $filters): Builder
    {
        $departmentIds = collect((array) ($filters['department_ids'] ?? []))
            ->map(fn (mixed $departmentId): int => (int) $departmentId)
            ->filter(fn (int $departmentId): bool => $departmentId > 0)
            ->values()
            ->all();

        return MonthlyLoeReport::query()
            ->with([
                'department:id,name',
                'user:id,name,email',
            ])
            ->when(
                $departmentIds !== [],
                fn (Builder $query): Builder => $query->whereIn('department_id', $departmentIds),
                fn (Builder $query): Builder => $query->when(
                    $filters['department_id'] ?? null,
                    fn (Builder $departmentQuery, $departmentId): Builder => $departmentQuery->where('department_id', $departmentId),
                ),
            )
            ->when($filters['project_id'] ?? null, fn (Builder $query, $projectId): Builder => $query->whereHas(
                'lines',
                fn (Builder $lineQuery): Builder => $lineQuery->where('project_id', $projectId),
            ))
            ->when($filters['report_month'] ?? null, fn (Builder $query, $reportMonth): Builder => $query->where('report_month', $reportMonth))
            ->when($filters['report_year'] ?? null, fn (Builder $query, $reportYear): Builder => $query->where('report_year', $reportYear))
            ->when($filters['status'] ?? null, fn (Builder $query, $status): Builder => $query->where('status', $status))
            ->when($filters['user_id'] ?? null, fn (Builder $query, $userId): Builder => $query->where('user_id', $userId))
            ->orderByDesc('report_year')
            ->orderByDesc('report_month')
            ->orderBy('user_id');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function projectAllocationQuery(array $filters): Builder
    {
        $departmentIds = collect((array) ($filters['department_ids'] ?? []))
            ->map(fn (mixed $departmentId): int => (int) $departmentId)
            ->filter(fn (int $departmentId): bool => $departmentId > 0)
            ->values()
            ->all();

        return MonthlyLoeReportLine::query()
            ->with([
                'monthlyLoeReport.department:id,name',
                'monthlyLoeReport.user:id,name',
                'project:id,name',
            ])
            ->where('line_type', 'project')
            ->when(
                $departmentIds !== [],
                fn (Builder $query): Builder => $query->whereHas(
                    'monthlyLoeReport',
                    fn (Builder $reportQuery): Builder => $reportQuery->whereIn('department_id', $departmentIds),
                ),
                fn (Builder $query): Builder => $query->when(
                    $filters['department_id'] ?? null,
                    fn (Builder $departmentQuery, $departmentId): Builder => $departmentQuery->whereHas(
                        'monthlyLoeReport',
                        fn (Builder $reportQuery): Builder => $reportQuery->where('department_id', $departmentId),
                    ),
                ),
            )
            ->when($filters['project_id'] ?? null, fn (Builder $query, $projectId): Builder => $query->where('project_id', $projectId))
            ->when($filters['report_month'] ?? null, fn (Builder $query, $reportMonth): Builder => $query->whereHas(
                'monthlyLoeReport',
                fn (Builder $reportQuery): Builder => $reportQuery->where('report_month', $reportMonth),
            ))
            ->when($filters['report_year'] ?? null, fn (Builder $query, $reportYear): Builder => $query->whereHas(
                'monthlyLoeReport',
                fn (Builder $reportQuery): Builder => $reportQuery->where('report_year', $reportYear),
            ))
            ->when($filters['status'] ?? null, fn (Builder $query, $status): Builder => $query->whereHas(
                'monthlyLoeReport',
                fn (Builder $reportQuery): Builder => $reportQuery->where('status', $status),
            ))
            ->when($filters['user_id'] ?? null, fn (Builder $query, $userId): Builder => $query->whereHas(
                'monthlyLoeReport',
                fn (Builder $reportQuery): Builder => $reportQuery->where('user_id', $userId),
            ))
            ->orderByDesc(
                MonthlyLoeReport::query()
                    ->select('report_year')
                    ->whereColumn('monthly_loe_reports.id', 'monthly_loe_report_lines.monthly_loe_report_id')
                    ->limit(1),
            )
            ->orderByDesc(
                MonthlyLoeReport::query()
                    ->select('report_month')
                    ->whereColumn('monthly_loe_reports.id', 'monthly_loe_report_lines.monthly_loe_report_id')
                    ->limit(1),
            );
    }

    /**
     * @param  array<int, array<int, string|null|int|float>>  $rows
     */
    private function streamRows(string $name, array $rows): StreamedResponse
    {
        $filename = sprintf('%s-%s.csv', $name, now()->format('Ymd-His'));

        return response()->streamDownload(function () use ($rows): void {
            $stream = fopen('php://output', 'wb');

            foreach ($rows as $row) {
                fputcsv($stream, $row);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
