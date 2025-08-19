<?php

namespace App\Livewire\BankCharges;

use App\Models\BankCharge;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use WireUi\Traits\WireUiActions;

#[Lazy()]
class Overview extends Component
{
    use WithPagination;
    use WireUiActions;

    public $search = '';
    public $perPage = 10;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Modals
    public bool $viewModal = false;
    public bool $createModal = false;
    public bool $editModal = false;

    // Form Data
    public $selectedCharge = null;
    public $name = '';
    public $type = 'deposit';
    public $rate = '';
    public $is_percentage = false;
    public $description = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|min:3',
        'type' => 'required|in:deposit,withdraw,transfer',
        'rate' => 'required|numeric|min:0',
        'is_percentage' => 'boolean',
        'description' => 'nullable|string',
        'is_active' => 'boolean'
    ];


    public function getTransactionTypes()
    {
        return [
            ['id' => 'deposit', 'name' => 'Deposit'],
            ['id' => 'withdraw', 'name' => 'Withdraw'],
            ['id' => 'transfer', 'name' => 'Transfer'],
        ];
    }

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

    public function create()
    {
        $this->reset(['name', 'type', 'rate', 'is_percentage', 'description', 'is_active']);
        $this->createModal = true;
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            BankCharge::create([
                'name' => $this->name,
                'type' => $this->type,
                'rate' => $this->rate,
                'is_percentage' => $this->is_percentage,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
        });

        $this->createModal = false;
        $this->notification()->send([
            'title' => 'Bank charge added successfully!',
            'icon' => 'success'
        ]);
    }

    public function edit(BankCharge $charge)
    {
        $this->selectedCharge = $charge;
        $this->name = $charge->name;
        $this->type = $charge->type;
        $this->rate = $charge->rate;
        $this->is_percentage = $charge->is_percentage;
        $this->description = $charge->description;
        $this->is_active = $charge->is_active;

        $this->editModal = true;
    }

    public function update()
    {
        $this->validate();

        DB::transaction(function () {
            $this->selectedCharge->update([
                'name' => $this->name,
                'type' => $this->type,
                'rate' => $this->rate,
                'is_percentage' => $this->is_percentage,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);
        });

        $this->editModal = false;

        $this->notification()->send([
            'title' => 'Bank charge updated successfully!',
            'icon' => 'success'
        ]);
    }

    public function delete(BankCharge $charge)
    {
        $charge->delete();

        $this->notification()->send([
            'title' => 'Bank charge deleted successfully!',
            'icon' => 'success'
        ]);
    }

    #[On('refresh')]
    public function render()
    {
        $charges = BankCharge::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('type', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.bank-charges.overview', [
            'charges' => $charges,
            'transactionTypes' => $this->getTransactionTypes()
        ]);
    }
}
