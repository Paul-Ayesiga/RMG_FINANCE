<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'loan_product_id',
        'account_id', // disbursement account
        'amount',
        'interest_rate',
        'term',
        'payment_frequency',
        'status', // pending, approved, rejected, active, closed, defaulted
        'disbursement_date',
        'first_payment_date',
        'last_payment_date',
        'total_payable',
        'total_interest',
        'processing_fee',
        'late_payment_fee',
        'early_payment_fee',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'closed_at',
    ];

    protected $casts = [
        'disbursement_date' => 'datetime',
        'first_payment_date' => 'datetime',
        'last_payment_date' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'closed_at' => 'datetime',
        'interest_rate' => 'float',
        'processing_fee' => 'float',
        'late_payment_fee' => 'float',
        'early_payment_fee' => 'float',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(PaymentSchedule::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LoanDocument::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function approve($userId)
    {
        try {
            $this->update([
                'status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now()
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Failed to approve loan: ' . $e->getMessage());
        }
    }

    public function disburse(): void
    {
        if ($this->status !== 'approved') {
            throw new \Exception('Loan must be approved before disbursement');
        }

        // Set first payment date based on disbursement date
        $this->first_payment_date = $this->calculateFirstPaymentDate();
        $this->save();

        // Calculate and create payment schedules
        $this->calculatePaymentSchedule();

        // Update loan status
        $this->update([
            'status' => 'active',
            'disbursed_by' => auth()->id(),
            'disbursed_at' => now(),
        ]);
    }

    private function calculateFirstPaymentDate(): Carbon
    {
        // Now using disbursement_date instead of accessing it directly
        return match($this->payment_frequency) {
            LoanProduct::FREQUENCY_DAILY => $this->disbursement_date->addDay(),
            LoanProduct::FREQUENCY_WEEKLY => $this->disbursement_date->addWeek(),
            LoanProduct::FREQUENCY_BIWEEKLY => $this->disbursement_date->addWeeks(2),
            LoanProduct::FREQUENCY_MONTHLY => $this->disbursement_date->addMonth(),
            LoanProduct::FREQUENCY_QUARTERLY => $this->disbursement_date->addMonths(3),
            default => $this->disbursement_date->addMonth(),
        };
    }

    private function calculateLastPaymentDate(): Carbon
    {
        return $this->first_payment_date->copy()->addMonths($this->term - 1);
    }

    private function getNumberOfPayments(): int
    {
        return match($this->payment_frequency) {
            LoanProduct::FREQUENCY_DAILY => $this->term * 30, // Assuming 30 days per month
            LoanProduct::FREQUENCY_WEEKLY => $this->term * 4, // Assuming 4 weeks per month
            LoanProduct::FREQUENCY_BIWEEKLY => $this->term * 2,
            LoanProduct::FREQUENCY_MONTHLY => $this->term,
            LoanProduct::FREQUENCY_QUARTERLY => ceil($this->term / 3),
            default => $this->term,
        };
    }

    private function calculatePaymentAmount(): array
    {
        $principal = $this->amount;
        $numberOfPayments = $this->getNumberOfPayments();
        
        // Convert annual interest rate to period rate
        $periodRate = match($this->payment_frequency) {
            LoanProduct::FREQUENCY_DAILY => $this->interest_rate / 365 / 100,
            LoanProduct::FREQUENCY_WEEKLY => $this->interest_rate / 52 / 100,
            LoanProduct::FREQUENCY_BIWEEKLY => $this->interest_rate / 26 / 100,
            LoanProduct::FREQUENCY_MONTHLY => $this->interest_rate / 12 / 100,
            LoanProduct::FREQUENCY_QUARTERLY => $this->interest_rate / 4 / 100,
            default => $this->interest_rate / 12 / 100,
        };

        // Calculate payment using PMT formula
        $payment = $principal * ($periodRate * pow(1 + $periodRate, $numberOfPayments))
                  / (pow(1 + $periodRate, $numberOfPayments) - 1);

        // Calculate principal and interest per payment
        $totalPayment = $payment * $numberOfPayments;
        $totalInterest = $totalPayment - $principal;
        
        $principalPerPayment = $principal / $numberOfPayments;
        $interestPerPayment = $totalInterest / $numberOfPayments;

        return [
            'period_rate' => $periodRate,
            'payment_amount' => $payment,
            'number_of_payments' => $numberOfPayments,
            'principal_per_payment' => $principalPerPayment,
            'interest_per_payment' => $interestPerPayment,
            'total_per_payment' => $principalPerPayment + $interestPerPayment
        ];
    }

    private function calculatePaymentSchedule(): void
    {
        $paymentCalculation = $this->calculatePaymentAmount();
        $paymentDate = $this->first_payment_date->copy();

        // Update loan totals
        $this->total_interest = $paymentCalculation['interest_per_payment'] * $paymentCalculation['number_of_payments'];
        $this->total_payable = $this->amount + $this->total_interest;
        $this->save();

        for ($i = 0; $i < $paymentCalculation['number_of_payments']; $i++) {
            // Create payment schedule with due_date
            $this->schedules()->create([
                'due_date' => $paymentDate->copy(),
                'principal_amount' => $paymentCalculation['principal_per_payment'],
                'interest_amount' => $paymentCalculation['interest_per_payment'],
                'total_amount' => $paymentCalculation['total_per_payment'],
                'remaining_amount' => $paymentCalculation['total_per_payment'],
                'status' => 'pending'
            ]);

            // Move to next payment date based on frequency
            $paymentDate = match($this->payment_frequency) {
                LoanProduct::FREQUENCY_DAILY => $paymentDate->addDay(),
                LoanProduct::FREQUENCY_WEEKLY => $paymentDate->addWeek(),
                LoanProduct::FREQUENCY_BIWEEKLY => $paymentDate->addWeeks(2),
                LoanProduct::FREQUENCY_MONTHLY => $paymentDate->addMonth(),
                LoanProduct::FREQUENCY_QUARTERLY => $paymentDate->addMonths(3),
                default => $paymentDate->addMonth(),
            };
        }
    }

    public function getPaymentFrequencyLabelAttribute(): string
    {
        return LoanProduct::getPaymentFrequencies()[$this->payment_frequency] ?? 'Unknown';
    }

    public function getNextPaymentDateAttribute()
    {
        return $this->schedules()
            ->where('status', '!=', 'paid')
            ->orderBy('due_date')
            ->first()?->due_date;
    }
}
