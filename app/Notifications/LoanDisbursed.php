<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;

class LoanDisbursed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Loan $loan)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        $amount = number_format($this->loan->amount, 2);
        $accountNumber = $this->loan->account->account_number;
        $disbursementDate = $this->loan->disbursement_date->format('Y-m-d');
        $firstPaymentDate = $this->loan->schedules->first()->due_date->format('Y-m-d');

        return (new MailMessage)
            ->subject("Loan #{$this->loan->id} Disbursed")
            ->greeting('Hello ' . $notifiable->name)
            ->line("Your loan #{$this->loan->id} has been successfully disbursed.")
            ->line("Disbursement Details:")
            ->line("- Amount: {$amount}")
            ->line("- Account Number: {$accountNumber}")
            ->line("- Disbursement Date: {$disbursementDate}")
            ->line("Your first payment is due on {$firstPaymentDate}")
            ->action('View Loan Details', route('my-loans', $this->loan->id))
            ->line('Thank you for banking with us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Loan Disbursed',
            'message' => "Your loan #{$this->loan->id} for {$this->loan->amount} has been disbursed to your account.",
            'type' => 'success',
            'loan_id' => $this->loan->id
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
} 