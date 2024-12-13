<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;

new class extends Component
{
    use Toast;

    public ?User $user;

    public $activeTab = 'moreDetails';
    public $tabs = ['moreDetails','addresses', 'payments'];

    #[Validate('required')]
    public $phone_number;

    #[Validate('required')]
    public $address;

    #[Validate('nullable')]
    public $secondaryAdress;

    #[Validate('required')]
    public $gender;

    #[Validate('required')]
    public $marital_status;

    #[Validate('required')]
    public $date_of_birth;

    #[Validate('required')]
    public $identification_number;

    #[Validate('required')]
    public $occupation;

    #[Validate('required')]
    public $employer;

    #[Validate('required')]
    public $annual_income;


    public function mount(){
        $this->user = Auth::user();

        $this->gender = $this->user->customer->gender;
        $this->marital_status = $this->user->customer->marital_status;
        $this->phone_number = $this->user->customer->phone_number;
        $this->date_of_birth = $this->user->customer->date_of_birth;
        $this->identification_number = $this->user->customer->identification_number;
        $this->occupation = $this->user->customer->occupation;
        $this->employer = $this->user->customer->employer;
        $this->annual_income = $this->user->customer->annual_income;
        $this->address = $this->user->customer->address;

    }

    public function updateMoreDetails()
    {
        $this->validate();

        try {

            $this->user->customer->update([
                'phone_number' => $this->phone_number,
                'address' => $this->address,
                'gender' => $this->gender,
                'date_of_birth' => $this->date_of_birth,
                'marital_status' => $this->marital_status,
                'identification_number' => $this->identification_number,
                'occupation' => $this->occupation,
                'employer' => $this->employer,
                'annual_income' => $this->annual_income,
            ]);

            $this->toast(
                type: 'success',
                title: 'Updates received successfully',
                description: null,
                position: 'toast-top toast-end',
                icon: 'o-check-badge',
                css: 'alert alert-success text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: route('customer-dashboard')
            );
        } catch (\Exception $e) {
            // Handle the error and show an error toast
            $this->toast(
                type: 'error',
                title: 'Update failed',
                description: $e->getMessage(), // Show error message
                position: 'toast-top toast-end',
                icon: 'o-x-circle', // Use an error icon
                css: 'alert alert-danger text-white shadow-lg rounded-sm p-3',
                timeout: 3000,
                redirectTo: null
            );
        }
    }

     // tab switching
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function next($currentTab)
    {
        $index = array_search($currentTab, $this->tabs);
        if ($this->validateCurrentTab($currentTab) && isset($this->tabs[$index + 1])) {
            $this->activeTab = $this->tabs[$index + 1];
        }
    }

    public function previous($currentTab)
    {
        $index = array_search($currentTab, $this->tabs);
        if (isset($this->tabs[$index - 1])) {
            $this->activeTab = $this->tabs[$index - 1];
        }
    }

    private function validateCurrentTab($currentTab)
    {
        switch ($currentTab) {
            case 'moreDetails':
                $this->validate([
                    'phone_number' => 'required',
                    'gender' => 'required',
                    'marital_status' => 'required',
                    'date_of_birth' => 'required|date',
                    'identification_number' => 'required|string',
                    'annual_income' => 'required',
                    'occupation' => 'required',
                    'employer' => 'required'
                ]);
                break;
            case 'addresses':
                $this->validate([
                    'address' => 'required',
                ]);
                break;
            case 'payments':
                $this->validate([
                    'paymentMethod' => 'nullable',
                    'cardNumber' => 'nullable',
                ]);
                break;
            // case 'notes':
            // Add more validation rules for other tabs
        }
        return true;
    }
}; ?>

<section>
<header>
     <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('More Detail Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-white">
            {{ __("Update your account's more detail information to full activate RMG Finance features.") }}
        </p>
</header>

<div class="flex justify-center mt-4">
    <nav class="flex overflow-x-auto items-center p-1 space-x-1 rtl:space-x-reverse text-sm text-gray-600 bg-gray-500/5 rounded-xl dark:bg-gray-500/20">
        @foreach ([ 'moreDetails', 'addresses', 'payments'] as $tab)
            <button role="tab" type="button" wire:click="setTab('{{ $tab }}')"
                @class([
                    'flex whitespace-nowrap items-center h-8 px-5 font-medium rounded-lg outline-none focus:ring-2 focus:ring-yellow-600 focus:ring-inset shadow',
                    'bg-white text-yellow-600 dark:bg-yellow-600 dark:text-white' => $activeTab === $tab,
                    'hover:text-gray-800 dark:hover:text-gray-300 dark:text-gray-400' => $activeTab !== $tab
                ])>
                {{ ucfirst(str_replace('_', ' ', $tab)) }}
            </button>
        @endforeach
    </nav>
</div>
   <x-mary-form wire:submit.prevent="updateMoreDetails">
        <div class="mt-4">
            <!-- More Details Tab -->
            <div x-show="$wire.activeTab === 'moreDetails'">
                <x-mary-card title="More Details" separator class="bg-white shadow-lg dark:bg-inherit">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <select wire:model.defer="gender" class="select select-primary w-full max-w-xs border-b-2 border-white shadow-lg focus:border-none focus:outline-none">
                            <option value="" selected>Pick Gender</option>
                            <option>female</option>
                            <option>male</option>
                            <option>other</option>
                        </select>
                        <select wire:model.defer="marital_status" class="select select-primary w-full max-w-xs border-b-2 border-white shadow-lg focus:border-none focus:outline-none">
                            <option value="" selected>Marital Status</option>
                            <option>single</option>
                            <option>married</option>
                            <option>divorced</option>
                            <option>widowed</option>
                        </select>
                        <x-mary-input label="Phone" wire:model.defer="phone_number" placeholder="Phone" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        <x-mary-datetime label="Birth Date" wire:model.defer="date_of_birth" icon="o-calendar" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        <x-mary-input label="Identification Number" wire:model.defer="identification_number" placeholder="National ID" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        <x-mary-input label="Occupation" wire:model.defer="occupation" placeholder="Occupation" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        <x-mary-input label="Employer" wire:model.defer="employer" placeholder="Employer" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        <x-mary-input label="Annual Income" wire:model.defer="annual_income" placeholder="Annual Income" type="number" step="0.01" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                    </div>
                    <x-slot:actions>
                        {{-- <x-mary-button label="Previous" icon="o-backward" class="bg-orange-900 text-white btn-sm" wire:click="previous('moreDetails')" spinner="previous('moreDetails')" /> --}}
                        <x-mary-button label="Next" icon="o-forward" class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('moreDetails')" spinner="next('moreDetails')" />
                    </x-slot:actions>
                </x-mary-card>
            </div>

                <!-- Addresses Tab -->
                <div x-show="$wire.activeTab === 'addresses'">
                    <x-mary-card title="Addresses" separator class="bg-white shadow-lg dark:bg-inherit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-mary-input label="Primary Address" wire:model="address" placeholder="Primary Address" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Secondary Address" wire:model="secondaryAddress" placeholder="Secondary Address" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"  disabled/>
                        </div>
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward" class="bg-orange-900 btn-sm text-white" wire:click="previous('addresses')" spinner="previous('addresses')" />
                            <x-mary-button label="Next" icon="o-forward" class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('addresses')" spinner="next('addresses')" />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

                <!-- Payments Tab -->
                <div x-show="$wire.activeTab === 'payments'">
                    <x-mary-card title="Payments (optional for now)" separator class="bg-white shadow-lg dark:bg-inherit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-mary-input label="Payment Method" wire:model.defer="paymentMethod" placeholder="e.g., Credit Card, PayPal" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Card Number" wire:model.defer="cardNumber" placeholder="**** **** **** 1234" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        </div>
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward" class="bg-orange-900 btn-sm text-white" wire:click="previous('payments')" spinner="previous('payments')" />
                            <x-mary-button label="Update" icon="o-arrow-up-circle"  class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="updateMoreDetails" spinner="updateMoreDetails"/>
                        </x-slot:actions>
                    </x-mary-card>
                </div>
            </div>
        </x-mary-form>
</section>
