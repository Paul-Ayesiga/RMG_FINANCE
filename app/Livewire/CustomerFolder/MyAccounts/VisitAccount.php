<?php

namespace App\Livewire\CustomerFolder\MyAccounts;

use Livewire\Component;
use App\Models\Account;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\Beneficiary;
use WireUi\Traits\WireUiActions;
use App\Models\Transaction;
use Livewire\Attributes\Lazy;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Livewire\WithPagination;
use Livewire\Attributes\On;


#[Lazy()]
class VisitAccount extends Component
{
    use WithPagination;
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

    public $beneficiaries=[];

    public $beneficiarySelectedIndex = null;

    // history
    public $search = '';
    public $type = '';
    public $status = '';
    public $dateRange = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $viewModal = false;
    public ?Transaction $selectedTransaction = null;

    public $selectedTransactions = [];
    public $selectAll = false;

    public $accountTransactions;

    public $currency;

    #[On('refresh')]
    public function mount(Account $account): void
    {

        $this->depositToAccount = $account;
        $this->withdrawFromAccount = $account;
        $this->MyAccounts = Auth::user()->customer->accounts()->where('id', '!=', $account->id)->get();
        $this->searchTransferCustomerAccounts();

        $this->beneficiaries = Beneficiary::where('user_id',Auth::id())->get()
        ->map(function ($beneficiary) {
            $accountDetails = $beneficiary->account ? $beneficiary->account->account_number : $beneficiary->account_number;
            $bankName = $beneficiary->bank_name;

            return [
                'id' => $beneficiary->id,
                'nickname' => $beneficiary->nickname,
                'account_number' => $accountDetails,
                'bank_name' => $bankName,
            ];
        });
        // dd($this->beneficiaries);

        $this->currency = Auth::user()->currency ?? session('currency', config('currencies.default'));
    }

    public function updatedcurrency()
    {
        // Update the user's preferred currency if authenticated
        if (Auth::check()) {
            User::where('id', Auth::id())->first()->update(['currency' => $this->currency]);
        }

        // Update the session currency
        session(['currency' => $this->currency]);

        // Emit an optional event if needed for other components
        $this->notification()->send([
            'icon' => 'success',
            'title' => 'currency changed successfully'
        ]);
    }

    public function deposit($accountId)
    {
        $user = Auth::id();
        $currentCurrency = User::where('id', $user)->get()->pluck('currency');

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
                'total_amount' => convertCurrency($transaction->total_amount,'UGX',$currentCurrency[0]),
                'reference' => $transaction->reference_number ?? 'DEP' . time(),
                'balance' => convertCurrency($account->balance,'UGX',$currentCurrency[0])
            ];

            $this->receiptType = 'deposit';
            $this->depositAmount = null;
            $this->showReceiptModal = true;
        } catch (\Exception $e) {

            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Deposit failed, please try again later',
                'description' =>  $e->getMessage(),
                'class' => 'bg-red-500'
            ]);
        }
    }

    public function withdraw($accountId)
    {
        $user = Auth::id();
        $currentCurrency = User::where('id', $user)->get()->pluck('currency');

        $account = Account::findOrFail($accountId);

        $this->validate([
            'withdrawalAmount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:' . convertCurrency($account->accountType->max_withdrawal, 'UGX', $currentCurrency[0]),
            ],
        ], [
            'withdrawalAmount.max' => 'Maximum withdrawal limit is ' . convertCurrency($account->accountType->max_withdrawal,'UGX', $currentCurrency[0]),
        ]);

        // Check withdrawal limit
        $withdrawalCount = $account->transactions()
            ->where('type', 'withdrawal')
            ->whereDate('created_at', today())
            ->count();

        if ($withdrawalCount >= 4) {
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Withdrawal limit reached',
                'class' => 'bg-red-500'
            ]);
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
                'total_amount' => convertCurrency($transaction->total_amount, 'UGX', $currentCurrency[0]),
                'reference' => $transaction->reference_number ?? 'WTH' . time(),
                'balance' => convertCurrency($account->balance,'UGX',$currentCurrency[0])
            ];

            DB::commit();

            $this->receiptType = 'withdrawal';
            $this->showReceiptModal = true;
            $this->withdrawalAmount = null;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Withdrawal failed, please try again later',
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
        $user = Auth::id();
        $currentCurrency = User::where('id', $user)->get()->pluck('currency');

        $sourceAccount = Account::findOrFail($id);

        // dd($this->beneficiarySelectedIndex);

        // Check if both forms are filled
        if (($this->transferCustomerAccountId && $this->accountNumber) || ($this->transferCustomerAccountId && $this->beneficiarySelectedIndex != null) || ($this->accountNumber && $this->beneficiarySelectedIndex != null)) {
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Multiple Accounts Selected!',
                'description' => 'Please provide only one account: either a Customer Account ID, Other Account Number, or Select a Beneficiary.',
                'css' => 'alert alert-warning text-white shadow-lg rounded-sm p-3',
            ]);
            return;
        }

        $this->transferOtherAccountId = Account::where('account_number', $this->accountNumber)->first()->id ?? null;

        // Validate transfer data, including beneficiary selection
        $this->validate([
            // Make sure at least one of these fields is provided
            'transferCustomerAccountId' => 'required_without_all:accountNumber,beneficiarySelectedIndex',
            'accountNumber' => 'required_without_all:transferCustomerAccountId,beneficiarySelectedIndex',
            'beneficiarySelectedIndex' => 'required_without_all:transferCustomerAccountId,accountNumber',

            // Transfer amount validation
            'transferAmount' => [
                'required',
                'numeric',
                'min:1000',
                'max:'
                . convertCurrency($sourceAccount->accountType->max_withdrawal, 'UGX', $currentCurrency[0]),
            ],
        ], [
            'transferCustomerAccountId.required_without_all' => 'Please select either transfer to account.',
            'accountNumber.required_without_all' => 'Please provide transfer to account number.',
            'beneficiarySelectedIndex.required_without_all' => 'Please select a beneficiary.',
            'transferAmount.required' => 'The transfer amount is required.',
            'transferAmount.numeric' => 'The transfer amount must be a valid number.',
            'transferAmount.min' => 'The transfer amount must be at least '. convertCurrency('1000', 'UGX', $currentCurrency[0]),
            'transferAmount.max' => 'The transfer amount exceeds the maximum limit of ' . number_format(convertCurrency($sourceAccount->accountType->max_withdrawal, 'UGX', $currentCurrency[0]) ?? PHP_FLOAT_MAX, 2) . '.',
        ]);


        // Determine if beneficiary selection is used
        if ($this->beneficiarySelectedIndex !== null) {
            // Get the selected beneficiary from the list
            $selectedBeneficiary = $this->beneficiaries[$this->beneficiarySelectedIndex];

            // You can now use $selectedBeneficiary['account_number'] and other info
            $destinationAccount = Account::where('account_number', $selectedBeneficiary['account_number'])->first();

            // dd($destinationAccount);
        }else {
            $destinationAccount = Account::find($this->transferCustomerAccountId ?? $this->transferOtherAccountId);
        }

        if($destinationAccount){

        // Check if the transfer is to the same account
            if ($sourceAccount->id === $destinationAccount->id) {
                $this->notification()->send([
                    'icon' => 'error',
                    'title' => 'Cannot transfer to same account',
                    'css' => 'alert alert-warning text-white shadow-lg rounded-sm p-3',
                ]);
                return;
            }
        }else{
            $this->notification()->send([
                'icon' => 'error',
                'title' => 'Account number provided doesnot exist',
                'css' => 'alert alert-warning text-white shadow-lg rounded-sm p-3',
            ]);
        }

        try {
            // Attempt the transfer
            $transaction = $sourceAccount->transfer($destinationAccount, $this->transferAmount);

            if (!$transaction || !is_object($transaction)) {
                throw new \Exception('Transfer failed to process');
            }

            // Determine if it's an internal transfer
            $isInternalTransfer = $sourceAccount->customer_id === $destinationAccount->customer_id;

            // Get charges breakdown
            $charges = $sourceAccount->appliedCharges()
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

            // Get taxes breakdown (only for external transfers)
            $taxes = $isInternalTransfer ? collect([]) : $sourceAccount->appliedTaxes()
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
                'from_account' => $sourceAccount->account_number,
                'to_account' => $destinationAccount->account_number,
                'amount' => $this->transferAmount,
                'charges' => $charges->toArray(),
                'total_charges' => $transaction->charges,
                'taxes' => $taxes->toArray(),
                'total_taxes' => $transaction->taxes,
                'total_amount' => $transaction->total_amount,
                'reference' => $transaction->reference_number ?? 'TRF' . time(),
                'balance' => $sourceAccount->balance,
                'is_internal' => $isInternalTransfer
            ];

            $this->receiptType = 'transfer';
            $this->showReceiptModal = true;
        } catch (\Exception $e) {
            $this->notification()->send([
                'icon' => 'error',
                'title' => $e->getMessage(),
                'css' => 'alert alert-warning text-white shadow-lg rounded-sm p-3 text-red-500',
            ]);
        }

        // Optionally save the beneficiary
        if ($this->saveBeneficiary) {
            $validatedData = $this->validate(
                [
                    'beneficiaryName' => 'required|string|max:255',
                    'bankName' => 'nullable|string|max:255',
                    'accountNumber' => 'required|string|max:50',
                ]
            );

            $beneficiary = Beneficiary::create([
                'user_id' => Auth::id(),
                'nickname' => $validatedData['beneficiaryName'],
                'account_id' => $this->transferOtherAccountId,
                'bank_name' => $validatedData['bankName'] ?? 'RMGBANK',
                'account_number' => $validatedData['accountNumber']
            ]);

            $this->notification()->send([
                'icon' => 'success',
                'title' => 'Beneficiary Saved',
                'description' => 'Beneficiary information has been successfully saved.'
            ]);

        }
        $this->resetForm();
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
        $this->beneficiarySelectedIndex = null;
    }

    // account history

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingType()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'type', 'status', 'dateRange']);
        $this->resetPage();

        // Emit an event to Alpine.js to show the tooltip
        $this->dispatch('showTooltip');
    }
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function export()
    {
        $fileName = 'transactions_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        $selectedTransactions = $this->selectedTransactions; // Get the selected IDs

        return Excel::download(new TransactionsExport(
            search: $this->search,
            type: $this->type,
            status: $this->status,
            dateRange: $this->dateRange,
            sortField: $this->sortField,
            sortDirection: $this->sortDirection,
            selectedIds: $selectedTransactions // Pass selected IDs
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

      public function viewTransaction(Transaction $transaction)
    {
        $this->selectedTransaction = $transaction;
        $this->viewModal = true;
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            // Use array_column to extract the 'id' values from the array
            $this->selectedTransactions = array_column($this->accountTransactions, 'id');
        } else {
            $this->selectedTransactions = [];
        }
    }

    public function render()
    {

        $accounttransactions = Transaction::query()
            ->where(function ($query) {
                $query->where('account_id', $this->account->id)
                    ->orWhere('source_account_id', $this->account->id)
                    ->orWhere('destination_account_id', $this->account->id);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('reference_number', 'like', '%' . $this->search . '%')
                        ->orWhere('amount', 'like', '%' . $this->search . '%')
                        ->orWhereHas('account', function ($accountQuery) {
                            $accountQuery->where('account_number', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->dateRange, function ($query) {
                if (str_contains($this->dateRange, ' to ')) {
                    [$startDate, $endDate] = explode(' to ', $this->dateRange);
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->latest()
            ->paginate($this->perPage);

            $this->accountTransactions = $accounttransactions->items();

        return view('livewire.customer-folder.my-accounts.visit-account',[
            'accountTransactionsBlade' => $accounttransactions
        ]);
    }

}
