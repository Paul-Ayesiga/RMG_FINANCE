<?php

namespace App\Livewire\CustomerFolder\MyLoans;

use App\Models\Loan;
use Livewire\Component;
use WireUi\Traits\WireUiActions;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\Account;
use App\Notifications\TransactionNotification;
use Livewire\Attributes\Lazy;
use Mary\Traits\Toast;


#[Lazy()]
class VisitLoan extends Component
{
    use WireUiActions;
    use Toast;

    public ?Loan $loan;

    #[Validate('required')]
    public ?int $accountId = null;

    public Collection $accounts;

    public ?int $selectedAccount = null;
    public Collection $userAccounts;

    public bool $isFullPayment = false;

    public bool $showReceiptModal = false;
    public $receiptData = null;
    public $receiptType = null;

    // Add these new properties
    #[Validate('required|numeric|min:0.01')]
    public $paymentAmount;

    #[Validate('required|string|regex:/^[0-9]{16}$/')]
    public $cardNumber;

    #[Validate('required|string|regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/')]
    public $cardExpiry;

    #[Validate('required|string|regex:/^[0-9]{3,4}$/')]
    public $cardCvv;

    #[Validate('required|string|regex:/^[0-9]{10}$/')]
    public $mobileMoneyNumber;

    #[On('refresh')]
    public function mount()
    {
        $this->accounts = collect();
        $this->searchLoanToPaymentAccounts();
    }

    public function searchLoanToPaymentAccounts(string $value = '')
    {
        $selectedAccount = Account::where('id', $this->selectedAccount)->get();

        $this->userAccounts = Account::query()
            ->where('customer_id', Auth::user()->customer->id)
            ->where('status', 'active')
            ->when($value, fn($query) => $query->where('account_number', 'like', "%$value%"))
            ->take(5)
            ->orderBy('account_number')
            ->get()
            ->merge($selectedAccount);
    }


    public function makePaymentFromAccount()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'selectedAccount' => 'required|exists:accounts,id',
        ]);

        $account = Account::findOrFail($this->selectedAccount);

        // Calculate total remaining amount for all unpaid schedules
        $unpaidSchedules = $this->loan->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->get();

        $totalRemainingAmount = $unpaidSchedules->sum('remaining_amount');

        // Check if this is a full repayment
        $isFullRepayment = $this->paymentAmount >= $totalRemainingAmount;

        // If amount is more than total remaining, adjust it to exact amount
        if ($isFullRepayment) {
            $this->paymentAmount = $totalRemainingAmount;
        } else {
            // Partial payment logic
            $now = now();
            $remainingPaymentAmount = $this->paymentAmount;
            $paymentReference = 'PART-PAY-' . time();

            // First handle any partially paid schedules
            $partialSchedules = $unpaidSchedules->where('status', 'partial');
            foreach ($partialSchedules as $schedule) {
                if ($remainingPaymentAmount <= 0) break;

                $scheduleRemainingAmount = $schedule->remaining_amount;
                $amountToPayForSchedule = min($remainingPaymentAmount, $scheduleRemainingAmount);

                // Create payment record for this schedule
                $payment = $this->loan->payments()->create([
                    'loan_id' => $this->loan->id,
                    'payment_schedule_id' => $schedule->id,
                    'amount' => $amountToPayForSchedule,
                    'payment_method' => 'account',
                    'reference_number' => $paymentReference,
                    'status' => 'completed',
                    'notes' => sprintf(
                        'Partial payment of %s made from account %s',
                        number_format($amountToPayForSchedule, 2),
                        $account->account_number
                    ),
                ]);

                // Update schedule
                $schedule->update([
                    'paid_amount' => $schedule->paid_amount + $amountToPayForSchedule,
                    'remaining_amount' => $schedule->remaining_amount - $amountToPayForSchedule,
                    'status' => ($schedule->remaining_amount - $amountToPayForSchedule) <= 0 ? 'paid' : 'partial',
                    'paid_at' => $schedule->paid_at ?? $now
                ]);

                $remainingPaymentAmount -= $amountToPayForSchedule;
            }

            // Then handle unpaid schedules if there's still remaining payment amount
            $unpaidOnlySchedules = $unpaidSchedules->where('status', '!=', 'partial');
            foreach ($unpaidOnlySchedules as $schedule) {
                if ($remainingPaymentAmount <= 0) break;

                $scheduleRemainingAmount = $schedule->remaining_amount;
                $amountToPayForSchedule = min($remainingPaymentAmount, $scheduleRemainingAmount);

                // Create payment record for this schedule
                $payment = $this->loan->payments()->create([
                    'loan_id' => $this->loan->id,
                    'payment_schedule_id' => $schedule->id,
                    'amount' => $amountToPayForSchedule,
                    'payment_method' => 'account',
                    'reference_number' => $paymentReference,
                    'status' => 'completed',
                    'notes' => sprintf(
                        'Partial payment of %s made from account %s',
                        number_format($amountToPayForSchedule, 2),
                        $account->account_number
                    ),
                ]);

                // Update schedule
                $schedule->update([
                    'paid_amount' => $schedule->paid_amount + $amountToPayForSchedule,
                    'remaining_amount' => $schedule->remaining_amount - $amountToPayForSchedule,
                    'status' => ($schedule->remaining_amount - $amountToPayForSchedule) <= 0 ? 'paid' : 'partial',
                    'paid_at' => $schedule->paid_at ?? $now
                ]);

                $remainingPaymentAmount -= $amountToPayForSchedule;
            }

            // Check if all schedules are paid to update loan status
            $unpaidSchedulesCount = $this->loan->schedules()
                ->where('status', '!=', 'paid')
                ->count();

            if ($unpaidSchedulesCount === 0) {
                $this->loan->update([
                    'status' => 'paid',
                    'closed_at' => $now
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $now = now();
            $totalAmount = $this->paymentAmount;

            // Create withdrawal transaction
            $transaction = $account->transactions()->create([
                'type' => 'loanPayment',
                'amount' => $totalAmount,
                'reference_number' => 'LOAN-PMT-' . time(),
                'description' => "Loan repayment for Loan #{$this->loan->id}",
                'status' => 'completed',
                'source_account_id' => $account->id,
                'destination_account_id' => null,
            ]);

            // Update account balance
            $account->balance -= $totalAmount;
            $account->save();

            if ($isFullRepayment) {
                // Update all remaining schedules as paid
                foreach ($unpaidSchedules as $schedule) {
                    $schedule->update([
                        'paid_amount' => $schedule->remaining_amount,
                        'remaining_amount' => 0,
                        'status' => 'paid',
                        'paid_at' => $now
                    ]);
                }

                // Create single payment record for full amount
                $payment = $this->loan->payments()->create([
                    'loan_id' => $this->loan->id,
                    'payment_schedule_id' => $unpaidSchedules->first()->id,
                    'amount' => $totalAmount,
                    'payment_method' => 'account',
                    'reference_number' => 'FULL-PAY-' . time(),
                    'status' => 'completed',
                    'notes' => sprintf(
                        'Full loan repayment of %s made from account %s',
                        number_format($totalAmount, 2),
                        $account->account_number
                    ),
                ]);

                // Update loan status to paid/closed
                $this->loan->update([
                    'status' => 'paid',
                    'closed_at' => $now
                ]);

                // Send notification for full repayment
                $account->customer->user->notify(new TransactionNotification(
                    $transaction,
                    'Loan Fully Repaid',
                    sprintf(
                        'Your loan #%d has been fully repaid. Amount: %s. Account balance: %s',
                        $this->loan->id,
                        number_format($totalAmount, 2),
                        number_format($account->balance, 2)
                    )
                ));
            } else {
                // Send notification for partial payment
                $account->customer->user->notify(new TransactionNotification(
                    $transaction,
                    'Loan Payment Made',
                    sprintf(
                        'Partial payment of %s made for loan #%d. Remaining balance: %s. Account balance: %s',
                        number_format($totalAmount, 2),
                        $this->loan->id,
                        number_format($this->loan->schedules->where('status', '!=', 'paid')->sum('remaining_amount'), 2),
                        number_format($account->balance, 2)
                    )
                ));
            }

            DB::commit();

            // Show receipt
            $this->receiptData = [
                'date' => $now->format('Y-m-d H:i:s'),
                'loan_id' => $this->loan->id,
                'amount' => $totalAmount,
                'reference' => $payment->reference_number,
                'account_number' => $account->account_number,
                'account_balance' => $account->balance,
                'payment_type' => $isFullRepayment ? 'Full Repayment' : 'Partial Payment',
                'loan_status' => $isFullRepayment ? 'Closed' : 'Active',
                'early_payment_fee_percentage' => 0, // Default value
                'late_payment_fee_percentage' => 0, // Default value
                'total_amount' => $totalAmount, // Total amount paid
                'remaining_balance' => $this->loan->schedules->where('status', '!=', 'paid')->sum('remaining_amount'), // Remaining balance
            ];

            $this->receiptType = 'payment with Account';
            $this->showReceiptModal = true;
            $this->reset(['paymentAmount', 'selectedAccount']);

            $this->toast(
                type: 'success',
                title: $isFullRepayment ? 'Loan fully repaid successfully' : 'Payment processed successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
            );
        } catch (\Exception $e) {
            DB::rollBack();

            dd($e->getMessage());
            $this->toast(
                type: 'error',
                title: 'Failed to process payment: ' . $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
            );
        }
    }


    // Add these new methods
    public function makePaymentWithCard()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'cardNumber' => 'required|string|regex:/^[0-9]{16}$/',
            'cardExpiry' => 'required|string|regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
            'cardCvv' => 'required|string|regex:/^[0-9]{3,4}$/',
        ]);

        try {
            // Here you would integrate with your payment gateway
            // This is a placeholder for the actual payment processing logic

            $schedule = $this->loan->schedules()
                ->where('status', '!=', 'paid')
                ->orderBy('due_date')
                ->first();

            if (!$schedule) {
                throw new \Exception('No pending payments found');
            }

            DB::beginTransaction();

            // Create payment record
            $payment = $this->loan->payments()->create([
                'loan_id' => $this->loan->id,
                'payment_schedule_id' => $schedule->id,
                'amount' => $this->paymentAmount,
                'payment_method' => 'card',
                'reference_number' => 'CARD-' . time(),
                'status' => 'completed',
                'notes' => "Card payment **** " . substr($this->cardNumber, -4),
            ]);

            // Update schedule
            $schedule->paid_amount += $this->paymentAmount;
            $schedule->remaining_amount -= $this->paymentAmount;
            $schedule->status = $schedule->remaining_amount <= 0 ? 'paid' : 'partial';
            $schedule->paid_at = now();
            $schedule->save();

            DB::commit();

            $this->reset(['paymentAmount', 'cardNumber', 'cardExpiry', 'cardCvv']);

            $this->toast(
                type: 'success',
                title: 'Card payment processed successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
            );
        } catch (\Exception $e) {
            DB::rollBack();

            $this->toast(
                type: 'error',
                title: 'Failed to process card payment: ' . $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
            );
        }
    }

    public function makePaymentWithMobileMoney()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'mobileMoneyNumber' => 'required|string|regex:/^[0-9]{10}$/',
            'mobileMoneyNetwork' => 'required|string|in:MTN,AIRTEL',
        ]);

        try {
            // Initialize Flutterwave
            $flw = new \Flutterwave\Rave(env('FLW_SECRET_KEY'));
            $mobileMoneyService = new \Flutterwave\MobileMoney();

            // Prepare payload
            $payload = [
                "type" => "mobile_money_uganda",
                "phone_number" => $this->mobileMoneyNumber,
                "network" => $this->mobileMoneyNetwork,
                "amount" => $this->paymentAmount,
                "currency" => 'UGX',
                "email" => Auth::user()->email,
                "tx_ref" => $this->generateTransactionReference(),
            ];

            // Initiate payment
            $response = $mobileMoneyService->mobilemoney($payload);

            if ($response['status'] === 'success') {
                // Handle redirect for payment authorization
                $redirectUrl = $response['meta']['authorization']['redirect'];
                return redirect()->away($redirectUrl);
            } else {
                throw new \Exception('Failed to initiate mobile money payment');
            }
        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Failed to process mobile money payment: ' . $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
            );
        }
    }

    // Add this method
    public function setFullRepaymentAmount()
    {
        if (!$this->loan) {
            return;
        }

        $totalRemaining = $this->loan->schedules()
            ->where('status', '!=', 'paid')
            ->sum('remaining_amount');

        $this->paymentAmount = $totalRemaining;
        $this->isFullPayment = true;
    }

    public function resetPaymentAmount()
    {
        $this->paymentAmount = 0;
        $this->isFullPayment = false;
    }
    public function render()
    {
        return view('livewire.customer-folder.my-loans.visit-loan');
    }
}
