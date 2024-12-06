<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Account;
use Livewire\Livewire;
use Illuminate\Broadcasting\PrivateChannel;

class AccountStatusNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Account $account,
        protected string $title,
        protected string $message,
        protected string $status,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->message)
            ->line($this->getStatusSpecificMessage())
            ->line('Thank you for banking with us!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'account_id' => $this->account->id,
            'account_number' => $this->account->account_number,
            'title' => $this->title,
            'message' => $this->message,
            'status' => $this->status,
            'type' => 'account_status',
            'created_at' => now(),
        ];
    }

    private function getStatusSpecificMessage(): string
    {
        return match ($this->status) {
            'active' => 'You can now access all account features.',
            'inactive' => 'If you need assistance, please contact our support team.',
            'closed' => 'Your account has been permanently closed.',
            default => 'Please contact support if you have any questions.',
        };
    }


    public function toArray($notifiable): array
    {
        $data = [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->status
        ];

        return $data;
    }
}
