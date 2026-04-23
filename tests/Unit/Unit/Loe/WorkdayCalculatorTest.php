<?php

use App\Services\Loe\WorkdayCalculator;
use Carbon\CarbonImmutable;

test('it excludes weekends public holidays and days before joining date', function () {
    $calculator = new WorkdayCalculator();

    $workingDays = $calculator->workingDaysInMonth(
        2026,
        4,
        publicHolidayDates: ['2026-04-10'],
        joiningDate: CarbonImmutable::parse('2026-04-15'),
    );

    expect($workingDays)->toBe(13);
    expect($calculator->availableHoursInMonth(
        2026,
        4,
        publicHolidayDates: ['2026-04-10'],
        joiningDate: CarbonImmutable::parse('2026-04-15'),
    ))->toBe(104);
});
