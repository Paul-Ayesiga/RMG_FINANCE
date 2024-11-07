@php
    $customer = Auth::user()->customer;
    // Check if any required field is empty
    $isCustomerIncomplete = !$customer || empty($customer->date_of_birth) || empty($customer->gender) || empty($customer->phone_number) || empty($customer->address) ||
                            empty($customer->occupation) || empty($customer->employer) || empty($customer->annual_income) || empty($customer->marital_status);
@endphp

<div class="h-screen overflow-y-auto">
@if($isCustomerIncomplete)

{{-- Show message if any of the fields are incomplete --}}
<x-mary-alert title="Complete Your Registration" description="Ensure your account is fully operational by completing your profile details." icon="o-exclamation-triangle" dismissible shadow class="mb-5 dark:shadow-lg dark:shadow-slate-300">
    <x-slot:actions>
        <x-mary-button icon="o-user" label="Go to Profile" link="/profile" class="bg-blue-100 hover:bg-blue-400 text-gray-700 font-medium" />
    </x-slot:actions>
</x-mary-alert>

<h1 class="font-bold text-5xl text-center me-3 mb-5 bg-gradient-to-r from-purple-500 to-pink-300 bg-clip-text text-transparent typing-effect">
    Welcome to RMG Finance,
    <span class="font-bold text-3xl text-center me-3 mb-5 bg-gradient-to-r from-purple-500 to-pink-300 bg-clip-text text-transparent">
        Your Financial Partner
    </span>
</h1>
<h3 class="text-lg font-serif text-center text-gray-600 mb-6 dark:text-white">
    You're just a few steps away from embarking on a successful financial journey with RMG Finance.
</h3>

<section class="relative bg-gray-50 dark:bg-gray-900">
    <div class="mt-2 md:mt-0 py-12 pb-6 sm:py-16 lg:pb-24">
        <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8 relative">
            <div class="relative mt-12 lg:mt-20">
                <div class="absolute inset-x-0 hidden xl:px-44 top-2 md:block md:px-20 lg:px-28">
                    <svg class="w-full" xmlns="http://www.w3.org/2000/svg" width="875" height="48" viewBox="0 0 875 48"
                        fill="none">
                        <path
                            d="M2 29C20.2154 33.6961 38.9915 35.1324 57.6111 37.5555C80.2065 40.496 102.791 43.3231 125.556 44.5555C163.184 46.5927 201.26 45 238.944 45C312.75 45 385.368 30.7371 458.278 20.6666C495.231 15.5627 532.399 11.6429 569.278 6.11109C589.515 3.07551 609.767 2.09927 630.222 1.99998C655.606 1.87676 681.208 1.11809 706.556 2.44442C739.552 4.17096 772.539 6.75565 805.222 11.5C828 14.8064 850.34 20.2233 873 24"
                            stroke="#D4D4D8" stroke-width="3" stroke-linecap="round" stroke-dasharray="1 12" />
                    </svg>
                </div>
                <div
                    class="relative grid grid-cols-1 text-center gap-y-8 sm:gap-y-10 md:gap-y-12 md:grid-cols-3 gap-x-12">
                    <!-- Step 1: Basic Registration -->
                    <div>
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-full shadow">
                            <span class="text-xl font-semibold text-gray-700 dark:text-gray-200">1</span>
                        </div>
                        <h3
                            class="mt-4 sm:mt-6 text-xl font-semibold leading-tight text-gray-900 dark:text-white md:mt-10">
                            Basic Registration
                        </h3>
                        <p class="mt-3 sm:mt-4 text-base text-gray-600 dark:text-gray-400">
                            Start by entering your basic details such as name, email, and identification number.
                        </p>
                    </div>
                    <!-- Step 2: More Details -->
                    <div>
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-full shadow">
                            <span class="text-xl font-semibold text-gray-700 dark:text-gray-200">2</span>
                        </div>
                        <h3
                            class="mt-4 sm:mt-6 text-xl font-semibold leading-tight text-gray-900 dark:text-white md:mt-10">
                            Provide More Details
                        </h3>
                        <p class="mt-3 sm:mt-4 text-base text-gray-600 dark:text-gray-400">
                            Complete your profile with additional information including date of birth, gender, address, marital status, and more.
                        </p>
                    </div>
                    <!-- Step 3: Benefits of RMG -->
                    <div>
                        <div
                            class="flex items-center justify-center w-16 h-16 mx-auto bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-full shadow">
                            <span class="text-xl font-semibold text-gray-700 dark:text-gray-200">3</span>
                        </div>
                        <h3
                            class="mt-4 sm:mt-6 text-xl font-semibold leading-tight text-gray-900 dark:text-white md:mt-10">
                            Enjoy RMG Benefits
                        </h3>
                        <p class="mt-3 sm:mt-4 text-base text-gray-600 dark:text-gray-400">
                            Access exclusive benefits, financial tools, and expert support designed to help you achieve your financial goals.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@else

<section class="bg-zinc-50">
    <div class="max-w-screen-xl px-4 mx-auto py-8">
        <div class="flex flex-row gap-4 justify-center">
            <a href="#_" class="transform hover:scale-125 duration-300">
                <img src="{{asset('banners\banner1.jpeg')}}"
                    class="rounded-lg rotate-3 hover:rotate-0 h-32 w-32 object-cover" alt="Image 1">
            </a>
            <a href="#_" class="transform hover:scale-125 duration-300">
                <img src="{{ asset('banners/banner4.jpeg')}}"
                    class="rounded-lg -rotate-3 hover:rotate-0 h-32 w-32 object-cover" alt="Image 2">
            </a>
            <a href="#_" class="transform hover:scale-125 duration-300">
                <img src="{{ asset('banners/banner2.jpeg')}}"
                    class="rounded-lg rotate-3 hover:rotate-0 h-32 w-32 object-cover" alt="Image 3">
            </a>
            <a href="#_" class="transform hover:scale-125 duration-300">
                <img src="{{ asset('banners/banner3.jpeg')}}"
                    class="rounded-lg -rotate-3 hover:rotate-0 h-32 w-32 object-cover" alt="Image 4">
            </a>
        </div>
    </div>
</section>

<!-- Finance Overview -->
<h1 class="text-2xl italic font-sans font-extrabold text-center">Finance Overview</h1>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 px-4 sm:px-8 mt-8">
    <!-- Deposit Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-green-400">
            <img src="{{ asset('icons/deposit.svg') }}" class="h-12 w-12" alt="Deposit Icon">
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Deposits</h3>
            <p class="text-3xl dark:text-white">{{ $stats['deposits']['count'] }}</p>
            <p class="text-sm dark:text-white">${{ 
                $stats['deposits']['amount'] >= 1000000000 
                    ? number_format($stats['deposits']['amount'] / 1000000000, 1) . 'B'
                    : ($stats['deposits']['amount'] >= 1000000 
                        ? number_format($stats['deposits']['amount'] / 1000000, 1) . 'M' 
                        : number_format($stats['deposits']['amount'], 2)) 
            }}</p>
        </div>
    </div>

    <!-- Withdrawal Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-blue-400">
            <img src="{{ asset('icons/withdrawal.svg') }}" class="h-12 w-12" alt="Withdrawal Icon">
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Withdrawals</h3>
            <p class="text-3xl dark:text-white">{{ $stats['withdrawals']['count'] }}</p>
            <p class="text-sm dark:text-white">${{ 
                $stats['withdrawals']['amount'] >= 1000000000 
                    ? number_format($stats['withdrawals']['amount'] / 1000000000, 1) . 'B'
                    : ($stats['withdrawals']['amount'] >= 1000000 
                        ? number_format($stats['withdrawals']['amount'] / 1000000, 1) . 'M' 
                        : number_format($stats['withdrawals']['amount'], 2)) 
            }}</p>
        </div>
    </div>

    <!-- Transfers Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-indigo-400">
            <img src="{{ asset('icons/card.svg') }}" class="h-12 w-20" alt="Transfers Icon">
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Transfers</h3>
            <p class="text-3xl dark:text-white">{{ $stats['transfers']['count'] }}</p>
            <p class="text-sm dark:text-white">${{ 
                $stats['transfers']['amount'] >= 1000000000 
                    ? number_format($stats['transfers']['amount'] / 1000000000, 1) . 'B'
                    : ($stats['transfers']['amount'] >= 1000000 
                        ? number_format($stats['transfers']['amount'] / 1000000, 1) . 'M' 
                        : number_format($stats['transfers']['amount'], 2)) 
            }}</p>
        </div>
    </div>

    <!-- Wallet Balance Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-red-400">
            <img src="{{ asset('icons/wallet.svg') }}" class="h-12 w-12" alt="Wallet Icon">
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Wallet Balance</h3>
            <p class="text-3xl dark:text-white">${{ 
                $stats['balance'] >= 1000000000 
                    ? number_format($stats['balance'] / 1000000000, 1) . 'B'
                    : ($stats['balance'] >= 1000000 
                        ? number_format($stats['balance'] / 1000000, 1) . 'M' 
                        : number_format($stats['balance'], 2)) 
            }}</p>
        </div>
    </div>
</div>

<!-- After existing stats cards -->
<h1 class="text-2xl italic font-sans font-extrabold text-center mt-8">Loan Overview</h1>

<div class="grid grid-cols-1 sm:grid-cols-4 gap-4 px-4 sm:px-8 mt-8">
    <!-- Active Loans Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-yellow-400">
            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Active Loans</h3>
            <p class="text-3xl dark:text-white">{{ $stats['loans']['active'] }}</p>
            <p class="text-sm dark:text-white">
                ${{ 
                $stats['loans']['total_amount'] >= 1000000000 
                    ? number_format($stats['loans']['total_amount'] / 1000000000, 1) . 'B'
                    : ($stats['loans']['total_amount'] >= 1000000 
                        ? number_format($stats['loans']['total_amount'] / 1000000, 1) . 'M' 
                        : number_format($stats['loans']['total_amount'], 2)) 
            }}
            </p>
        </div>
    </div>

    <!-- Approved Loans Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-green-400">
            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Approved Loans</h3>
            <p class="text-3xl dark:text-white">{{ $stats['loans']['approved'] }}</p>
        </div>
    </div>

    <!-- Rejected Loans Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-red-400">
            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Rejected Loans</h3>
            <p class="text-3xl dark:text-white">{{ $stats['loans']['rejected'] }}</p>
        </div>
    </div>

    <!-- Paid Loans Card -->
    <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
        <div class="p-4 bg-blue-400">
            <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="px-4 text-gray-700">
            <h3 class="text-sm tracking-wider dark:text-white">Paid Loans</h3>
            <p class="text-3xl dark:text-white">{{ $stats['loans']['paid'] }}</p>
            <p class="text-sm dark:text-white">${{ number_format($stats['loans']['paid_amount'], 2) }}</p>
        </div>
    </div>
</div>

<!-- Divider -->
<hr class="border-dashed mt-5" />

<!-- After the stats cards -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8 px-4 sm:px-8">
    <!-- Transaction Trends Chart -->
    <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-800">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold dark:text-white">Transaction Trends</h3>
            <x-mary-button label="Switch Chart Type" wire:click="switchTrendChart" spinner class="btn-sm" />
        </div>
        <div class="relative" style="min-height: 300px; width: 100%;">
            <x-mary-chart wire:model="transactionChart" />
        </div>
    </div>

    <!-- Distribution Chart -->
    <div class="bg-white p-4 rounded-lg shadow dark:bg-gray-800">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold dark:text-white">Transaction Distribution</h3>
            <x-mary-button label="Switch Chart Type" wire:click="switchDistributionChart" spinner class="btn-sm" />
        </div>
        <div class="relative" style="min-height: 300px; width: 100%;">
            <x-mary-chart wire:model="distributionChart" />
        </div>
    </div>
</div>

<!-- After your existing charts -->
<div class="mt-8 px-4 sm:px-8 pb-8">
    <div class="bg-white p-6 rounded-lg shadow dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold dark:text-white">Financial Calendar</h3>
            <x-mary-button 
                label="Add Event" 
                icon="o-plus"
                wire:click="openEventModal" 
                class="btn-primary" 
                spinner="openEventModal"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
            <!-- Calendar -->
            <div class="md:col-span-3">
                <div wire:poll.60s>
                    <x-mary-calendar 
                        :events="$events" 
                        locale="en" 
                        weekend-highlight 
                    />
                </div>
            </div>

            <!-- Events List -->
            <div class="md:col-span-2 bg-base-100 p-4 rounded-lg border dark:border-gray-700">
                <h4 class="font-semibold mb-4 dark:text-white">Upcoming Events</h4>
                <div class="space-y-3 max-h-[300px] overflow-y-auto">
                    @foreach($events as $event)
                        <div class="flex items-center justify-between p-2 bg-base-200 rounded-lg hover:bg-base-300 transition-colors dark:bg-gray-700 dark:hover:bg-gray-600">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate dark:text-white">
                                    {{ $event['label'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ isset($event['date']) 
                                        ? Carbon\Carbon::parse($event['date'])->format('M d, Y h:i A')
                                        : Carbon\Carbon::parse($event['range'][0])->format('M d, Y h:i A') }}
                                </p>
                                @if(isset($event['description']))
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                        {{ $event['description'] }}
                                    </p>
                                @endif
                            </div>
                            @if(isset($event['id']))
                                <x-mary-button 
                                    icon="o-trash"
                                    wire:click="deleteEvent({{ $event['id'] }})"
                                    class="btn-error btn-sm"
                                    spinner
                                />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Modal -->
<x-mary-modal wire:model="showEventModal" title="Add New Event">
    <form wire:submit="saveEvent" class="space-y-4">
        <!-- Event Label -->
        <div>
            <x-mary-input 
                label="Event Title" 
                wire:model="eventLabel" 
                placeholder="Enter event title"
                
            />
            @error('eventLabel') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Event Type (now as input) -->
        <div>
            <x-mary-select
                label="Event Type"
                wire:model="eventType"
                placeholder="Select event type"
                :options="$eventTypeOptions"
                option-label="name"
                option-value="id"
                
            >
                <x-slot:option>
                    <div class="flex items-center gap-2">
                        <span>@{{ option.icon }}</span>
                        <span>@{{ option.name }}</span>
                    </div>
                </x-slot:option>
            </x-mary-select>
            @error('eventType') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Event Description -->
        <div>
            <x-mary-textarea 
                label="Event Description" 
                wire:model="eventDescription" 
                placeholder="Enter event description"
                
            />
            @error('eventDescription') 
                <span class="text-red-500 text-sm">{{ $message }}</span> 
            @enderror
        </div>

        <!-- Event Date -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-mary-input 
                    type="datetime-local" 
                    label="Start Date & Time" 
                    wire:model="eventDate"
                    
                />
                @error('eventDate') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror
            </div>

            <div>
                <x-mary-input 
                    type="datetime-local" 
                    label="End Date & Time (Optional)" 
                    wire:model="eventEndDate"
                />
                @error('eventEndDate') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                @enderror
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end gap-x-4 mt-6">
            <x-mary-button 
                label="Cancel" 
                wire:click="closeEventModal"
                class="btn-outline"
                spinner="closeEventModal"
            />
            <x-mary-button 
                type="submit" 
                label="Save Event" 
                class="btn-primary"
                spinner="saveEvent"
            />
        </div>
    </form>
</x-mary-modal>

@endif
</div>

@script
    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.js"></script>
@endscript
