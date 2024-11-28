<?php

namespace App\Livewire\Loans;

use App\Models\Loan;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Notifications\LoanApproved;
use App\Notifications\LoanDisbursed;
use Illuminate\Support\Facades\DB;

class Overview extends Component
{
    use Toast, WithPagination;

    // Search and Filters
    public $search = '';
    public $statusFilter = '';
    public array $dateRange = [
        'from' => '',
        'to' => ''
    ];
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public $perPage = 10;
    public array $selected = [];

    // Modals
    public bool $viewLoanModal = false;
    public bool $approveLoanModal = false;
    public bool $disburseLoanModal = false;
    public bool $rejectLoanModal = false;

    // Form Data
    public $selectedLoan = null;
    public $rejectionReason = '';
    public $disbursementNote = '';

    public array $columns = [
        'id' => true,
        'customer.name' => true,
        'loanProduct.name' => true,
        'amount' => true,
        'status' => true,
        'created_at' => true,
        'disbursement_date' => true,
    ];

    public array $activeFilters = [];

    public function mount()
    {
        $this->updateActiveFilters();
    }

    public function headers()
    {
        return collect([
            ['key' => 'id', 'label' => 'Loan ID'],
            ['key' => 'customer.user.name', 'label' => 'Customer'],
            ['key' => 'loanProduct.name', 'label' => 'Product'],
            ['key' => 'amount', 'label' => 'Amount'],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'created_at', 'label' => 'Application Date'],
            ['key' => 'disbursement_date', 'label' => 'Disbursement Date'],
            ['key' => 'actions', 'label' => 'Actions'],
        ])->filter(function ($header) {
            return $this->columns[$header['key']] ?? true;
        })->toArray();
    }

    public function loans()
    {
        return Loan::query()
            ->with(['customer', 'loanProduct', 'schedules'])
            ->when($this->search, function (Builder $query) {
                $query->where('id', 'like', "%{$this->search}%")
                    ->orWhereHas('customer', function (Builder $q) {
                        $q->where('name', 'ilike', "%{$this->search}%");
                    })
                    ->orWhereHas('loanProduct', function (Builder $q) {
                        $q->where('name', 'ilike', "%{$this->search}%");
                    });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->dateRange['from'], function (Builder $query) {
                $query->whereDate('created_at', '>=', $this->dateRange['from']);
            })
            ->when($this->dateRange['to'], function (Builder $query) {
                $query->whereDate('created_at', '<=', $this->dateRange['to']);
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function viewLoan($loanId)
    {
        $this->selectedLoan = Loan::with([
            'customer',
            'loanProduct',
            'schedules',
            'documents',
            'account'
        ])->findOrFail($loanId);
        $this->viewLoanModal = true;
    }

    public function openApprovalModal($loanId)
    {
        $this->selectedLoan = Loan::with(['customer', 'loanProduct'])->findOrFail($loanId);
        $this->approveLoanModal = true;
    }

    public function approveLoan()
    {
        if (!$this->selectedLoan || $this->selectedLoan->status !== 'pending') {
            $this->toast(
                type: 'error',
                title: 'Invalid loan or status',
                position: 'toast-top toast-end'
            );
            return;
        }

        try {
            DB::beginTransaction();

            $this->selectedLoan->approve(auth()->id());

            $this->selectedLoan->customer->user->notify(new LoanApproved($this->selectedLoan));

            DB::commit();

            $this->toast(
                type: 'success',
                title: 'Loan approved successfully',
                position: 'toast-top toast-end'
            );

            $this->approveLoanModal = false;
            $this->selectedLoan = null;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->toast(
                type: 'error',
                title: 'Error approving loan: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    #[On('echo:loan-approved,LoanApproved')]
    public function notifyNewOrder()
    {
        dd('hello loan update');
    }
    public function openDisbursementModal($loanId)
    {
        $this->selectedLoan = Loan::with(['customer', 'loanProduct', 'account'])
            ->findOrFail($loanId);
        $this->disburseLoanModal = true;
    }

    public function disburseLoan()
    {
        if (!$this->selectedLoan || $this->selectedLoan->status !== 'approved') {
            $this->toast(
                type: 'error',
                title: 'Invalid loan or status',
                position: 'toast-top toast-end'
            );
            return;
        }

        try {
            DB::beginTransaction();

            $this->selectedLoan->disbursement_date = now();
            $this->selectedLoan->save();

            $this->selectedLoan->disburse();

            // Credit customer's account
            $this->selectedLoan->account->deposit(
                $this->selectedLoan->amount,
                'Loan disbursement - ' . $this->selectedLoan->id
            );

            $this->selectedLoan->customer->user->notify(new LoanDisbursed($this->selectedLoan));

            DB::commit();

            $this->toast(
                type: 'success',
                title: 'Loan disbursed successfully',
                position: 'toast-top toast-end'
            );

            $this->disburseLoanModal = false;
            $this->selectedLoan = null;
            $this->disbursementNote = '';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->toast(
                type: 'error',
                title: 'Error disbursing loan: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    public function openRejectModal($loanId)
    {
        $this->selectedLoan = Loan::findOrFail($loanId);
        $this->rejectLoanModal = true;
    }

    public function rejectLoan()
    {
        $this->validate([
            'rejectionReason' => 'required|min:10'
        ]);

        if (!$this->selectedLoan || $this->selectedLoan->status !== 'pending') {
            $this->toast(
                type: 'error',
                title: 'Invalid loan or status',
                position: 'toast-top toast-end'
            );
            return;
        }

        try {
            $this->selectedLoan->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $this->rejectionReason
            ]);

            $this->toast(
                type: 'success',
                title: 'Loan rejected successfully',
                position: 'toast-top toast-end'
            );

            $this->rejectLoanModal = false;
            $this->selectedLoan = null;
            $this->rejectionReason = '';

        } catch (\Exception $e) {
            $this->toast(
                type: 'error',
                title: 'Error rejecting loan: ' . $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
    }

    public function toggleColumnVisibility($column)
    {
        $this->columns[$column] = !$this->columns[$column];
    }

    public function updateActiveFilters()
    {
        $this->activeFilters = [];

        if (!empty($this->search)) {
            $this->activeFilters['search'] = "Search: " . $this->search;
        }

        if (!empty($this->statusFilter)) {
            $this->activeFilters['status'] = "Status: " . ucfirst($this->statusFilter);
        }

        if (!empty($this->dateRange['from']) || !empty($this->dateRange['to'])) {
            $dateFilter = 'Date: ';
            if (!empty($this->dateRange['from'])) {
                $dateFilter .= 'From ' . $this->dateRange['from'];
            }
            if (!empty($this->dateRange['to'])) {
                $dateFilter .= (!empty($this->dateRange['from']) ? ' - ' : '') . 'To ' . $this->dateRange['to'];
            }
            $this->activeFilters['date'] = $dateFilter;
        }
    }

    public function removeFilter($filter)
    {
        switch ($filter) {
            case 'search':
                $this->search = '';
                break;
            case 'status':
                $this->statusFilter = '';
                break;
            case 'date':
                $this->dateRange = ['from' => '', 'to' => ''];
                break;
        }

        $this->updateActiveFilters();
        $this->resetPage();
    }

    public function clearAllFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateRange = ['from' => '', 'to' => ''];
        $this->updateActiveFilters();
        $this->resetPage();
    }

    public function clearDateRange()
    {
        $this->dateRange = [
            'from' => '',
            'to' => ''
        ];
        $this->updateActiveFilters();
    }

    public function applyDateFilter()
    {
        $this->validate([
            'dateRange.from' => 'nullable|date',
            'dateRange.to' => 'nullable|date|after_or_equal:dateRange.from',
        ], [
            'dateRange.to.after_or_equal' => 'To date must be after or equal to From date',
        ]);

        $this->updateActiveFilters();
    }

    #[Computed]
    public function activeFiltersCount()
    {
        return count($this->activeFilters);
    }

    public function render()
    {
        return view('livewire.loans.overview', [
            'loans' => $this->loans(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
        ]);
    }

    public function setStatusFilter($status)
    {
        if ($status === $this->statusFilter) {
            // If clicking the same status, clear it
            $this->statusFilter = '';
        } else {
            $this->statusFilter = $status;
        }
        $this->updateActiveFilters();
    }
}
