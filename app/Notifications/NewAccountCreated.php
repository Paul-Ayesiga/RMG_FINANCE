<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAccountCreated extends Notification implements ShouldQueue
{
use Queueable;

protected string $accountName;
protected string $message;

/**
* Create a new notification instance.
*
* @param string $accountName
* @param string $message
*/
public function __construct(string $accountName, string $message)
{
    $this->accountName = $accountName;
    $this->message = $message;
}

/**
* Get the notification's delivery channels.
*
* @return array<int, string>
    */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
    * Get the mail representation of the notification.
    */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('New Account Created')
        ->greeting('Hello ' . $notifiable->name)
        ->line('A new account has been created for you.')
        ->line($this->message)
        ->action('View Your Account', url('/'))
        ->line('Thank you for using our application!');
    }

    /**
    * Get the database representation of the notification.
    */
    public function toDatabase(object $notifiable): array
    {
        return [
        'account_name' => $this->accountName,
        'message' => $this->message,
        'type' => 'new_account',
        'created_at' => now(),
    ];
    }

    /**
    * Get the broadcast representation of the notification.
    */
    

    /**
    * Get the array representation of the notification.
    */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Account Created',
            'message' => $this->message,
            'type' => 'new_account',
            ];
    }
    }
