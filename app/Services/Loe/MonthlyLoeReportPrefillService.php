<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeReport;
use App\MonthlyLoeReportLineType;
use Carbon\CarbonImmutable;

class MonthlyLoeReportPrefillService
{
    public function prefill(MonthlyLoeReport $report): MonthlyLoeReport
    {
        $report->loadMissing('lines');

        if ($report->lines->isNotEmpty()) {
            return $report;
        }

        $previousMonth = CarbonImmutable::create($report->report_year, $report->report_month, 1)->subMonth();

        $previousReport = MonthlyLoeReport::query()
            ->with('lines')
            ->where('user_id', $report->user_id)
            ->where('report_year', $previousMonth->year)
            ->where('report_month', $previousMonth->month)
            ->first();

        if ($previousReport === null) {
            return $report;
        }

        foreach ($previousReport->lines as $line) {
            if ($line->line_type === MonthlyLoeReportLineType::OpenToNewProjects) {
                continue;
            }

            $report->lines()->create([
                'line_type' => $line->line_type,
                'project_id' => $line->project_id,
                'project_assignment_id' => $line->project_assignment_id,
                'entered_hours' => $line->entered_hours,
                'calculated_days' => 0,
                'calculated_percentage' => 0,
                'expected_percentage' => $line->expected_percentage,
                'line_notes' => $line->line_notes,
                'sort_order' => $line->sort_order,
            ]);
        }

        return $report->fresh('lines');
    }
}
