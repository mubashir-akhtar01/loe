<?php

namespace App\Services\Loe;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class WorkdayCalculator
{
    public function availableHoursInMonth(
        int $year,
        int $month,
        iterable $publicHolidayDates = [],
        ?CarbonInterface $joiningDate = null,
    ): int {
        return $this->workingDaysInMonth($year, $month, $publicHolidayDates, $joiningDate) * LoeValueCalculator::HOURS_PER_DAY;
    }

    /**
     * @param  iterable<int, CarbonInterface|string>  $publicHolidayDates
     * @return Collection<int, CarbonImmutable>
     */
    public function workingDatesForMonth(
        int $year,
        int $month,
        iterable $publicHolidayDates = [],
        ?CarbonInterface $joiningDate = null,
    ): Collection {
        $monthStart = CarbonImmutable::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->endOfMonth();

        if ($joiningDate !== null && $joiningDate->greaterThan($monthEnd)) {
            return collect();
        }

        $effectiveStart = $joiningDate === null
            ? $monthStart
            : CarbonImmutable::instance($joiningDate)->startOfDay()->max($monthStart);

        $excludedDates = collect($publicHolidayDates)
            ->map(fn (CarbonInterface|string $date): string => CarbonImmutable::parse($date)->toDateString())
            ->unique();

        return collect($effectiveStart->daysUntil($monthEnd->addDay()))
            ->filter(function (CarbonImmutable $date) use ($excludedDates): bool {
                if ($date->isSaturday() || $date->isSunday()) {
                    return false;
                }

                return ! $excludedDates->contains($date->toDateString());
            })
            ->values();
    }

    /**
     * @param  iterable<int, CarbonInterface|string>  $publicHolidayDates
     */
    public function workingDaysInMonth(
        int $year,
        int $month,
        iterable $publicHolidayDates = [],
        ?CarbonInterface $joiningDate = null,
    ): int {
        return $this->workingDatesForMonth($year, $month, $publicHolidayDates, $joiningDate)->count();
    }
}
