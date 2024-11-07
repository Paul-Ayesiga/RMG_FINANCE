<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanScheduleReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schedule;
    protected $reminderType;

    /**
     * Create a new notification instance.
     */
    public function __construct($schedule, $reminderType)
    {
        $this->schedule = $schedule;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
       $message = new MailMessage();

        switch ($this->reminderType) {
            case 'first':
                $message->subject('Loan Payment Reminder (7-3 days)');
                // First reminder content
                break;
            case 'second':
                $message->subject('Loan Payment Reminder (3-1 days)');
                // Second reminder content
                break;
            case 'final':
                $message->subject('Loan Payment Due Tomorrow!');
                // Final reminder content
                break;
        }

        $message->line('Loan amount: ' . $this->schedule->amount_due)
            ->line('Due date: ' . $this->schedule->due_date)
            ->action('View Loan', url('/loans/' . $this->schedule->loan->id))
            ->line('Thank you for using our service!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
