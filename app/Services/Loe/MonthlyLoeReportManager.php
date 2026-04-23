<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\Notifications\Loe\AdminLoeReportAlertNotification;
use App\Notifications\Loe\AdminLoeReportSubmittedNotification;
use App\Notifications\Loe\AdminLoeReportUpdatedNotification;
use App\MonthlyLoeReportActivityAction;
use App\MonthlyLoeReportLineType;
use App\MonthlyLoeReportStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MonthlyLoeReportManager
{
    public function __construct(
        protected MonthlyLoeMonthLockService $monthLockService,
        protected MonthlyLoeReportActivityLogger $activityLogger,
        protected MonthlyLoeReportCalculator $reportCalculator,
        protected MonthlyLoeReportPrefillService $reportPrefillService,
        protected WorkdayCalculator $workdayCalculator,
    ) {
    }

    public function getOrCreateForMonth(User $user, int $year, int $month): MonthlyLoeReport
    {
        $report = MonthlyLoeReport::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'report_year' => $year,
                'report_month' => $month,
            ],
            [
                'department_id' => $user->department_id,
                'status' => MonthlyLoeReportStatus::Draft,
            ],
        );

        if ($report->wasRecentlyCreated) {
            $this->activityLogger->log(
                $report,
                MonthlyLoeReportActivityAction::Created,
                $user,
                'Monthly LoE report created.',
            );
        }

        $report->loadMissing('lines');

        if ($report->lines->isEmpty()) {
            $prefilledReport = $this->reportPrefillService->prefill($report);

            if ($prefilledReport->lines->isNotEmpty()) {
                $this->activityLogger->log(
                    $prefilledReport,
                    MonthlyLoeReportActivityAction::Prefilled,
                    $user,
                    'Monthly LoE report prefilled from previous month.',
                );
            }
        }

        return $this->recalculate($report->fresh('lines'), $user);
    }

    /**
     * @param  array<int|string, mixed>  $projectHours
     * @param  array<int|string, mixed>  $projectNotes
     */
    public function saveReport(
        MonthlyLoeReport $report,
        User $user,
        array $projectHours,
        array $projectNotes,
        float $timeOffHours,
        ?string $timeOffNotes,
        ?string $reportNotes,
        bool $submit = false,
    ): MonthlyLoeReport {
        if ($this->monthLockService->isLocked($report->report_year, $report->report_month)) {
            abort(403, 'This month is locked.');
        }

        return DB::transaction(function () use (
            $projectHours,
            $projectNotes,
            $report,
            $reportNotes,
            $submit,
            $timeOffHours,
            $timeOffNotes,
            $user,
        ): MonthlyLoeReport {
            $wasSubmitted = $report->status === MonthlyLoeReportStatus::Submitted;

            $activeAssignments = $user->projectAssignments()
                ->with('project')
                ->where('is_active', true)
                ->where(function ($query): void {
                    $today = now()->toDateString();

                    $query
                        ->whereNull('starts_on')
                        ->orWhere('starts_on', '<=', $today);
                })
                ->where(function ($query): void {
                    $today = now()->toDateString();

                    $query
                        ->whereNull('ends_on')
                        ->orWhere('ends_on', '>=', $today);
                })
                ->get()
                ->keyBy('id');

            $existingProjectLines = $report->lines()
                ->where('line_type', MonthlyLoeReportLineType::Project)
                ->get()
                ->keyBy('project_assignment_id');

            foreach ($activeAssignments as $assignmentId => $assignment) {
                $hours = round((float) ($projectHours[$assignmentId] ?? 0), 2);
                $notes = trim((string) ($projectNotes[$assignmentId] ?? ''));

                $line = $existingProjectLines->get($assignmentId);

                if ($hours <= 0 && $notes === '') {
                    $line?->delete();

                    continue;
                }

                $attributes = [
                    'project_id' => $assignment->project_id,
                    'entered_hours' => $hours,
                    'line_notes' => $notes !== '' ? $notes : null,
                    'sort_order' => (int) $assignmentId,
                ];

                if ($line === null) {
                    $report->lines()->create([
                        'line_type' => MonthlyLoeReportLineType::Project,
                        'project_assignment_id' => $assignment->id,
                        ...$attributes,
                    ]);

                    continue;
                }

                $line->update($attributes);
            }

            $report->lines()
                ->where('line_type', MonthlyLoeReportLineType::Project)
                ->whereNotIn('project_assignment_id', $activeAssignments->keys())
                ->delete();

            $timeOffLine = $report->lines()
                ->where('line_type', MonthlyLoeReportLineType::TimeOff)
                ->first();

            if ($timeOffHours <= 0 && blank($timeOffNotes)) {
                $timeOffLine?->delete();
            } elseif ($timeOffLine === null) {
                $report->lines()->create([
                    'line_type' => MonthlyLoeReportLineType::TimeOff,
                    'entered_hours' => round($timeOffHours, 2),
                    'line_notes' => filled($timeOffNotes) ? trim($timeOffNotes) : null,
                    'sort_order' => 500000,
                ]);
            } else {
                $timeOffLine->update([
                    'entered_hours' => round($timeOffHours, 2),
                    'line_notes' => filled($timeOffNotes) ? trim($timeOffNotes) : null,
                ]);
            }

            $report->forceFill([
                'department_id' => $user->department_id,
                'report_notes' => filled($reportNotes) ? trim($reportNotes) : null,
                'status' => $submit ? MonthlyLoeReportStatus::Submitted : MonthlyLoeReportStatus::Draft,
                'submitted_at' => $submit ? now() : null,
            ])->save();

            $updatedReport = $this->recalculate($report->fresh('lines'), $user);

            $this->activityLogger->log(
                $updatedReport,
                $submit ? MonthlyLoeReportActivityAction::Submitted : MonthlyLoeReportActivityAction::Updated,
                $user,
                $submit ? 'Monthly LoE report submitted.' : 'Monthly LoE report saved as draft.',
                [
                    'status' => $updatedReport->status->value,
                    'total_percentage' => $updatedReport->total_percentage,
                ],
            );

            $this->notifyAdmins($updatedReport->loadMissing(['lines.project', 'user']), $wasSubmitted, $submit);

            return $updatedReport;
        });
    }

    protected function notifyAdmins(MonthlyLoeReport $report, bool $wasSubmitted, bool $submit): void
    {
        $admins = User::query()
            ->where('role', \App\UserRole::Admin)
            ->where('is_active', true)
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        if ($submit && ! $wasSubmitted) {
            Notification::send($admins, new AdminLoeReportSubmittedNotification($report));
            $this->activityLogger->log(
                $report,
                MonthlyLoeReportActivityAction::NotificationSent,
                null,
                'Admins notified about submitted LoE report.',
                ['notification' => 'submitted'],
            );
        }

        if ($wasSubmitted) {
            Notification::send($admins, new AdminLoeReportUpdatedNotification($report, returnedToDraft: ! $submit));
            $this->activityLogger->log(
                $report,
                MonthlyLoeReportActivityAction::NotificationSent,
                null,
                'Admins notified about updated LoE report.',
                [
                    'notification' => 'updated',
                    'returned_to_draft' => ! $submit,
                ],
            );
        }

        $alerts = $this->alertsForReport($report);

        if ($alerts === []) {
            return;
        }

        Notification::send($admins, new AdminLoeReportAlertNotification($report, $alerts));
        $this->activityLogger->log(
            $report,
            MonthlyLoeReportActivityAction::NotificationSent,
            null,
            'Admins notified about LoE allocation alerts.',
            [
                'notification' => 'alerts',
                'alerts' => $alerts,
            ],
        );
    }

    /**
     * @return array<int, string>
     */
    protected function alertsForReport(MonthlyLoeReport $report): array
    {
        $alerts = [];
        $totalPercentage = (float) $report->total_percentage;

        if ($totalPercentage < 100) {
            $alerts[] = 'Total allocation is below 100%.';
        }

        if ($totalPercentage > 100) {
            $alerts[] = 'Total allocation is above 100%.';
        }

        foreach ($report->lines as $line) {
            if ($line->line_type !== MonthlyLoeReportLineType::Project || $line->expected_percentage === null) {
                continue;
            }

            if ((float) $line->calculated_percentage <= (float) $line->expected_percentage) {
                continue;
            }

            $alerts[] = sprintf(
                '%s exceeded expected allocation (actual %s%% vs expected %s%%).',
                $line->project?->name ?? 'A project',
                number_format((float) $line->calculated_percentage, 2),
                number_format((float) $line->expected_percentage, 2),
            );
        }

        return array_values(array_unique($alerts));
    }

    protected function recalculate(MonthlyLoeReport $report, User $user): MonthlyLoeReport
    {
        $holidayDates = DB::table('public_holidays')
            ->whereYear('holiday_date', $report->report_year)
            ->whereMonth('holiday_date', $report->report_month)
            ->pluck('holiday_date');

        $workingDays = $this->workdayCalculator->workingDaysInMonth(
            $report->report_year,
            $report->report_month,
            $holidayDates,
            $user->joining_date,
        );

        return $this->reportCalculator->recalculate($report, $workingDays);
    }
}
