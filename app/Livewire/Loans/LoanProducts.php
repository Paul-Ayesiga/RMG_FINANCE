<?php

namespace App\Livewire\Loans;

use App\Models\LoanProduct;
use Livewire\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Illuminate\Support\Collection;

#[Lazy()]
class LoanProducts extends Component
{
    use Toast;
    use WithPagination;

    public ?LoanProduct $loanProduct;

    public $search = '';
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];
    public $perPage = 5;
    public array $selected = [];

    public bool $filledbulk = false;
    public bool $emptybulk = false;
    public bool $addLoanProductModal = false;
    public bool $previewLoanProductModal = false;
    public bool $editLoanProductModal = false;
    public bool $deleteLoanProductModal = false;

    public array $activeFilters = [];

    #[Computed]
    public $loanProductToPreview = null;

    #[Computed]
    public $loanProductToDelete = null;


    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('nullable|string')]
    public $description;

    #[Validate('required|numeric|min:0|max:100')]
    public $interest_rate;

    #[Validate('required|numeric|min:0')]
    public $minimum_amount;

    #[Validate('required|numeric|min:0')]
    public $maximum_amount;

    #[Validate('required|integer|min:1')]
    public $minimum_term;

    #[Validate('required|integer|min:1')]
    public $maximum_term;

    #[Validate('numeric|min:0')]
    public $processing_fee = 0;

    #[Validate('numeric|min:0|max:100')]
    public $late_payment_fee_percentage = 0;

    #[Validate('numeric|min:0|max:100')]
    public $early_payment_fee_percentage = 0;

    #[Validate('required|string')]
    public $status = 'active';

    #[Validate('nullable')]
    public $requirements = [];

    #[Validated('nullable')]
    public ?array $allowed_frequencies = [];
    public Collection $frequenciesSearchable;
    

    public $columns = [
        'loans_count' => true,
        'name' => true,
        'description' => false,
        'interest_rate' => true,
        'minimum_amount' => true,
        'maximum_amount' => true,
        'minimum_term' => true,
        'maximum_term' => true,
        'processing_fee' => true,
        'status' => true,
        ];

    public function mount()
    {
        $this->frequenciesSearchable = collect([]); // Initialize as empty collection
        $this->searchFrequencies();
    }

    public function headers()
    {
        return collect([
            ['key' => 'loans_count', 'label' => 'Active Loans'],
            ['key' => 'name', 'label' => 'Name'],
            ['key' => 'description', 'label' => 'Description'],
            ['key' => 'interest_rate', 'label' => 'Interest Rate (%)'],
            ['key' => 'minimum_amount', 'label' => 'Min Amount'],
            ['key' => 'maximum_amount', 'label' => 'Max Amount'],
            ['key' => 'minimum_term', 'label' => 'Min Term'],
            ['key' => 'maximum_term', 'label' => 'Max Term'],
            ['key' => 'processing_fee', 'label' => 'Processing Fee (%)'],
            ['key' => 'status', 'label' => 'Status'],
        ])->filter(function ($header) {
            return $this->columns[$header['key']] ?? false;
        })->toArray();
    }

    public function searchFrequencies(string $value = '')
    {
        // Get currently selected frequencies
        $selectedFrequencies = collect(LoanProduct::getPaymentFrequencies())
            ->whereIn('id', $this->allowed_frequencies ?? [])
            ->values();

        // Get filtered frequencies based on search
        $this->frequenciesSearchable = collect(LoanProduct::getPaymentFrequencies())
            ->filter(function($frequency) use ($value) {
                return str_contains(strtolower($frequency['name']), strtolower($value));
            })
            ->values()
            ->merge($selectedFrequencies)
            ->unique('id');
    }

    public function loanProducts(): LengthAwarePaginator
    {
        return LoanProduct::query()
            ->withCount('loans')
            ->when($this->search, fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function saveLoanProduct()
    {
        $this->validate();

        LoanProduct::create([
            'name' => $this->name,
            'description' => $this->description,
            'interest_rate' => $this->interest_rate,
            'minimum_amount' => $this->minimum_amount,
            'maximum_amount' => $this->maximum_amount,
            'minimum_term' => $this->minimum_term,
            'maximum_term' => $this->maximum_term,
            'allowed_frequencies' => json_encode($this->allowed_frequencies),
            'processing_fee' => $this->processing_fee,
            'late_payment_fee_percentage' => $this->late_payment_fee_percentage,
            'early_payment_fee_percentage' => $this->early_payment_fee_percentage,
            'status' => $this->status,
            'requirements' => $this->requirements,
        ]);

        $this->toast(
                type: 'success',
                title: 'Loan product created successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null

        );
        
        // $this->reset();
        $this->addLoanProductModal = false;
    }

    #[On('edit-loan-product')]
    public function editLoanProduct($loanProductId)
    {
        $loanProduct = LoanProduct::find($loanProductId);
        
        $this->loanProduct = $loanProduct;
        $this->name = $loanProduct->name;
        $this->description = $loanProduct->description;
        $this->interest_rate = $loanProduct->interest_rate;
        $this->minimum_amount = $loanProduct->minimum_amount;
        $this->maximum_amount = $loanProduct->maximum_amount;
        $this->minimum_term = $loanProduct->minimum_term;
        $this->maximum_term = $loanProduct->maximum_term;
        $this->allowed_frequencies = json_decode($loanProduct->allowed_frequencies) ?? [];
        $this->processing_fee = $loanProduct->processing_fee;
        $this->late_payment_fee_percentage = $loanProduct->late_payment_fee_percentage;
        $this->early_payment_fee_percentage = $loanProduct->early_payment_fee_percentage;
        $this->status = $loanProduct->status;
        $this->requirements = $loanProduct->requirements;
        
        // Refresh the frequencies list with selected values
        $this->searchFrequencies();
        
        $this->editLoanProductModal = true;
    }
    
    public function updateLoanProduct()
    {
        $this->validate();

        $this->loanProduct->update([
            'name' => $this->name,
            'description' => $this->description,
            'interest_rate' => $this->interest_rate,
            'minimum_amount' => $this->minimum_amount,
            'maximum_amount' => $this->maximum_amount,
            'minimum_term' => $this->minimum_term,
            'maximum_term' => $this->maximum_term,
            'allowed_frequencies' => json_encode($this->allowed_frequencies),
            'processing_fee' => $this->processing_fee,
            'late_payment_fee_percentage' => $this->late_payment_fee_percentage,
            'early_payment_fee_percentage' => $this->early_payment_fee_percentage,
            'status' => $this->status,
            'requirements' => $this->requirements,
        ]);

        // $this->reset();
        $this->editLoanProductModal = false;
        $this->toast(
                type: 'success',
                title: 'Loan product updated successfully',
                position: 'toast-top toast-end',
                icon: 'o-check-circle',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
        );
    }

    public function OpenPreviewLoanProductModal($id)
    {
        $loanProduct = LoanProduct::findOrFail($id);
        $this->loanProductToPreview = $loanProduct;
        $this->previewLoanProductModal = true;
    }

    public function openDeleteLoanProductModal($id)
    {
        $this->loanProductToDelete = $id;
        $this->deleteLoanProductModal = true;
    }

    public function confirmDelete($id)
    {
        try {
            $loanProduct = LoanProduct::findOrFail($id);
            
            // Begin a database transaction
            \DB::beginTransaction();
            
            // Delete all associated loans if needed
            // $loanProduct->loans()->delete();
            
            // Delete the loan product
            $loanProduct->delete();
            
            // Commit the transaction
            \DB::commit();
            
            $this->deleteLoanProductModal = false;
            $this->toast(
                type: 'success',
                title: 'Loan Product deleted successfully',
                position: 'toast-top toast-end'
            );
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            \DB::rollBack();
            
            $this->deleteLoanProductModal = false;
            $this->toast(
                type: 'error',
                title: 'Failed to delete Loan Product',
                description: $e->getMessage(),
                position: 'toast-top toast-end'
            );
        }
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
    // ... Add other methods similar to AccountTypes component
    // (editLoanProduct, updateLoanProduct, confirmDelete, etc.)

    public function render()
    {
        return view('livewire.loans.loan-products', [
            'loanProducts' => $this->loanProducts(),
            'headers' => $this->headers(),
            'selected' => $this->selected,
            'activeFiltersCount' => $this->activeFiltersCount(),
        ]);
    }
}
