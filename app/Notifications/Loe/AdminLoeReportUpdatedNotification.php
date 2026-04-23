<?php

namespace App\Notifications\Loe;

use App\Filament\Resources\MonthlyLoeReports\MonthlyLoeReportResource;
use App\Models\MonthlyLoeReport;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoeReportUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MonthlyLoeReport $report,
        public bool $returnedToDraft = false,
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
        $statusLabel = ucfirst($this->report->status->value);

        return (new MailMessage)
            ->subject("LoE report updated by {$employeeName}")
            ->line("{$employeeName} updated the LoE report for {$periodLabel}.")
            ->line("Current status: {$statusLabel}.")
            ->line($this->returnedToDraft
                ? 'The update moved the report back to draft for further edits.'
                : 'The report remains submitted after the latest changes.')
            ->action('Review report', MonthlyLoeReportResource::getUrl('index'))
            ->line('You are receiving this because you are an administrator.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'admin_loe_report_updated',
            'report_id' => $this->report->id,
            'employee_name' => $this->report->user->name,
            'period' => CarbonImmutable::create($this->report->report_year, $this->report->report_month, 1)->format('F Y'),
            'status' => $this->report->status->value,
            'returned_to_draft' => $this->returnedToDraft,
            'total_percentage' => $this->report->total_percentage,
        ];
    }
}
