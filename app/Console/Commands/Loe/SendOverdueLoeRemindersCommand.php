<?php

namespace App\Console\Commands\Loe;

use App\Models\User;
use App\Notifications\Loe\EmployeeLoeOverdueReminderNotification;
use App\Services\Loe\MonthlyLoeMonthLockService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('loe:send-overdue-reminders')]
#[Description('Send overdue LoE reminders during the grace period for the previous month.')]
class SendOverdueLoeRemindersCommand extends Command
{
    public function handle(MonthlyLoeMonthLockService $monthLockService): int
    {
        $today = CarbonImmutable::now();

        if ($today->day > 3) {
            $this->components->info('Not within the overdue grace period.');

            return self::SUCCESS;
        }

        $previousMonth = $today->subMonth();

        if ($monthLockService->isMonthClosed($previousMonth->year, $previousMonth->month)) {
            $this->components->info('The previous month is already closed.');

            return self::SUCCESS;
        }

        $users = User::query()
            ->where('is_active', true)
            ->whereDoesntHave('monthlyLoeReports', function ($query) use ($previousMonth): void {
                $query
                    ->where('report_year', $previousMonth->year)
                    ->where('report_month', $previousMonth->month)
                    ->where('status', \App\MonthlyLoeReportStatus::Submitted);
            })
            ->get();

        Notification::send($users, new EmployeeLoeOverdueReminderNotification($previousMonth->format('F Y')));

        $this->components->info("Sent {$users->count()} overdue LoE reminders.");

        return self::SUCCESS;
    }
}
