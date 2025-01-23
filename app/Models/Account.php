<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mary\Traits\Toast;
use App\Notifications\TransactionNotification;
use Illuminate\Support\Facades\DB;
use App\Events\PrivateNotify;
use Illuminate\Support\Facades\Auth;

class Account extends Model
{
    use HasFactory;
    use Toast;

    // protected $fillable = ['customer_id', 'account_type_id', 'balance'];
    protected $guarded=[];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

      public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function appliedCharges()
    {
        return $this->morphMany(AppliedCharge::class, 'chargeable');
    }

    public function appliedTaxes()
    {
        return $this->morphMany(AppliedTax::class, 'taxable');
    }

    protected function calculateChargesAndTaxes(string $type, float $amount): array
    {
        $totalCharges = 0;
        $totalTaxes = 0;
        $appliedChargeDetails = [];
        $appliedTaxDetails = [];

        // Calculate Bank Charges
        $charges = BankCharge::where('type', $type)
            ->where('is_active', true)
            ->get();

        foreach ($charges as $charge) {
            $chargeAmount = $charge->is_percentage
                ? ($amount * $charge->rate) / 100
                : $charge->rate;

            $totalCharges += $chargeAmount;

            // Store charge details
            $appliedChargeDetails[] = [
                'name' => $charge->name,
                'amount' => $chargeAmount,
                'rate' => $charge->rate,
                'is_percentage' => $charge->is_percentage
            ];

            // Record the applied charge
            $this->appliedCharges()->create([
                'bank_charge_id' => $charge->id,
                'amount' => $chargeAmount,
                'rate_used' => $charge->rate,
                'was_percentage' => $charge->is_percentage
            ]);
        }

        // Calculate Taxes
        $taxes = Tax::where('is_active', true)->get();
        foreach ($taxes as $tax) {

            $user = Auth::id();
            $currentCurrency = User::where('id', $user)->get()->pluck('currency');

            if ($currentCurrency === 'UGX') {
                $taxAmount = $tax->is_percentage
                    ? ($amount * $tax->rate) / 100
                    : $tax->rate;
            } else {
                $amount = convertCurrencyToUGX($amount,'UGX', $currentCurrency);
                $taxAmount = $tax->is_percentage
                    ? ($amount * $tax->rate) / 100
                    : $tax->rate;
            }

            $totalTaxes += $taxAmount;

            // Store tax details
            $appliedTaxDetails[] = [
                'name' => $tax->name,
                'amount' => $taxAmount,
                'rate' => $tax->rate,
                'is_percentage' => $tax->is_percentage
            ];

            // Record the applied tax
            $this->appliedTaxes()->create([
                'tax_id' => $tax->id,
                'amount' => $taxAmount,
                'rate_used' => $tax->rate,
                'was_percentage' => $tax->is_percentage
            ]);
        }

        return [
            'charges' => $totalCharges,
            'charges_breakdown' => $appliedChargeDetails,
            'taxes' => $totalTaxes,
            'taxes_breakdown' => $appliedTaxDetails,
            'total_deductions' => $totalCharges + $totalTaxes
        ];
    }

    public function deposit($amount)
    {
        DB::beginTransaction();
        try {
            // Calculate charges and taxes
            $deductions = $this->calculateChargesAndTaxes('deposit', $amount);

            $user = Auth::id();
            $currentCurrency = User::where('id',$user)->get()->pluck('currency');

            // dd($currentCurrency);

            if ($currentCurrency === 'UGX') {

                $convertedAmount = $amount;
                // $convertedDeductions = $this->calculateChargesAndTaxes('deposit', $convertedAmount);
            } else {
                // Convert the amount from the deposit currency to UGX if it's not already UGX
                $convertedAmount = convertCurrencyToUGX($amount, 'UGX', $currentCurrency);
                // $convertedDeductions = $this->calculateChargesAndTaxes('deposit', $convertedAmount);
            }

            // Calculate final amount (amount minus deductions)
            $finalAmount = $convertedAmount - $deductions['total_deductions'];

            // Update balance
            $this->balance += $finalAmount;
            $this->save();

            // Create the transaction record with breakdowns
            $transaction = $this->transactions()->create([
                'type' => 'deposit',
                'amount' => $convertedAmount,
                'charges' => $deductions['charges'],
                'charges_breakdown' => json_encode($deductions['charges_breakdown']),
                'taxes' => $deductions['taxes'],
                'taxes_breakdown' => json_encode($deductions['taxes_breakdown']),
                'total_amount' => $finalAmount,
                'reference_number' => 'DEP' . time(),
                'status' => 'completed',
                'description' => 'Account deposit'
            ]);

            // Send notification
            // $this->customer->user->notify(new TransactionNotification(
            //     $transaction,
            //     'Deposit Successful',
            //     "A deposit of " . number_format($amount, 2) . " has been processed. Net amount after charges: " . number_format($finalAmount, 2)
            // ));

            // PrivateNotify::dispatch($this->customer->user, 'Your deposit was successful!');

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function withdraw($amount)
    {
        DB::beginTransaction();
        try {

            $user = Auth::id();
            $currentCurrency = User::where('id', $user)->get()->pluck('currency'); // Fallback to 'UGX' if no currency is set

            // Check minimum balance requirement
            $minBalance = $this->accountType->min_balance ?? 0;
            $maxWithdrawal = $this->accountType->max_withdrawal ?? PHP_FLOAT_MAX;

            // dd($maxWithdrawal);

            if ($currentCurrency === 'UGX') {

                $convertedAmount = $amount;
                // $convertedmaxWithdrawal = $maxWithdrawal;
                // $convertedDeductions = $this->calculateChargesAndTaxes('deposit', $convertedAmount);
            } else {
                // Convert the amount from the deposit currency to UGX if it's not already UGX
                $convertedAmount = convertCurrencyToUGX($amount, 'UGX', $currentCurrency);
                // $convertedmaxWithdrawal = convertCurrencyToUGX($maxWithdrawal, 'UGX', $currentCurrency);
                // $convertedDeductions = $this->calculateChargesAndTaxes('deposit', $convertedAmount);
            }

            // dd($convertedAmount);

            // Check maturity period if set
            if ($this->accountType->maturity_period) {
                $accountAge = now()->diffInDays($this->created_at);
                if ($accountAge < $this->accountType->maturity_period) {
                    throw new \Exception("Account has not reached maturity period of {$this->accountType->maturity_period} days");
                }
            }

            // Calculate charges and taxes
            $deductions = $this->calculateChargesAndTaxes('withdraw', $amount);
            $totalAmount = $convertedAmount + $deductions['total_deductions'];

            // Check if withdrawal would leave account below minimum balance
            if (($this->balance - $totalAmount) < $minBalance) {
                throw new \Exception("Withdrawal would put account below minimum balance requirement of " . convertCurrencyToUGX($minBalance, 'UGX', $currentCurrency));
            }

            // Check if sufficient balance including charges and taxes
            if ($this->balance < $totalAmount) {
                throw new \Exception('Insufficient funds (including charges and taxes)');
            }

            // Deduct total amount (withdrawal + charges + taxes)
            $this->balance -= $totalAmount;
            $this->save();

            // Create the transaction record with breakdowns
            $transaction = $this->transactions()->create([
                'type' => 'withdrawal',
                'amount' => $convertedAmount,
                'charges' => $deductions['charges'],
                'charges_breakdown' => json_encode($deductions['charges_breakdown']),
                'taxes' => $deductions['taxes'],
                'taxes_breakdown' => json_encode($deductions['taxes_breakdown']),
                'total_amount' => $totalAmount,
                'reference_number' => 'WIT' . time(),
                'status' => 'completed',
                'description' => 'Account withdrawal'
            ]);

            // Send notification
            // $this->customer->user->notify(new TransactionNotification(
            //     $transaction,
            //     'Withdrawal Successful',
            //     "A withdrawal of " . number_format($amount, 2) . " has been processed. Total deduction including charges: " . number_format($totalAmount, 2)
            // ));

            // PrivateNotify::dispatch($this->customer->user, 'Your withdrawal was successful!');

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function transfer($destinationAccount, $amount)
    {
        DB::beginTransaction();
        try {
            $user = Auth::id();
            $currentCurrency = User::where('id', $user)->get()->pluck('currency');

            // Check minimum balance requirement for source account
            $minBalance = $this->accountType->min_balance ?? 0;
            $maxTransfer = $this->accountType->max_withdrawal ?? PHP_FLOAT_MAX;

            // Check maturity period if set
            if ($this->accountType->maturity_period) {
                $accountAge = now()->diffInDays($this->created_at);
                if ($accountAge < $this->accountType->maturity_period) {
                    throw new \Exception("Account has not reached maturity period of {$this->accountType->maturity_period} days");
                }
            }

            // Validate transfer amount against max_transfer/withdrawal limit
            if ($amount > $maxTransfer) {
                throw new \Exception("Maximum transfer limit is " . number_format(convertCurrencyToUGX($maxTransfer,'UGX', $currentCurrency), 2));
            }

            // Determine if it's an internal transfer (same customer)
            $isInternalTransfer = $this->customer_id === $destinationAccount->customer_id;

            // Calculate charges and taxes based on transfer type
            if ($isInternalTransfer) {
                // Only calculate charges for internal transfers
                $charges = BankCharge::where('type', 'transfer')
                    ->where('is_active', true)
                    ->get();

                $totalCharges = 0;
                $appliedChargeDetails = [];

                foreach ($charges as $charge) {
                    $chargeAmount = $charge->is_percentage
                        ? ($amount * $charge->rate) / 100
                        : $charge->rate;

                    $totalCharges += $chargeAmount;

                    $appliedChargeDetails[] = [
                        'name' => $charge->name,
                        'amount' => $chargeAmount,
                        'rate' => $charge->rate,
                        'is_percentage' => $charge->is_percentage
                    ];

                    $this->appliedCharges()->create([
                        'bank_charge_id' => $charge->id,
                        'amount' => $chargeAmount,
                        'rate_used' => $charge->rate,
                        'was_percentage' => $charge->is_percentage
                    ]);
                }

                $deductions = [
                    'charges' => $totalCharges,
                    'charges_breakdown' => $appliedChargeDetails,
                    'taxes' => 0,
                    'taxes_breakdown' => [],
                    'total_deductions' => $totalCharges
                ];
            } else {
                // Calculate both charges and taxes for external transfers
                $deductions = $this->calculateChargesAndTaxes('transfer', $amount);
            }

            if ($currentCurrency === 'UGX') {

                $convertedAmount = $amount;
                // $convertedmaxWithdrawal = $maxWithdrawal;
                // $convertedDeductions = $this->calculateChargesAndTaxes('deposit', $convertedAmount);
            } else {
                // Convert the amount from the deposit currency to UGX if it's not already UGX
                $convertedAmount = convertCurrencyToUGX($amount, 'UGX', $currentCurrency);
                // $convertedmaxWithdrawal = convertCurrencyToUGX($maxWithdrawal, 'UGX', $currentCurrency);
                // $convertedDeductions = $this->calculateChargesAndTaxes('deposit', $convertedAmount);
            }

            $totalAmount = $convertedAmount + $deductions['total_deductions'];

            // Check if transfer would leave account below minimum balance
            if (($this->balance - $totalAmount) < $minBalance) {
                throw new \Exception("Transfer would put account below minimum balance requirement of " . number_format(convertCurrency($minBalance, 'UGX','$currentCurrency'), 0));
            }

            // Check if sufficient balance including charges and taxes
            if ($this->balance < $totalAmount) {
                throw new \Exception('Insufficient funds (including charges and taxes)');
            }

            // Deduct from source account (amount + charges + taxes)
            $this->balance -= $totalAmount;
            $this->save();

            // Add only the transfer amount to destination account
            $destinationAccount->balance += $amount;
            $destinationAccount->save();

            // Record transaction
            $reference = 'TRF' . time();

            $transaction = $this->transactions()->create([
                'type' => 'transfer',
                'amount' => $convertedAmount,
                'charges' => $deductions['charges'],
                'charges_breakdown' => json_encode($deductions['charges_breakdown']),
                'taxes' => $deductions['taxes'],
                'taxes_breakdown' => json_encode($deductions['taxes_breakdown']),
                'total_amount' => $totalAmount,
                'reference_number' => $reference,
                'status' => 'completed',
                'source_account_id' => $this->id,
                'destination_account_id' => $destinationAccount->id,
                'description' => 'Fund transfer to ' . $destinationAccount->account_number
            ]);

            // Send notification to sender
            // $this->customer->user->notify(new TransactionNotification(
            //     $transaction,
            //     'Transfer Sent',
            //     "You have transferred " . number_format($amount, 2) . " to account " . $destinationAccount->account_number . ". Total deduction including charges: " . number_format($totalAmount, 2)
            // ));

            // PrivateNotify::dispatch($this->customer->user, 'Your transfer was successful!');

            // // Send notification to recipient
            // $destinationAccount->customer->user->notify(new TransactionNotification(
            //     $transaction,
            //     'Transfer Received',
            //     "You have received " . number_format($amount, 2) . " from account " . $this->account_number
            // ));

            // PrivateNotify::dispatch($destinationAccount->customer->user, 'You have received funds!');

            DB::commit();
            return $transaction;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function standingOrders()
    {
        return $this->belongsToMany(StandingOrder::class, 'account_standing_order');
    }

}
