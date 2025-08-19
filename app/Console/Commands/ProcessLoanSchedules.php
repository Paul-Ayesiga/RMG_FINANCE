<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentSchedule;
use App\Models\Loan;
use App\Models\Transaction;
use App\Notifications\TransactionNotification;
use Carbon\Carbon;

class ProcessLoanSchedules extends Command
{
    protected $signature = 'loans:process-schedules';
    protected $description = 'Process loan schedules and deduct payments on due dates';

    public function handle()
    {
        $now = Carbon::now(); // Get current date and time

        // Get schedules that are due now or overdue and not paid
        $schedules = PaymentSchedule::where('status', '!=', 'paid')
            ->where('due_date', '<=', $now) // Compare with current date and time
            ->with(['loan', 'loan.product', 'loan.account.customer.user']) // Eager-load relationships
            ->get();

        foreach ($schedules as $schedule) {
            $account = $schedule->loan->account;

            if ($account->balance >= $schedule->remaining_amount) {
                // Sufficient funds: Process payment
                $this->processPayment($schedule, $account);
            } else {
                // Insufficient funds: Mark as late and apply late fee
                $this->markLate($schedule);
            }
        }
    }

    private function processPayment($schedule, $account)
    {
        $amount = $schedule->remaining_amount;
        $now = now(); // Get current timestamp

        // Deduct from account balance
        $account->balance -= $amount;
        $account->save();

        // Create transaction record
        $transaction = $account->transactions()->create([
            'type' => 'loanPayment',
            'amount' => $amount,
            'reference_number' => 'LOAN-PMT-' . time(),
            'description' => "Scheduled loan payment for Loan #{$schedule->loan_id}",
            'status' => 'completed',
        ]);

        // Update schedule
        $schedule->update([
            'paid_amount' => $amount,
            'remaining_amount' => 0,
            'status' => 'paid',
            'paid_at' => $now,
        ]);

        // Handle early payment fee if applicable
        $this->applyEarlyPaymentFee($schedule, $transaction);

        // Update loan status if all schedules are paid
        if ($schedule->loan->schedules()->where('status', '!=', 'paid')->count() === 0) {
            $schedule->loan->update([
                'status' => 'paid',
                'closed_at' => $now,
            ]);
        }

        // Notify customer
        $schedule->loan->account->customer->user->notify(new TransactionNotification(
            $transaction,
            'Scheduled Payment Processed',
            sprintf(
                'Scheduled payment of %s for Loan #%d was processed successfully. Account balance: %s.',
                number_format($amount, 2),
                $schedule->loan->id,
                number_format($account->balance, 2)
            )
        ));

        $this->info("Payment processed for Schedule #{$schedule->id}");
    }

    private function markLate($schedule)
    {
        $now = Carbon::now();
        $dueDate = Carbon::parse($schedule->due_date); // Ensure $due_date is a Carbon instance

        // If the payment is late
        if ($dueDate->lt($now)) {
            $loanProduct = $schedule->loan->product;

            // Apply late payment fee
            $lateFee = $loanProduct->late_payment_fee_percentage
                ? ($schedule->remaining_amount * $loanProduct->late_payment_fee_percentage / 100)
                : 0;

            $schedule->update([
                'status' => 'late',
                'late_fee' => $lateFee,
            ]);

            // Notify customer of insufficient funds
            $schedule->loan->account->customer->user->notify(new TransactionNotification(
                null,
                'Late Payment Notice',
                sprintf(
                    'Scheduled payment of %s for Loan #%d could not be processed due to insufficient funds. A late fee of %s has been added.',
                    number_format($schedule->remaining_amount, 2),
                    $schedule->loan->id,
                    number_format($lateFee, 2)
                )
            ));

            $this->warn("Schedule #{$schedule->id} marked as late due to insufficient funds.");
        }
    }

    private function applyEarlyPaymentFee($schedule, $transaction)
    {
        $loanProduct = $schedule->loan->product;

        // Check if payment is early
        if ($schedule->paid_at && $schedule->paid_at->lt($schedule->due_date)) {
            $earlyFee = $loanProduct->early_payment_fee_percentage
                ? ($schedule->remaining_amount * $loanProduct->early_payment_fee_percentage / 100)
                : 0;

            // Update transaction and schedule for early fee
            $transaction->update([
                'amount' => $transaction->amount + $earlyFee,
                'description' => "{$transaction->description} (Early Payment Fee Applied)"
            ]);

            $schedule->update([
                'remaining_amount' => $schedule->remaining_amount + $earlyFee,
            ]);

            // Notify customer
            $schedule->loan->account->customer->user->notify(new TransactionNotification(
                $transaction,
                'Early Payment Fee Applied',
                sprintf(
                    'An early payment fee of %s was applied for Loan #%d. Thank you for paying early!',
                    number_format($earlyFee, 2),
                    $schedule->loan->id
                )
            ));

            $this->info("Early payment fee applied for Schedule #{$schedule->id}");
        }
    }
}
