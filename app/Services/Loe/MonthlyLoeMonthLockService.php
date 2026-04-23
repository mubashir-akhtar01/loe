<?php

namespace App\Services\Loe;

use App\Models\MonthlyLoeClosure;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class MonthlyLoeMonthLockService
{
    public function closingDeadline(int $year, int $month): CarbonImmutable
    {
        return CarbonImmutable::create($year, $month, 1)
            ->endOfMonth()
            ->addDays(3)
            ->endOfDay();
    }

    public function isEditable(int $year, int $month, ?CarbonInterface $now = null): bool
    {
        return ! $this->isLocked($year, $month, $now);
    }

    public function isLocked(int $year, int $month, ?CarbonInterface $now = null): bool
    {
        if ($this->isMonthClosed($year, $month)) {
            return true;
        }

        $comparisonPoint = $now === null
            ? CarbonImmutable::now()
            : CarbonImmutable::instance($now);

        return $comparisonPoint->greaterThan($this->closingDeadline($year, $month));
    }

    public function isMonthClosed(int $year, int $month): bool
    {
        return MonthlyLoeClosure::query()
            ->where('closure_year', $year)
            ->where('closure_month', $month)
            ->exists();
    }
}
