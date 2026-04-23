<?php

namespace App\Console\Commands\Loe;

use App\MonthlyLoeClosureType;
use App\Services\Loe\MonthlyLoeClosureManager;
use App\Services\Loe\MonthlyLoeMonthLockService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('loe:auto-close-months')]
#[Description('Automatically close LoE months after the grace period ends.')]
class AutoCloseMonthlyLoeCommand extends Command
{
    public function handle(
        MonthlyLoeClosureManager $closureManager,
        MonthlyLoeMonthLockService $monthLockService,
    ): int
    {
        $today = CarbonImmutable::now();
        $candidates = collect([
            $today->subMonth(),
            $today->subMonths(2),
        ])->unique(fn (CarbonImmutable $date): string => $date->format('Y-m'));

        $closedCount = 0;

        foreach ($candidates as $candidate) {
            if ($monthLockService->isMonthClosed($candidate->year, $candidate->month)) {
                continue;
            }

            if ($today->lessThanOrEqualTo($monthLockService->closingDeadline($candidate->year, $candidate->month))) {
                continue;
            }

            $closureManager->closeMonth(
                $candidate->year,
                $candidate->month,
                MonthlyLoeClosureType::Automatic,
                notes: 'Automatically closed after the reporting grace period ended.',
            );

            $closedCount++;
        }

        $this->components->info("Auto-closed {$closedCount} LoE month(s).");

        return self::SUCCESS;
    }
}
