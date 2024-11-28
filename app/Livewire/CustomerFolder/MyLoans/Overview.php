<?php

namespace App\Livewire\CustomerFolder\MyLoans;

use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Account;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Mary\Traits\WithMediaSync;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Illuminate\Validation\Rule;
use App\Traits\LoanChecks;
use App\Notifications\TransactionNotification;

#[Lazy()]
class Overview extends Component
{
    use Toast, WithPagination, WithFileUploads, WithMediaSync, LoanChecks;

    public $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public $perPage = 5;
    public array $selected = [];

    public array $activeFilters = [];

    // Modals
    public bool $addLoanModal = false;
    public bool $viewLoanModal = false;
    public bool $repaymentModal = false;
    public bool $showReceiptModal = false;


    #[Validate('required')]
    public ? int $loanProductId = null;

    public Collection $loanProducts;

    #[Validate('required')]
    public ? int $accountId = null;

    public Collection $accounts;

    #[Validate('required|numeric|min:0')]
    public $amount;

    #[Validate('required|numeric|min:0')]
    public $paymentFrequency;

    #[Validate('required|numeric|min:1')]
    public $term;

    public $documents = [];
    public $receiptData = null;

    public $selectedLoan = null;

    public array $columns = [
        'loanProduct.name' => true,
        'amount' => true,
        'status' => true,
        'disbursement_date' => true,
        'next_payment_date' => true,
    ];

    public ?int $selectedAccount = null;
    public Collection $userAccounts;

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

    // Add these properties
    public $minTerm = 0;
    public $maxTerm = 0;
    public $minAmount = 0;
    public $maxAmount = 0;

    // Add this property
    public $allowedFrequencies = [];


    public bool $isFullPayment = false;

    public $mobileMoneyNetwork;

    public function mount()
    {
        $this->loanProducts = collect();
        $this->accounts = collect();
        $this->searchLoanDisbursementAccount();
        $this->searchLoanToPaymentAccounts();
    }

     public function activeFiltersCount(): int
    {
        $count = 0;

        if (!empty($this->search)) $count++;
        return $count;
    }

    public function updateActiveFilters()
    {
        $this->activeFilters = [];

        if (!empty($this->search)) {
            $this->activeFilters['search'] = "Search: " . $this->search;
        }
    }

    public function removeFilter($filter)
    {
        if ($filter == 'search') {
            $this->search = '';
        }

        $this->updateActiveFilters();
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->search = '';
        $this->updateActiveFilters();
        $this->resetPage();
    }

    public function exportToExcel()
    {
        // Implement export logic as needed
    }

     // Add this method
    public function openLoanModal()
    {
        // Check for pending loans
        $loanCheck = $this->canApplyForLoan();
        if (!$loanCheck['can_apply']) {
            $this->toast(
                type: 'error',
                title: $loanCheck['message'],  // Put the message in the title
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3'
            );
            return;
        }

        // Initialize collections if they're empty
        if ($this->loanProducts->isEmpty()) {
            $this->searchLoanProduct();
        }
        if ($this->accounts->isEmpty()) {
            $this->searchLoanDisbursementAccount();
        }

        $this->addLoanModal = true;
    }

    public function searchLoanProduct(string $value = '')
    {
        $selectedLoanProduct = LoanProduct::where('id', $this->loanProductId)->get();

        $this->loanProducts = LoanProduct::query()
            ->where('name', 'ilike', "%$value%")
            ->take(5)
            ->orderBy('name')
            ->get()
            ->merge($selectedLoanProduct);

        // If a loan product is selected, update the constraints
        if ($this->loanProductId) {
            $this->updateLoanProductConstraints();
        }
    }

    // Add this new method
    public function updateLoanProductConstraints()
    {
        $loanProduct = LoanProduct::find($this->loanProductId);
        if ($loanProduct) {
            $this->minTerm = $loanProduct->minimum_term;
            $this->maxTerm = $loanProduct->maximum_term;
            $this->minAmount = $loanProduct->minimum_amount;
            $this->maxAmount = $loanProduct->maximum_amount;

            // Handle the allowed frequencies
            $frequencies = $loanProduct->allowed_frequencies;

            // Normalize the frequencies data structure
            if (is_string($frequencies)) {
                $frequencies = json_decode($frequencies, true);
            }

            $this->allowedFrequencies = collect($frequencies)
                ->map(function($frequency) {
                    // Handle different possible data structures
                    $value = match(true) {
                        is_array($frequency) => $frequency[0],
                        is_string($frequency) && str_starts_with($frequency, '[') => json_decode($frequency)[0],
                        default => $frequency
                    };

                    return [
                        'id' => $value,
                        'name' => str_replace('_', ' ', ucfirst($value))
                    ];
                })
                ->values()
                ->toArray();
        }
    }

    // Update this method
    public function updatedLoanProductId($value)
    {
        if ($value) {
            $this->updateLoanProductConstraints();
        } else {
            $this->minTerm = 0;
            $this->maxTerm = 0;
            $this->minAmount = 0;
            $this->maxAmount = 0;
            $this->allowedFrequencies = [];
        }
    }

    public function searchLoanDisbursementAccount(string $value = '')
    {
        $selectedAccount = Account::where('id', $this->accountId)->get();

        $this->accounts = Account::query()
            ->where('customer_id', auth()->user()->customer->id)
            ->where('status', 'active')
            ->when($value, fn($query) => $query->where('account_number', 'like', "%$value%"))
            ->take(5)
            ->orderBy('account_number')
            ->get()
            ->merge($selectedAccount);
    }

    public function searchLoanToPaymentAccounts(string $value = '')
    {
        $selectedAccount = Account::where('id', $this->selectedAccount)->get();

        $this->userAccounts = Account::query()
            ->where('customer_id', auth()->user()->customer->id)
            ->where('status', 'active')
            ->when($value, fn($query) => $query->where('account_number', 'like', "%$value%"))
            ->take(5)
            ->orderBy('account_number')
            ->get()
            ->merge($selectedAccount);
    }

    public function toggleColumnVisibility($column)
    {
        $this->columns[$column] = !$this->columns[$column];
    }

    public function headers()
    {
        return collect([
            ['key' => 'loanProduct.name', 'label' => 'Loan Type'],
            ['key' => 'amount', 'label' => 'Amount'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'disbursement_date', 'label' => 'Disbursement Date'],
            ['key' => 'next_payment_date', 'label' => 'Next Payment'],
        ])->filter(function ($header) {
            return $this->columns[$header['key']] ?? false;
        })->toArray();
    }

    public function loans(): LengthAwarePaginator
    {
        return Loan::query()
            ->with(['loanProduct', 'schedules'])
            ->where('customer_id', Auth::user()->customer->id)
            ->when($this->search, function (Builder $query) {
                $query->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('loanProduct', function (Builder $subQuery) {
                        $subQuery->where('name', 'ilike', "%{$this->search}%");
                    });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function applyForLoan()
    {
        // Check if user can apply for a loan
        $loanCheck = $this->canApplyForLoan();
        if (!$loanCheck['can_apply']) {
            $this->toast(
                type: 'error',
                title: 'Cannot Apply for Loan',
                message: $loanCheck['message'],
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
            );
            return;
        }

        $loanProduct = LoanProduct::findOrFail($this->loanProductId);

        $this->validate([
            'loanProductId' => 'required|exists:loan_products,id',
            'accountId' => 'required|exists:accounts,id',  // Updated validation rule
            'amount' => [
                'required',
                'numeric',
                'min:' . $this->minAmount,
                'max:' . $this->maxAmount
            ],
            'term' => [
                'required',
                'integer',
                'min:' . $this->minTerm,
                'max:' . $this->maxTerm
            ],
            'paymentFrequency' => [
                'required',
                Rule::in(array_column(LoanProduct::getPaymentFrequencies(), 'id'))
            ],
            'documents.*' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240' // 10MB max file size
            ]
        ], [
            'amount.min' => 'The loan amount must be at least ' . number_format($this->minAmount, 2),
            'amount.max' => 'The loan amount cannot exceed ' . number_format($this->maxAmount, 2),
            'term.min' => 'The loan term must be at least ' . $this->minTerm . ' months',
            'term.max' => 'The loan term cannot exceed ' . $this->maxTerm . ' months',
            'documents.*.mimes' => 'Documents must be PDF or image files (jpg, jpeg, png)',
            'documents.*.max' => 'Documents cannot be larger than 10MB'
        ]);

        // Clean up the payment frequency value
        $frequency = is_array($this->paymentFrequency)
            ? $this->paymentFrequency[0]
            : (is_string($this->paymentFrequency) && str_starts_with($this->paymentFrequency, '[')
                ? json_decode($this->paymentFrequency)[0]
                : $this->paymentFrequency);

        try {
            // Create loan application
            $loan = Loan::create([
                'customer_id' => Auth::user()->customer->id,
                'loan_product_id' => $this->loanProductId,
                'account_id' => $this->accountId,  // Updated field name
                'amount' => $this->amount,
                'term' => $this->term,
                'interest_rate' => $loanProduct->interest_rate,
                'payment_frequency' => $frequency, // Use the cleaned frequency value
                'status' => 'pending',
                'total_payable' => $this->calculateTotalPayable(),
                'total_interest' => $this->calculateTotalInterest(),
                'processing_fee' => $loanProduct->processing_fee,
            ]);

            // Handle document uploads if any
            if (!empty($this->documents)) {
                foreach ($this->documents as $document) {
                    $loan->addMedia($document->getRealPath())
                        ->usingName($document->getClientOriginalName())
                        ->toMediaCollection('loan_documents');
                }
            }



            $this->addLoanModal = false;
            $this->reset(['amount', 'term', 'documents']);

            $this->toast(
                type: 'success',
                title: 'Loan application submitted successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
            );

        } catch (\Exception $e) {
            dd($e);
            $this->toast(
                type: 'error',
                title: 'Failed to submit loan application: ' . $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
            );
        }
    }

    public function viewLoan($loanId)
    {
        $this->selectedLoan = Loan::with(['loanProduct', 'schedules', 'payments'])
            ->findOrFail($loanId);
        $this->viewLoanModal = true;
    }

    public function openRepaymentModal($loanId)
    {
       $this->selectedLoan = Loan::with(['schedules' => function ($query) {
            $query->where('status', '!=', 'paid')
                ->orderBy('due_date');
        }])->findOrFail($loanId);

        $this->paymentAmount = 0;
        $this->isFullPayment = false;
        $this->repaymentModal = true;
    }

    public function makePaymentFromAccount()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'selectedAccount' => 'required|exists:accounts,id',
        ]);

        $account = Account::findOrFail($this->selectedAccount);

        // Calculate total remaining amount for all unpaid schedules
        $unpaidSchedules = $this->selectedLoan->schedules()
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
                $payment = $this->selectedLoan->payments()->create([
                    'loan_id' => $this->selectedLoan->id,
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
                $payment = $this->selectedLoan->payments()->create([
                    'loan_id' => $this->selectedLoan->id,
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
            $unpaidSchedulesCount = $this->selectedLoan->schedules()
                ->where('status', '!=', 'paid')
                ->count();

            if ($unpaidSchedulesCount === 0) {
                $this->selectedLoan->update([
                    'status' => 'paid',
                    'closed_at' => $now
                ]);
            }
        }

        try {
            \DB::beginTransaction();

            $now = now();
            $totalAmount = $this->paymentAmount;

            // Create withdrawal transaction
            $transaction = $account->transactions()->create([
                'type' => 'loanPayment',
                'amount' => $totalAmount,
                'reference_number' => 'LOAN-PMT-' . time(),
                'description' => "Loan repayment for Loan #{$this->selectedLoan->id}",
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
                $payment = $this->selectedLoan->payments()->create([
                    'loan_id' => $this->selectedLoan->id,
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
                $this->selectedLoan->update([
                    'status' => 'paid',
                    'closed_at' => $now
                ]);

                // Send notification for full repayment
                $account->customer->user->notify(new TransactionNotification(
                    $transaction,
                    'Loan Fully Repaid',
                    sprintf(
                        'Your loan #%d has been fully repaid. Amount: %s. Account balance: %s',
                        $this->selectedLoan->id,
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
                        $this->selectedLoan->id,
                        number_format($this->selectedLoan->schedules->where('status', '!=', 'paid')->sum('remaining_amount'), 2),
                        number_format($account->balance, 2)
                    )
                ));
            }

            \DB::commit();

            // Show receipt
            $this->receiptData = [
                'date' => $now->format('Y-m-d H:i:s'),
                'loan_id' => $this->selectedLoan->id,
                'amount' => $totalAmount,
                'reference' => $payment->reference_number,
                'account_number' => $account->account_number,
                'account_balance' => $account->balance,
                'payment_type' => $isFullRepayment ? 'Full Repayment' : 'Partial Payment',
                'loan_status' => $isFullRepayment ? 'Closed' : 'Active',
                'early_payment_fee_percentage' => 0, // Default value
                'late_payment_fee_percentage' => 0, // Default value
                'total_amount' => $totalAmount, // Total amount paid
                'remaining_balance' => $this->selectedLoan->schedules->where('status', '!=', 'paid')->sum('remaining_amount'), // Remaining balance
            ];

            $this->repaymentModal = false;
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
            \DB::rollBack();

            $this->toast(
                type: 'error',
                title: 'Failed to process payment: ' . $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
            );
        }
    }

    private function calculateEarlyPaymentFee($schedule, $loanProduct)
    {
        $daysEarly = $schedule->due_date->diffInDays(now());
        $earlyPaymentFeePercentage = $loanProduct->early_payment_fee_percentage ?? 0;
        return $this->paymentAmount * ($earlyPaymentFeePercentage / 100);
    }

    private function calculateLatePaymentFee($schedule, $loanProduct)
    {
        $daysLate = now()->diffInDays($schedule->due_date);
        $latePaymentFeePercentage = $loanProduct->late_payment_fee_percentage ?? 0;
        return $this->paymentAmount * ($latePaymentFeePercentage / 100);
    }

    private function calculateTotalPayable()
    {
        $loanProduct = LoanProduct::find($this->loanProductId);
        $interest = ($this->amount * $loanProduct->interest_rate * $this->term) / 100;
        return $this->amount + $interest + $loanProduct->processing_fee;
    }

    private function calculateTotalInterest()
    {
        $loanProduct = LoanProduct::find($this->loanProductId);
        return ($this->amount * $loanProduct->interest_rate * $this->term) / 100;
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

            $schedule = $this->selectedLoan->schedules()
                ->where('status', '!=', 'paid')
                ->orderBy('due_date')
                ->first();

            if (!$schedule) {
                throw new \Exception('No pending payments found');
            }

            \DB::beginTransaction();

            // Create payment record
            $payment = $this->selectedLoan->payments()->create([
                'loan_id' => $this->selectedLoan->id,
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

            \DB::commit();

            $this->repaymentModal = false;
            $this->reset(['paymentAmount', 'cardNumber', 'cardExpiry', 'cardCvv']);

            $this->toast(
                type: 'success',
                title: 'Card payment processed successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
            );

        } catch (\Exception $e) {
            \DB::rollBack();

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
        if (!$this->selectedLoan) {
            return;
        }

        $totalRemaining = $this->selectedLoan->schedules()
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
        return view('livewire.customer-folder.my-loans.overview', [
            'loans' => $this->loans(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
        ]);
    }
}
