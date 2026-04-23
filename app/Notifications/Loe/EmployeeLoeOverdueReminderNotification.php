<?php

namespace App\Notifications\Loe;

use App\Filament\Employee\Pages\MyReport;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeLoeOverdueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $periodLabel,
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
        return (new MailMessage)
            ->subject("Overdue LOE report for {$this->periodLabel}")
            ->line("Your Level of Effort report for {$this->periodLabel} is overdue.")
            ->line('The reporting grace period is still open, so please submit it before the month is closed.')
            ->action('Open my LoE report', route('loe.report'))
            ->line('Admins can manually close the month before the grace period ends.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title("Overdue LOE report for {$this->periodLabel}")
            ->danger()
            ->body("Your Level of Effort report for {$this->periodLabel} is overdue.")
            ->actions([
                Action::make('openReport')
                    ->button()
                    ->markAsRead()
                    ->url(MyReport::getUrl(panel: 'employee')),
            ])
            ->getDatabaseMessage() + [
                'period' => $this->periodLabel,
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
            'kind' => 'monthly_loe_overdue_reminder',
            'period' => $this->periodLabel,
            'message' => "Your LoE report for {$this->periodLabel} is overdue.",
        ];
    }
}
