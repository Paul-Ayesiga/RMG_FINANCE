<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;

class LoanApproved extends Notification implements ShouldQueue
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
        $product = $this->loan->loanProduct->name;
        
        return (new MailMessage)
            ->subject("Loan Application #{$this->loan->id} Approved")
            ->greeting('Hello ' . $notifiable->name)
            ->line("Your loan application #{$this->loan->id} has been approved.")
            ->line("Loan Details:")
            ->line("- Amount: {$amount}")
            ->line("- Product: {$product}")
            ->line("- Interest Rate: {$this->loan->interest_rate}%")
            ->line("- Term: {$this->loan->term} months")
            ->line("Please wait for the disbursement notification. The funds will be transferred to your account shortly.")
            ->action('View Loan Details', route('my-loans', $this->loan->id))
            ->line('Thank you for banking with us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Loan Approved',
            'message' => "Your loan application #{$this->loan->id} for {$this->loan->amount} has been approved.",
            'type' => 'success',
            'loan_id' => $this->loan->id
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
} 