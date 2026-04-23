<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeReport;
use App\Models\MonthlyLoeReportLine;
use App\MonthlyLoeReportLineType;
use Illuminate\Support\Collection;

class MonthlyLoeReportCalculator
{
    public function __construct(
        protected LoeValueCalculator $loeValueCalculator,
    ) {
    }

    public function recalculate(MonthlyLoeReport $report, int $workingDays, float $openThreshold = 95.0): MonthlyLoeReport
    {
        $report->loadMissing('lines.projectAssignment');

        $lines = $report->lines;

        $manualLines = $lines
            ->reject(fn (MonthlyLoeReportLine $line): bool => $line->line_type === MonthlyLoeReportLineType::OpenToNewProjects)
            ->values();

        $this->recalculateManualLines($manualLines, $workingDays);

        $manualTotalHours = round($manualLines->sum('entered_hours'), 2);
        $manualTotalDays = round($manualLines->sum('calculated_days'), 2);
        $manualTotalPercentage = round($manualLines->sum('calculated_percentage'), 2);
        $timeOffHours = round($manualLines->where('line_type', MonthlyLoeReportLineType::TimeOff)->sum('entered_hours'), 2);
        $timeOffPercentage = round($manualLines->where('line_type', MonthlyLoeReportLineType::TimeOff)->sum('calculated_percentage'), 2);

        $openLine = $this->syncOpenToNewProjectsLine($report, $lines, $manualTotalPercentage, $workingDays, $openThreshold);

        $report->forceFill([
            'open_to_new_projects_hours' => $openLine?->entered_hours ?? 0,
            'open_to_new_projects_percentage' => $openLine?->calculated_percentage ?? 0,
            'time_off_hours' => $timeOffHours,
            'time_off_percentage' => $timeOffPercentage,
            'total_days' => round($manualTotalDays + ($openLine?->calculated_days ?? 0), 2),
            'total_hours' => round($manualTotalHours + ($openLine?->entered_hours ?? 0), 2),
            'total_percentage' => round($manualTotalPercentage + ($openLine?->calculated_percentage ?? 0), 2),
        ])->save();

        return $report->fresh('lines');
    }

    /**
     * @param  Collection<int, MonthlyLoeReportLine>  $lines
     */
    protected function recalculateManualLines(Collection $lines, int $workingDays): void
    {
        $lines->each(function (MonthlyLoeReportLine $line) use ($workingDays): void {
            $expectedPercentage = $line->projectAssignment?->expected_percentage;

            $line->forceFill([
                'calculated_days' => $this->loeValueCalculator->daysFromHours((float) $line->entered_hours),
                'calculated_percentage' => $this->loeValueCalculator->percentageFromHours((float) $line->entered_hours, $workingDays),
                'expected_percentage' => $expectedPercentage,
            ])->save();
        });
    }

    /**
     * @param  Collection<int, MonthlyLoeReportLine>  $lines
     */
    protected function syncOpenToNewProjectsLine(
        MonthlyLoeReport $report,
        Collection $lines,
        float $manualTotalPercentage,
        int $workingDays,
        float $openThreshold,
    ): ?MonthlyLoeReportLine {
        /** @var Collection<int, MonthlyLoeReportLine> $existingOpenLines */
        $existingOpenLines = $lines
            ->filter(fn (MonthlyLoeReportLine $line): bool => $line->line_type === MonthlyLoeReportLineType::OpenToNewProjects)
            ->values();

        $primaryOpenLine = $existingOpenLines->shift();

        $existingOpenLines->each->delete();

        if ($manualTotalPercentage >= $openThreshold) {
            $primaryOpenLine?->delete();

            return null;
        }

        $remainingPercentage = max(round(100 - $manualTotalPercentage, 2), 0);
        $remainingHours = $this->loeValueCalculator->hoursFromPercentage($remainingPercentage, $workingDays);
        $remainingDays = $this->loeValueCalculator->daysFromHours($remainingHours);

        $openLine = $primaryOpenLine ?? new MonthlyLoeReportLine([
            'monthly_loe_report_id' => $report->id,
            'line_type' => MonthlyLoeReportLineType::OpenToNewProjects,
        ]);

        $openLine->forceFill([
            'project_id' => null,
            'project_assignment_id' => null,
            'entered_hours' => $remainingHours,
            'calculated_days' => $remainingDays,
            'calculated_percentage' => $remainingPercentage,
            'expected_percentage' => null,
            'line_notes' => null,
            'sort_order' => 999999,
        ])->save();

        return $openLine;
    }
}
