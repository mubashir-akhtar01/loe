<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeClosure;
use App\Models\MonthlyLoeReport;
use App\Models\User;
use App\MonthlyLoeClosureType;
use App\MonthlyLoeReportActivityAction;
use App\MonthlyLoeReportStatus;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class MonthlyLoeClosureManager
{
    public function __construct(
        protected MonthlyLoeMonthLockService $monthLockService,
        protected MonthlyLoeReportActivityLogger $activityLogger,
    ) {
    }

    public function canManuallyClose(int $year, int $month, ?CarbonInterface $now = null): bool
    {
        if ($this->monthLockService->isMonthClosed($year, $month)) {
            return false;
        }

        $comparison = $now ? CarbonImmutable::instance($now) : CarbonImmutable::now();
        $monthEnd = CarbonImmutable::create($year, $month, 1)->endOfMonth()->endOfDay();
        $deadline = $this->monthLockService->closingDeadline($year, $month);

        return $comparison->greaterThan($monthEnd) && $comparison->lessThanOrEqualTo($deadline);
    }

    public function closeMonth(
        int $year,
        int $month,
        MonthlyLoeClosureType $closureType,
        ?User $actor = null,
        ?string $notes = null,
        ?CarbonInterface $closedAt = null,
    ): MonthlyLoeClosure {
        return DB::transaction(function () use ($actor, $closedAt, $closureType, $month, $notes, $year): MonthlyLoeClosure {
            $closure = MonthlyLoeClosure::query()->firstOrCreate(
                [
                    'closure_year' => $year,
                    'closure_month' => $month,
                ],
                [
                    'closed_by_user_id' => $actor?->id,
                    'closure_type' => $closureType,
                    'closed_at' => $closedAt ?? now(),
                    'notes' => $notes,
                ],
            );

            $reports = MonthlyLoeReport::query()
                ->where('report_year', $year)
                ->where('report_month', $month)
                ->get();

            $reports->each(function (MonthlyLoeReport $report) use ($actor, $closure): void {
                $report->forceFill([
                    'status' => MonthlyLoeReportStatus::Closed,
                    'closed_at' => $closure->closed_at,
                ])->save();

                $this->activityLogger->log(
                    $report,
                    MonthlyLoeReportActivityAction::Closed,
                    $actor,
                    'Monthly LoE report closed.',
                    [
                        'closure_type' => $closure->closure_type->value,
                    ],
                );
            });

            return $closure;
        });
    }
}
