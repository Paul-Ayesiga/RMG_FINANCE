<div class="p-3">
    <div class="breadcrumbs text-sm mb-2">
        <ul>
            <li><a wire:navigate href="{{ route('dashboard')}}">Home</a></li>
            <li><a disabled>Clients</a></li>
        </ul>
    </div>
     <!-- HEADER -->
    <x-mary-header title="Clients" separator progress-indicator>
            <x-slot:middle>
                <x-mary-input
                    label=""
                    placeholder="Search clients.."
                    wire:model.live.debounce="search"
                    clearable
                    icon="o-magnifying-glass"
                    class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none"
                />
            </x-slot:middle>
            <x-slot:actions>
                <x-mary-button label="Create Client" @click="$wire.addCustomerDrawer = true"  icon="o-plus" class="bg-blue-700 mb-3 text-white rounded-md mr-10" />
            </x-slot:actions>
    </x-mary-header>


    {{-- clients table --}}
    <x-mary-card title="" subtitle="" shadow separator progress-indicator>
        {{-- datatable options like xls, bulk delete --}}
        <x-mary-card class="shadow-lg bg-white  h-auto mb-10 dark:bg-inherit">
            <!-- Action buttons -->
            <div class="inline-flex flex-wrap items-center mb-2 space-x-2">
                <!-- Bulk Button -->
                <x-mary-button label="Bulk?" icon="o-trash" class="btn-error btn-sm mx-3" wire:click="bulk" />

                <!-- Filter Button with Badge -->
                <x-mary-button label="Filter" icon="o-funnel" class="bg-blue-200 btn-sm mx-2 rounded-md border-none dark:text-white dark:bg-slate-700"
                    wire:click="$set('filtersDrawer', true)" badge="{{$activeFiltersCount}}" />
            </div>
            {{-- export buttons --}}
             <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="export" icon="o-arrow-down-tray" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    <x-mary-button label="PDF" class="btn-sm rounded-md mx-1 dark:bg-inherit" wire:click="exportToPDF" />
                    <!-- Export to Excel Button -->
                    <x-mary-button label="XLS" class="btn-sm rounded-md mx-2 dark:bg-inherit" wire:click="exportToExcel" />
                    </x-mary-dropdown>
            </div>

            <!-- Column Visibility Dropdown -->
            <div class="inline-flex flex-wrap items-center mb-2">
                <x-mary-dropdown>
                    <x-slot name="trigger">
                        <x-mary-button label="" icon="o-eye" class="bg-blue-200 btn-sm border-none dark:text-white dark:bg-slate-700" />
                    </x-slot>
                    @foreach($columns as $column => $visible)
                        <x-mary-menu-item wire:click="toggleColumnVisibility('{{ $column }}')">
                            @if($visible)
                                <x-mary-icon name="o-eye" class="text-green-500" />
                            @else
                                <x-mary-icon name="o-eye-slash" class="text-gray-500" />
                            @endif
                            <span class="ml-2">{{ ucfirst(str_replace(['_', '.'], ' ', $column)) }}</span>
                        </x-mary-menu-item>
                    @endforeach
                </x-mary-dropdown>
            </div>

            {{-- active filters --}}
            <div class="mb-4 mt-5">
                @if(count($activeFilters) > 0)
                    <x-mary-button wire:click="clearAllFilters" label="Clear All Filters" class="mt-2 btn-danger btn-sm"/>
                @endif
                <div class="flex flex-wrap gap-2">
                    @foreach($activeFilters as $filter => $value)
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium text-white bg-blue-500 rounded-full mt-3">
                            {{ $value }}
                            <button type="button" wire:click="removeFilter('{{ $filter }}')" class="ml-2 text-white hover:text-gray-300">
                                &times;
                            </button>
                        </span>
                    @endforeach
                </div>
            </div>
        </x-mary-card>
        {{-- end of datatable options --}}

        <x-mary-table :headers="$headers" :rows="$customers"  link="/clients/{id}/edit"  :sort-by="$sortBy" with-pagination  per-page="perPage"
            :per-page-values="[1,3, 5, 10]"  wire:model="selected" selectable striped>
            @scope('cell_user.avatar', $customer)
                @if($customer->user->avatar)
                    <x-mary-avatar image="{{ $customer->user->avatar}}" class="!w-10" />
                @else
                    <x-mary-avatar image="{{ asset('user.png')}}" class="!w-10" />
                @endif
            @endscope
            @scope('cell_address', $customer)
                <x-mary-badge :value="$customer->address" class="badge-success truncate" />
            @endscope
            {{-- Special `actions` slot --}}
            @scope('actions', $customer)
                <div class="inline-flex">
                    <x-mary-button icon="o-eye" wire:click.stop="OpenPreviewCustomerModal({{$customer->id}})" spinner="OpenPreviewCustomerModal({{$customer->id}})" class="btn-sm bg-blue-400 dark:text-white" />
                    <x-mary-button icon="o-trash"  wire:click.stop="OpenDeleteCustomerModal({{$customer->id}})" spinner="OpenDeleteCustomerModal({{$customer->id}})" class="btn-sm bg-red-600 dark:text-white" />
                </div>

                {{-- Single Client Modal --}}
                <x-mary-modal wire:model="previewCustomerModal" title="Client Details" subtitle="" separator>
                    @if($this->customerPreview)
                        <div>
                            <div class="rounded-t-lg h-32 overflow-hidden">
                                <img class="object-cover object-top w-full" src='{{ asset('banners/banner3.jpeg')}}' alt='Background Image'>
                            </div>

                            <div class="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
                                @if(!$this->customerPreview->user->avatar)
                                    <img class="object-cover object-center h-32" src="{{ asset('user.png') }}" alt='Profile Image'>
                                @else
                                    <img class="object-cover object-center h-32" src='{{asset($this->customerPreview->user->avatar)}}' alt='Profile Image'>
                                @endif
                            </div>

                            <div class="text-center mt-2">
                                <h2 class="font-semibold text-lg">{{ $this->customerPreview->user->name }}</h2>
                                <p class="text-gray-500">{{ $this->customerPreview->user->email }}</p>
                            </div>

                            <!-- Basic Info Section -->
                            <div class="p-4 border-t mx-8 mt-2">
                                <h3 class="font-extrabold text-gray-700 text-2xl font-sans">Basic Info</h3>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <span class="font-bold">Phone:</span>
                                    <span class="text-lg">{{ $this->customerPreview->phone_number }}</span>
                                </div>
                            </div>

                            <!-- More Details Section -->
                            <div class="p-4 border-t mx-8 mt-2">
                                <h3 class="font-extrabold text-gray-700 text-2xl font-sans">More Details</h3>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <span class="font-bold">Gender:</span>
                                    <span class="text-lg">{{ $this->customerPreview->gender }}</span>
                                    <span class="font-bold">Marital Status:</span>
                                    <span class="text-lg">{{ $this->customerPreview->marital_status }}</span>
                                    <span class="font-bold">Birth Date:</span>
                                    <span class="text-lg">{{ $this->customerPreview->date_of_birth }}</span>
                                    <span class="font-bold">ID Number:</span>
                                    <span class="text-lg">{{ $this->customerPreview->identification_number }}</span>
                                    <span class="font-bold">Occupation:</span>
                                    <span class="text-lg">{{ $this->customerPreview->occupation }}</span>
                                    <span class="font-bold">Employer:</span>
                                    <span class="text-lg">{{ $this->customerPreview->employer }}</span>
                                    <span class="font-bold">Annual Income:</span>
                                    <span class="text-lg">{{ $this->customerPreview->annual_income }}</span>
                                </div>
                            </div>

                            <!-- Addresses Section -->
                            <div class="p-4 border-t mx-8 mt-2">
                                <h3 class="font-extrabold text-gray-700 text-2xl font-sans">Addresses</h3>
                                <div class="grid grid-cols-2 gap-4 mt-2">
                                    <span class="font-bold">Primary Address:</span>
                                    <span class="text-lg">{{ $this->customerPreview->address }}</span>
                                    <span class="font-bold">Secondary Address:</span>
                                    <span class="text-lg">{{ 'Secondary Address' }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <p>Loading customer details...</p>
                        </div>
                    @endif

                    <x-slot:actions>
                        <x-mary-button label="Cancel" @click.stop="$wire.previewCustomerModal = false" />
                        @if($this->customerPreview)
                            <x-mary-button label="Continue To Edit" link="/clients/{{$this->customerPreview->id}}/edit" class="bg-red-600 rounded-md text-white font-bold border-none" spinner />
                        @endif
                    </x-slot:actions>
                </x-mary-modal>
                {{-- End --}}

                {{-- single accountdelete modal --}}
                <x-mary-modal wire:model="deleteCustomerModal" title="Deletion yet To Happen" subtitle="" separator>
                    <div>
                        Are you sure? , you want to perform this action, its irreversible
                    </div>
                    <x-slot:actions>
                        <x-mary-button label="Cancel" @click.stop="$wire.deleteCustomerModal = false" />
                        <x-mary-button label="Delete" wire:click="confirmCustomerdelete({{$this->customerToDelete}})" class="bg-red-600 rounded-md text-white font-bold" spinner/>
                    </x-slot:actions>
                </x-mary-modal>
                {{-- end --}}
            @endscope

            <x-slot:empty>
                <x-mary-icon name="o-cube" label="It is empty." />
            </x-slot:empty>
        </x-mary-table>

    </x-mary-card>
    {{-- end of clients table --}}

    {{-- when selected bulk deletion modal --}}
        <x-mary-modal wire:model="filledbulk"  title="Bulk Deletion yet To Happen" subtitle="" separator>
            <div>
                Are you sure? , you want to perform this action, its irreversible
            </div>
            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.filledbulk = false" />
                <x-mary-button label="Delete" wire:click="deleteSelected" class="bg-red-600 rounded-md text-white font-bold" spinner/>
            </x-slot:actions>
        </x-mary-modal>
        {{-- when selected bulk deletion modal --}}
        <x-mary-modal wire:model="emptybulk"  title="Ooops! No rows selected " subtitle="" separator>
            <div>
                Select some rows to delete
            </div>
            <x-slot:actions>
                <x-mary-button label="Okay" @click="$wire.emptybulk = false" class="btn btn-accent" />
            </x-slot:actions>
        </x-mary-modal>
    {{-- end of bulk delete modal --}}




    <!-- Drawer for Adding Customer -->
    <x-mary-drawer wire:model="addCustomerDrawer" title="Create Customer" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">
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
                            <x-mary-password label="Password" wire:model.live="password" password-icon="o-lock-closed" password-visible-icon="o-lock-open" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" placeholder="Password" />
                            <x-mary-password label="Confirm Password" wire:model.defer="password_confirmation" password-icon="o-lock-closed" password-visible-icon="o-lock-open" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" placeholder="Confirm Password" />
                            <x-mary-input label="Phone" wire:model.defer="phone_number" placeholder="Phone" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        </div>
                        <x-slot:actions>
                            <x-mary-button icon="o-forward" label="Next" class="bg-violet-300 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('basicInfo')" spinner="next('basicInfo')"  />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

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
                            <select wire:model.defer="maritalStatus" class="select select-primary w-full max-w-xs border-b-2 border-white shadow-lg focus:border-none focus:outline-none">
                                <option value="" selected>Marital Status</option>
                                <option>single</option>
                                <option>married</option>
                                <option>divorced</option>
                                <option>widowed</option>
                            </select>
                            <x-mary-datetime label="Birth Date" wire:model.defer="birthDate" icon="o-calendar" class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Identification Number" wire:model.defer="identification_number" placeholder="National ID" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Occupation" wire:model.defer="occupation" placeholder="Occupation" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Employer" wire:model.defer="employer" placeholder="Employer" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Annual Income" wire:model.defer="annual_income" placeholder="Annual Income" type="number" step="0.01" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                        </div>
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward"  class="bg-orange-900 text-white btn-sm" wire:click="previous('moreDetails')" spinner="previous('moreDetails')" />
                            <x-mary-button label="Next" icon="o-forward"  class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('moreDetails')" spinner="next('moreDetails')" />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

                <!-- Profile Image Tab -->
                <div x-show="$wire.activeTab === 'profileImage'">
                    <x-mary-card title="Profile Image" separator class="shadow-lg bg-white dark:bg-inherit">
                        <x-mary-file wire:model="photo" accept="image/png image/jpeg" crop-after-change hint="Click to change">
                            <img src="{{ $avatar ?? asset('user.png') }}" class="h-40 rounded-lg" />
                        </x-mary-file>
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward"  class="bg-orange-900 btn-sm text-white" wire:click="previous('profileImage')" spinner="previous('profileImage')" />
                            <x-mary-button label="Next" icon="o-forward"  class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="next('profileImage')" spinner="next('profileImage')" />
                        </x-slot:actions>
                    </x-mary-card>
                </div>

                <!-- Addresses Tab -->
                <div x-show="$wire.activeTab === 'addresses'">
                    <x-mary-card title="Addresses" separator class="bg-white shadow-lg dark:bg-inherit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <x-mary-input label="Primary Address" wire:model="address" placeholder="Primary Address" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" />
                            <x-mary-input label="Secondary Address" wire:model="secondaryAddress" placeholder="Secondary Address" clearable class="border-b-2 border-white shadow-lg focus:border-none focus:outline-none" disabled />
                        </div>
                        <x-slot:actions>
                            <x-mary-button label="Previous" icon="o-backward"  class="bg-orange-900 btn-sm text-white" wire:click="previous('addresses')" spinner="previous('addresses')" />
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
                            <x-mary-button label="Previous" icon="o-backward"  class="bg-orange-900 btn-sm text-white" wire:click="previous('payments')" spinner="previous('payments')" />
                            <x-mary-button label="Submit" icon="o-arrow-up-circle"  class="bg-violet-200 btn-sm text-blue-900 dark:text-blue-900" wire:click="save" spinner="save"/>
                        </x-slot:actions>
                    </x-mary-card>
                </div>
            </div>
        </x-mary-form>
    </x-mary-drawer>

    <x-mary-drawer wire:model="filtersDrawer" title="Filters" separator with-close-button close-on-escape class="w-11/12 lg:w-3/4 md:w-1/2">

    </x-mary-drawer>

</div>



