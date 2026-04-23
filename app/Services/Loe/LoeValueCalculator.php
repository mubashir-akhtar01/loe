<?php

namespace App\Services\Loe;

class LoeValueCalculator
{
    public const HOURS_PER_DAY = 8;

    public function daysFromHours(float $hours): float
    {
        return round($hours / self::HOURS_PER_DAY, 2);
    }

    public function hoursFromPercentage(float $percentage, int $workingDays): float
    {
        $availableHours = $workingDays * self::HOURS_PER_DAY;

        if ($availableHours <= 0) {
            return 0.0;
        }

        return round(($percentage / 100) * $availableHours, 2);
    }

    public function percentageFromHours(float $hours, int $workingDays): float
    {
        $availableHours = $workingDays * self::HOURS_PER_DAY;

        if ($availableHours <= 0) {
            return 0.0;
        }

        return round(($hours / $availableHours) * 100, 2);
    }
}
