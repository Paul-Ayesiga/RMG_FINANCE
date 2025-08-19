<?php

namespace App\Livewire\Taxes;

use App\Models\Tax;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
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
    public $selectedTax = null;
    public $name = '';
    public $rate = '';
    public $is_percentage = true; // Default to true for taxes
    public $description = '';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|min:3',
        'rate' => 'required|numeric|min:0',
        'is_percentage' => 'boolean',
        'description' => 'nullable|string',
        'is_active' => 'boolean'
    ];

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
        $this->reset(['name', 'rate', 'is_percentage', 'description', 'is_active']);
        $this->is_percentage = true; // Default to percentage for new taxes
        $this->createModal = true;
    }

    public function store()
    {
        $this->validate();

        Tax::create([
            'name' => $this->name,
            'rate' => $this->rate,
            'is_percentage' => $this->is_percentage,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->createModal = false;
        $this->notification()->send([
            'title' => 'Tax added successfully!',
            'icon' => 'success'
        ]);
    }

    public function edit(Tax $tax)
    {
        $this->selectedTax = $tax;
        $this->name = $tax->name;
        $this->rate = $tax->rate;
        $this->is_percentage = $tax->is_percentage;
        $this->description = $tax->description;
        $this->is_active = $tax->is_active;

        $this->editModal = true;
    }

    public function update()
    {
        $this->validate();

        $this->selectedTax->update([
            'name' => $this->name,
            'rate' => $this->rate,
            'is_percentage' => $this->is_percentage,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ]);

        $this->editModal = false;
        $this->notification()->send([
            'title' => 'Tax updated successfully!',
            'icon' => 'success'
        ]);
    }

    public function delete(Tax $tax)
    {
        $tax->delete();

        $this->notification()->send([
            'title' => 'Tax deleted successfully!',
            'icon' => 'success'
        ]);
    }

    public function render()
    {
        $taxes = Tax::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.taxes.overview', [
            'taxes' => $taxes
        ]);
    }
}
