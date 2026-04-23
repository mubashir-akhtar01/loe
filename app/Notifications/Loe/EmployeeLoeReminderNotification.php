<?php

namespace App\Notifications\Loe;

use App\Filament\Employee\Pages\MyReport;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeLoeReminderNotification extends Notification implements ShouldQueue
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
            ->subject("LOE reminder for {$this->periodLabel}")
            ->line("Please submit your Level of Effort report for {$this->periodLabel}.")
            ->line('This reminder is only sent because your report has not been submitted yet.')
            ->action('Open my LoE report', route('loe.report'))
            ->line('Once you submit the report, these reminder notifications will stop.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title("LOE reminder for {$this->periodLabel}")
            ->warning()
            ->body("Please submit your Level of Effort report for {$this->periodLabel}.")
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
            'kind' => 'monthly_loe_reminder',
            'period' => $this->periodLabel,
            'message' => "Submit your LOE report for {$this->periodLabel}.",
        ];
    }
}
