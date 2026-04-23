<?php

namespace App\Console\Commands\Loe;

use App\Models\User;
use App\Notifications\Loe\EmployeeLoeReminderNotification;
use App\Services\Loe\MonthlyLoeMonthLockService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('loe:send-reminders')]
#[Description('Send LoE reminders during the last three days of the current month.')]
class SendMonthlyLoeRemindersCommand extends Command
{
    public function handle(MonthlyLoeMonthLockService $monthLockService): int
    {
        $today = CarbonImmutable::now();

        if (($today->endOfMonth()->day - $today->day) > 2) {
            $this->components->info('Not within the final three calendar days of the month.');

            return self::SUCCESS;
        }

        if ($monthLockService->isMonthClosed($today->year, $today->month)) {
            $this->components->info('The current month is already closed.');

            return self::SUCCESS;
        }

        $periodLabel = $today->format('F Y');

        $users = User::query()
            ->where('is_active', true)
            ->whereDoesntHave('monthlyLoeReports', function ($query) use ($today): void {
                $query
                    ->where('report_year', $today->year)
                    ->where('report_month', $today->month)
                    ->where('status', \App\MonthlyLoeReportStatus::Submitted);
            })
            ->get();

        Notification::send($users, new EmployeeLoeReminderNotification($periodLabel));

        $this->components->info("Sent {$users->count()} monthly LoE reminders.");

        return self::SUCCESS;
    }
}
