<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Transaction;

class TransactionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected ?Transaction $transaction;
    protected string $title;
    protected string $message;

    public function __construct(
        ?Transaction $transaction,
        string $title,
        string $message
    ) {
        $this->transaction = $transaction;
        $this->title = $title;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->message);

        if ($this->transaction) {
            $mail->line('Transaction Details:')
                ->line('- Reference: ' . $this->transaction->reference_number)
                ->line('- Amount: ' . number_format($this->transaction->account->balance, 2))
                ->when($this->transaction->charges > 0, function ($mail) {
                    $mail->line('- Charges: ' . number_format($this->transaction->charges, 2));
                    if ($this->transaction->charges_breakdown) {
                        foreach (json_decode($this->transaction->charges_breakdown, true) as $charge) {
                            $mail->line('  • ' . $charge['name'] . ': ' . number_format($charge['amount'], 2));
                        }
                    }
                    return $mail;
                })
                ->when($this->transaction->taxes > 0, function ($mail) {
                    $mail->line('- Taxes: ' . number_format($this->transaction->taxes, 2));
                    if ($this->transaction->taxes_breakdown) {
                        foreach (json_decode($this->transaction->taxes_breakdown, true) as $tax) {
                            $mail->line('  • ' . $tax['name'] . ': ' . number_format($tax['amount'], 2));
                        }
                    }
                    return $mail;
                })
                ->line('- Total Amount: ' . number_format($this->transaction->total_amount, 2))
                ->line('- Date: ' . $this->transaction->created_at->format('Y-m-d H:i:s'));
        }

        return $mail->line('Thank you for banking with us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'transaction_id' => $this->transaction?->id,
            'type' => $this->transaction?->type,
            'amount' => $this->transaction?->amount,
            'reference' => $this->transaction?->reference_number,
            'created_at' => $this->transaction?->created_at
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
