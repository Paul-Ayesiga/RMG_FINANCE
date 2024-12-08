<?php

namespace App\Livewire\CustomerFolder\MyAccounts;

use Livewire\Component;
use App\Models\Account;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Mary\Traits\Toast;
use App\Models\Beneficiary;
use WireUi\Traits\WireUiActions;
use App\Models\Transaction;
use Livewire\Attributes\Lazy;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;

#[Lazy()]
class VisitAccount extends Component
{
    use Toast;
    use WireUiActions;
    public ?Account $account;

    #[Computed]
    public $depositToAccount = null;

    // withdrawal account
    #[Computed]
    public $withdrawFromAccount = null;

    // deposit amount
    #[Validate('required')]
    public $depositAmount;

    // withdrawal amount
    #[Validate('required')]
    public $withdrawalAmount;

    // trnasfer amount
    #[Validate('required')]
    public $transferAmount;

    public $transferFromAccountId = null;

    public $MyAccounts;

    public $history;

    #[Validate('required')]
    public ?int $transferCustomerAccountId = null;

    #[Validate('required')]
    public ?int $transferOtherAccountId = null;

    public Collection $transferCustomerAccounts;

    // receipt modal
    public bool $showReceiptModal = false;
    public $receiptData = null;
    public $receiptType = null;


    public $beneficiaryName;

    #[Validate('required')]
    public $accountNumber;

    public $bankName;

    public $saveBeneficiary = false;


    // history
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    public function mount(Account $account): void
    {
        $this->depositToAccount = $account;
        $this->withdrawFromAccount = $account;
        $this->MyAccounts = Auth::user()->customer->accounts()->where('id', '!=', $account->id)->get();
        $this->searchTransferCustomerAccounts();
        $this->history = Transaction::where('account_id',$account->id)->get();
    }

    public function deposit($accountId)
    {
        $this->validate([
            'depositAmount' => 'required|numeric|min:1000',
        ]);

        $account = Account::findOrFail($accountId);

        try {
            // Get the transaction object from deposit method
            $transaction = $account->deposit($this->depositAmount);

            // Check if transaction was successful and is an object
            if (!$transaction || !is_object($transaction)) {
                throw new \Exception('Transaction failed to process');
            }

            // Get breakdowns of charges and taxes
            $charges = $account->appliedCharges()
                ->where('created_at', $transaction->created_at)
                ->with('bankCharge:id,name')
                ->get()
                ->map(function ($charge) {
                    return [
                        'name' => $charge->bankCharge->name,
                        'amount' => $charge->amount,
                        'rate' => $charge->rate_used . ($charge->was_percentage ? '%' : '')
                    ];
                });

            $taxes = $account->appliedTaxes()
                ->where('created_at', $transaction->created_at)
                ->with('tax:id,name')
                ->get()
                ->map(function ($tax) {
                    return [
                        'name' => $tax->tax->name,
                        'amount' => $tax->amount,
                        'rate' => $tax->rate_used . ($tax->was_percentage ? '%' : '')
                    ];
                });

            // Set receipt data and show modal
            $this->receiptData = [
                'date' => now()->format('Y-m-d H:i:s'),
                'account_number' => $account->account_number,
                'amount' => $this->depositAmount,
                'charges' => $charges->toArray(),
                'total_charges' => $transaction->charges,
                'taxes' => $taxes->toArray(),
                'total_taxes' => $transaction->taxes,
                'total_amount' => $transaction->total_amount,
                'reference' => $transaction->reference_number ?? 'DEP' . time(),
                'balance' => $account->balance
            ];

            $this->receiptType = 'deposit';
            $this->depositAmount = null;
            $this->showReceiptModal = true;
        } catch (\Exception $e) {
            dd($e->getMessage());
            $this->toast(
                type: 'error',
                title: $e->getMessage(),
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
        }
    }

    public function withdraw($accountId)
    {
        $account = Account::findOrFail($accountId);

        $this->validate([
            'withdrawalAmount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . ($account->accountType->max_withdrawal ?? PHP_FLOAT_MAX),
            ],
        ], [
            'withdrawalAmount.max' => 'Maximum withdrawal limit is ' . number_format($account->accountType->max_withdrawal ?? PHP_FLOAT_MAX, 2),
        ]);

        // Check withdrawal limit
        $withdrawalCount = $account->transactions()
            ->where('type', 'withdrawal')
            ->whereDate('created_at', today())
            ->count();

        if ($withdrawalCount >= 4) {
            $this->toast(
                type: 'error',
                title: 'Withdrawal limit reached',
                position: 'toast-top toast-end'
            );
            return;
        }

        try {
            DB::beginTransaction();

            // Get the transaction object from withdraw method
            $transaction = $account->withdraw($this->withdrawalAmount);

            if (!$transaction || !is_object($transaction)) {
                throw new \Exception('Transaction failed to process');
            }

            // Get breakdowns of charges and taxes
            $charges = $account->appliedCharges()
                ->where('created_at', $transaction->created_at)
                ->with('bankCharge:id,name')
                ->get()
                ->map(function ($charge) {
                    return [
                        'name' => $charge->bankCharge->name,
                        'amount' => $charge->amount,
                        'rate' => $charge->rate_used . ($charge->was_percentage ? '%' : '')
                    ];
                });

            $taxes = $account->appliedTaxes()
                ->where('created_at', $transaction->created_at)
                ->with('tax:id,name')
                ->get()
                ->map(function ($tax) {
                    return [
                        'name' => $tax->tax->name,
                        'amount' => $tax->amount,
                        'rate' => $tax->rate_used . ($tax->was_percentage ? '%' : '')
                    ];
                });

            // Set receipt data
            $this->receiptData = [
                'date' => now()->format('Y-m-d H:i:s'),
                'account_number' => $account->account_number,
                'amount' => $this->withdrawalAmount,
                'charges' => $charges->toArray(),
                'total_charges' => $transaction->charges,
                'taxes' => $taxes->toArray(),
                'total_taxes' => $transaction->taxes,
                'total_amount' => $transaction->total_amount,
                'reference' => $transaction->reference_number ?? 'WTH' . time(),
                'balance' => $account->balance
            ];

            DB::commit();

            $this->receiptType = 'withdrawal';
            $this->showReceiptModal = true;
            $this->withdrawalAmount = null;
        } catch (\Exception $e) {
            DB::rollBack();
            // $this->toast(
            //     type: 'error',
            //     title: $e->getMessage(),
            //     position: 'toast-top toast-end'
            // );
            $this->notification()->send([

                'icon' => 'error',

                'title' => 'new notification!',

                'description' =>  $e->getMessage(),

                'class' => 'bg-red-500'

            ]);
        }
    }

    public function searchTransferCustomerAccounts(string $value = '')
    {
        $customerId = Auth::user()->customer->id;
        $this->transferCustomerAccounts = Account::query()
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->whereNot('id', $this->account->id)  // Exclude the current account
            ->where(function ($query) use ($value) {
                $query->where('account_number', 'ilike', "%$value%")
                ->orWhereHas('accountType', function ($subQuery) use ($value) {
                    $subQuery->where('name', 'ilike', "%$value%");
                });
            })
            ->take(5)
            ->orderBy('account_number')
            ->get();
    }

    public function transfer($id)
    {
        $sourceAccount = Account::findOrFail($id);

        // $this->validate();

        $this->transferOtherAccountId = Account::where('account_number', $this->accountNumber)->first()->id ?? null;

        // Validate based on transfer type

        if($this->transferCustomerAccountId == null){
            $this->validate(['transferCustomerAccountId' => 'required' ]);
        }

        if($this->transferOtherAccountId == null){
            $this->validate(['accountNumber' => 'required']);
        }

        $this->validate([

            'transferAmount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . ($sourceAccount->accountType->max_withdrawal ?? PHP_FLOAT_MAX),
            ],
        ], [
            'transferAmount.max' => 'Maximum transfer limit is ' . number_format($sourceAccount->accountType->max_withdrawal ?? PHP_FLOAT_MAX, 2),
        ]);


        $destinationAccount = Account::find($this->transferCustomerAccountId ?? $this->transferOtherAccountId);



        if ($sourceAccount->id === $destinationAccount->id) {
            $this->toast(
                type: 'error',
                title: 'Cannot transfer to same account',
                position: 'toast-top toast-end',
                icon: 'o-x-circle',
                css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
                timeout: 3000
            );
            return;
        }

        // try {
        //     // Attempt the transfer
        //     $transaction = $sourceAccount->transfer($destinationAccount, $this->transferAmount);

        //     if (!$transaction || !is_object($transaction)) {
        //         throw new \Exception('Transfer failed to process');
        //     }

        //     // Determine if it's an internal transfer
        //     $isInternalTransfer = $sourceAccount->customer_id === $destinationAccount->customer_id;

        //     // Get charges breakdown
        //     $charges = $sourceAccount->appliedCharges()
        //         ->where('created_at', $transaction->created_at)
        //         ->with('bankCharge:id,name')
        //         ->get()
        //         ->map(function ($charge) {
        //             return [
        //                 'name' => $charge->bankCharge->name,
        //                 'amount' => $charge->amount,
        //                 'rate' => $charge->rate_used . ($charge->was_percentage ? '%' : '')
        //             ];
        //         });

        //     // Get taxes breakdown (only for external transfers)
        //     $taxes = $isInternalTransfer ? collect([]) : $sourceAccount->appliedTaxes()
        //         ->where('created_at', $transaction->created_at)
        //         ->with('tax:id,name')
        //         ->get()
        //         ->map(function ($tax) {
        //             return [
        //                 'name' => $tax->tax->name,
        //                 'amount' => $tax->amount,
        //                 'rate' => $tax->rate_used . ($tax->was_percentage ? '%' : '')
        //             ];
        //         });

        //     // Set receipt data and show modal
        //     $this->receiptData = [
        //         'date' => now()->format('Y-m-d H:i:s'),
        //         'from_account' => $sourceAccount->account_number,
        //         'to_account' => $destinationAccount->account_number,
        //         'amount' => $this->transferAmount,
        //         'charges' => $charges->toArray(),
        //         'total_charges' => $transaction->charges,
        //         'taxes' => $taxes->toArray(),
        //         'total_taxes' => $transaction->taxes,
        //         'total_amount' => $transaction->total_amount,
        //         'reference' => $transaction->reference_number ?? 'TRF' . time(),
        //         'balance' => $sourceAccount->balance,
        //         'is_internal' => $isInternalTransfer
        //     ];

        //     $this->receiptType = 'transfer';
        //     $this->showReceiptModal = true;
        //     $this->resetForm();
        // } catch (\Exception $e) {
        //     $this->toast(
        //         type: 'error',
        //         title: $e->getMessage(),
        //         position: 'toast-top toast-end',
        //         icon: 'o-x-circle',
        //         css: 'alert alert-error text-white shadow-lg rounded-sm p-3',
        //         timeout: 3000
        //     );
        // }

        // Optionally save the beneficiary
        if ($this->saveBeneficiary) {
            // dd('saving');
            $validatedData = $this->validate([
                'beneficiaryName' => 'required|string|max:255',
                'bankName' => 'nullable|string|max:255',
            ]);

            $beneficiary = Beneficiary::create([
                'user_id' => Auth::id(),
                'nickname' => $validatedData['beneficiaryName'],
                'account_id' => $this->transferOtherAccountId,
                'bank_name' => $validatedData['bankName'] ?? 'RMGBANK',
            ]);

            $this->toast(
                type: 'success',
                title: 'Beneficiary Saved',
                description: 'Beneficiary information has been successfully saved.',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 9000
            );

        }
    }

    public function transferToOtherLocalBank($id){
        $this->validate([
            'beneficiaryName' => 'required|string|max:255',
            'accountNumber' => 'required',
            'bankName' => 'required|string|max:255',
            'transferAmount' => 'required|numeric|min:1',
        ]);
        dd('transfering to another bank');
    }

    private function resetForm()
    {
        $this->transferAmount = null;
        $this->accountNumber = null;
        $this->transferCustomerAccountId = null;
        $this->transferOtherAccountId = null;
        $this->beneficiaryName = null;
    }


    // account history
    public function export()
    {
        $fileName = 'transactions_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new TransactionsExport(
            search: $this->search,
            type: $this->type,
            status: $this->status,
            dateRange: $this->dateRange,
            sortField: $this->sortField,
            sortDirection: $this->sortDirection
        ), $fileName);
    }
    public function copyToClipboard($value)
    {
        $this->js("navigator.clipboard.writeText('$value')");

        $this->notification()->send([

            'icon' => 'success',

            'title' => 'Reference copied to clipboard',

        ]);
    }



    public function render()
    {
        return view('livewire.customer-folder.my-accounts.visit-account');
    }
}
