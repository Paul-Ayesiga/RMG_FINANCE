<div>
    <div class="breadcrumbs text-sm mb-2">
        <ul>
            <li><a wire:navigate href="{{ route('dashboard')}}">Home</a></li>
            <li><a wire:navigate href="{{ route('clients')}}">Clients</a></li>
            <li><a disabled>edit_client</a></li>
        </ul>
    </div>
        <div class="flex justify-center mt-4">
            <nav class="flex overflow-x-auto items-center p-1 space-x-1 rtl:space-x-reverse text-sm text-gray-600 bg-gray-500/5 rounded-xl dark:bg-gray-500/20">
                @foreach (['basicInfo', 'moreDetails', 'profileImage', 'addresses', 'payments'] as $tab)
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
        <x-mary-form wire:submit.prevent="save">
            <div class="mt-4">
                <!-- Basic Info Tab -->
                <div x-show="$wire.activeTab === 'basicInfo'">
                    <x-mary-card title="Basic Information" separator progress-indicator class="bg-white shadow-lg dark:bg-inherit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-mary-input label="Name" wire:model.defer="name" placeholder="Customer name" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Email" wire:model.defer="email" placeholder="Email" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Phone" wire:model.defer="phone_number" placeholder="Phone" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        </div>
                        <x-slot:actions>
                            <x-mary-button label="Next" icon="o-forward" class="bg-violet-300 btn-sm  dark:text-blue-900" wire:click="next('basicInfo')" spinner="next('basicInfo')"  />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

                <!-- More Details Tab -->
                <div x-show="$wire.activeTab === 'moreDetails'">
                    <x-mary-card title="More Details" separator class="bg-white shadow-lg dark:bg-inherit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <select wire:model.defer="gender" class="select select-primary w-full max-w-xs border-b-2 border-white shadow-lg focus:border-none focus:outline-none">
                                <option disabled selected>Pick Gender</option>
                                <option>female</option>
                                <option>male</option>
                                <option>other</option>
                            </select>
                            <select wire:model.defer="marital_status" class="select select-primary w-full max-w-xs border-b-2 border-white shadow-lg focus:border-none focus:outline-none">
                                <option disabled selected>Marital Status</option>
                                <option>single</option>
                                <option>married</option>
                                <option>divorced</option>
                                <option>widowed</option>
                            </select>
                            <x-mary-datetime label="Birth Date" wire:model.defer="date_of_birth" icon="o-calendar" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Identification Number" wire:model.defer="identification_number" placeholder="National ID" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" readonly/>
                            <x-mary-input label="Occupation" wire:model.defer="occupation" placeholder="Occupation" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Employer" wire:model.defer="employer" placeholder="Employer" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Annual Income" wire:model.defer="annual_income" placeholder="Annual Income" type="number" step="0.01" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        </div>
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward" class="bg-orange-900 text-white btn-sm" wire:click="previous('moreDetails')" spinner="previous('moreDetails')" />
                            <x-mary-button label="Next" icon="o-forward" class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('moreDetails')" spinner="next('moreDetails')" />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

                <!-- Profile Image Tab -->
                <div x-show="$wire.activeTab === 'profileImage'">
                    <x-mary-card title="Profile Image" separator class="shadow-lg bg-white dark:bg-inherit">
                        @if(!empty($customer->user->avatar))
                        <x-mary-file wire:model="photo" accept="image/png image/jpeg" crop-after-change hint="click to change">
                            <img src="{{ $customer->user->avatar }}" class="h-40 rounded-lg" />
                        </x-mary-file>
                        @else
                        <x-mary-file wire:model="photo" accept="image/png image/jpeg" crop-after-change hint="click to change">
                            <img src="{{ asset('user.png') }}" class="h-40 rounded-lg" />
                        </x-mary-file>
                        @endif
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward" class="bg-orange-900 btn-sm text-white" wire:click="previous('profileImage')" spinner="previous('profileImage')" />
                            <x-mary-button label="Next" icon="o-forward" class="btn-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('profileImage')" spinner="next('profileImage')" />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

                <!-- Addresses Tab -->
                <div x-show="$wire.activeTab === 'addresses'">
                    <x-mary-card title="Addresses" separator class="bg-white shadow-lg dark:bg-inherit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-mary-input label="Primary Address" wire:model="address" placeholder="Primary Address" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Secondary Address" wire:model="secondaryAddress" placeholder="Secondary Address" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
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
                            <x-mary-button label="Submit" icon="o-arrow-up-circle"  class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="save" spinner="save"/>
                        </x-slot:actions>
                    </x-mary-card>
                </div>
            </div>
        </x-mary-form>
</div>
