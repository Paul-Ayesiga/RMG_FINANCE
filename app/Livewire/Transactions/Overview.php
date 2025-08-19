<?php

namespace App\Livewire\Transactions;

use Livewire\Component;
use App\Models\Transaction;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Livewire\Attributes\On;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;

#[Lazy()]
class Overview extends Component
{
    use WithPagination;
    use Toast;

    public $search = '';
    public $type = '';
    public $status = '';
    public $dateRange = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public bool $viewModal = false;
    public ?Transaction $selectedTransaction = null;

    public function updatingSearch()
    {
        $this->resetPage();
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
        $this->toast(
                type: 'success',
                title: 'Reference copied to clipboard',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null

        );
    }

    public function viewTransaction(Transaction $transaction)
    {
        $this->selectedTransaction = $transaction;
        $this->viewModal = true;
    }

    public function getTransactionStatusColor($status)
    {
        return match($status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'error',
            default => 'info'
        };
    }

    public function resetFilters()
    {
        $this->reset(['search', 'type', 'status', 'dateRange']);
        $this->resetPage();
    }

    public function render()
    {
        $transactions = Transaction::query()
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
            ->paginate($this->perPage);

        return view('livewire.transactions.overview', [
            'transactions' => $transactions
        ]);
    }
}