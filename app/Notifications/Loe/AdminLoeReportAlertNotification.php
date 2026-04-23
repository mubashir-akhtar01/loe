<?php

namespace App\Notifications\Loe;

use App\Filament\Resources\MonthlyLoeReports\MonthlyLoeReportResource;
use App\Models\MonthlyLoeReport;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoeReportAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $alerts
     */
    public function __construct(
        public MonthlyLoeReport $report,
        public array $alerts,
    ) {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = $this->report->user->name;
        $periodLabel = CarbonImmutable::create($this->report->report_year, $this->report->report_month, 1)->format('F Y');

        $message = (new MailMessage)
            ->subject("LoE alert for {$employeeName}")
            ->line("The {$periodLabel} LoE report for {$employeeName} needs attention.")
            ->line('Total allocation: ' . number_format((float) $this->report->total_percentage, 2) . '%.');

        foreach ($this->alerts as $alert) {
            $message->line("- {$alert}");
        }

        return $message
            ->action('Review report', MonthlyLoeReportResource::getUrl('index'))
            ->line('You are receiving this because you are an administrator.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'admin_loe_report_alert',
            'report_id' => $this->report->id,
            'employee_name' => $this->report->user->name,
            'period' => CarbonImmutable::create($this->report->report_year, $this->report->report_month, 1)->format('F Y'),
            'total_percentage' => $this->report->total_percentage,
            'alerts' => $this->alerts,
        ];
    }
}
