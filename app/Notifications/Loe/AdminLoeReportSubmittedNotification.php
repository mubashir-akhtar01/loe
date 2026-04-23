<?php

namespace App\Notifications\Loe;

use App\Filament\Resources\MonthlyLoeReports\MonthlyLoeReportResource;
use App\Models\MonthlyLoeReport;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminLoeReportSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MonthlyLoeReport $report,
    ) {
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $employeeName = $this->report->user->name;
        $periodLabel = CarbonImmutable::create($this->report->report_year, $this->report->report_month, 1)->format('F Y');

        return (new MailMessage)
            ->subject("LoE report submitted by {$employeeName}")
            ->line("{$employeeName} submitted a LoE report for {$periodLabel}.")
            ->line('Total allocation: '.number_format((float) $this->report->total_percentage, 2).'%.')
            ->action('Review reports', MonthlyLoeReportResource::getUrl('index'))
            ->line('You are receiving this because you are an administrator.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $employeeName = $this->report->user->name;
        $periodLabel = CarbonImmutable::create($this->report->report_year, $this->report->report_month, 1)->format('F Y');

        return FilamentNotification::make()
            ->title("LoE report submitted by {$employeeName}")
            ->success()
            ->body("{$employeeName} submitted a report for {$periodLabel}.")
            ->actions([
                Action::make('reviewReports')
                    ->button()
                    ->markAsRead()
                    ->url(MonthlyLoeReportResource::getUrl('index')),
            ])
            ->getDatabaseMessage() + [
                'period' => $periodLabel,
                'report_id' => $this->report->id,
            ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'admin_loe_report_submitted',
            'report_id' => $this->report->id,
            'employee_name' => $this->report->user->name,
            'period' => CarbonImmutable::create($this->report->report_year, $this->report->report_month, 1)->format('F Y'),
            'total_percentage' => $this->report->total_percentage,
        ];
    }
}
