<div>
    <div class="breadcrumbs text-sm mb-2">
        <ul>
            <li><a>Home</a></li>
            <li><a>Dashboard</a></li>
        </ul>
    </div>
        {{-- Finance overview--}}
    <h1 class="text-2xl italic font-sans font-extrabold text-center">Finance Overview</h1>
    <div class="grid grid-cols-1 gap-4 px-4 mt-8 sm:grid-cols-3 sm:px-8">
        <!-- Deposits -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-green-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m4-4v8m4-8v8m4-8v8" />
                </svg>
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Deposits</h3>
                <p class="text-3xl dark:text-white">
                    <small>UGX</small> {{ $monthlyStats['deposits']['current'] >= 1000000000
                        ? number_format($monthlyStats['deposits']['current'] / 1000000000, 1) . 'B'
                        : ($monthlyStats['deposits']['current'] >= 1000000
                            ? number_format($monthlyStats['deposits']['current'] / 1000000, 1) . 'M'
                            : number_format($monthlyStats['deposits']['current'])) }}
                </p>
                <span class="text-xs {{ $monthlyStats['deposits']['trend'] === 'increase' ? 'text-green-500' : 'text-red-500' }}">
                    {{ $monthlyStats['deposits']['percentage'] }}%
                    {{ $monthlyStats['deposits']['trend'] === 'increase' ? '↑' : '↓' }}
                </span>
            </div>
        </div>

        <!-- Withdrawals -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Withdrawals</h3>
                <p class="text-3xl dark:text-white">
                     <small>UGX</small> {{ $monthlyStats['withdrawals']['current'] >= 1000000000
                        ? number_format($monthlyStats['withdrawals']['current'] / 1000000000, 1) . 'B'
                        : ($monthlyStats['withdrawals']['current'] >= 1000000
                            ? number_format($monthlyStats['withdrawals']['current'] / 1000000, 1) . 'M'
                            : number_format($monthlyStats['withdrawals']['current'])) }}
                </p>
                <span class="text-xs {{ $monthlyStats['withdrawals']['trend'] === 'increase' ? 'text-green-500' : 'text-red-500' }}">
                    {{ $monthlyStats['withdrawals']['percentage'] }}%
                    {{ $monthlyStats['withdrawals']['trend'] === 'increase' ? '↑' : '↓' }}
                </span>
            </div>
        </div>

        <!-- Transfers -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-blue-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Transfers</h3>
                <p class="text-3xl dark:text-white">
                     <small>UGX</small> {{ $monthlyStats['transfers']['current'] >= 1000000000
                        ? number_format($monthlyStats['transfers']['current'] / 1000000000, 1) . 'B'
                        : ($monthlyStats['transfers']['current'] >= 1000000
                            ? number_format($monthlyStats['transfers']['current'] / 1000000, 1) . 'M'
                            : number_format($monthlyStats['transfers']['current'])) }}
                </p>
                <span class="text-xs {{ $monthlyStats['transfers']['trend'] === 'increase' ? 'text-green-500' : 'text-red-500' }}">
                    {{ $monthlyStats['transfers']['percentage'] }}%
                    {{ $monthlyStats['transfers']['trend'] === 'increase' ? '↑' : '↓' }}
                </span>
            </div>
        </div>
    </div>

    @role('super-admin')
    <div class="grid grid-cols-1 gap-4 px-4 mt-4 sm:px-8">
        <!-- Wallet Balance -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-yellow-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Wallet Balance</h3>
                <p class="text-3xl dark:text-white">
                     <small>UGX</small> {{ $monthlyStats['wallet_balance']['current'] >= 1000000000
                        ? number_format($monthlyStats['wallet_balance']['current'] / 1000000000, 1) . 'B'
                        : ($monthlyStats['wallet_balance']['current'] >= 1000000
                            ? number_format($monthlyStats['wallet_balance']['current'] / 1000000, 1) . 'M'
                            : number_format($monthlyStats['wallet_balance']['current'])) }}
                </p>
                <span class="text-xs {{ $monthlyStats['wallet_balance']['trend'] === 'increase' ? 'text-green-500' : 'text-red-500' }}">
                    {{ $monthlyStats['wallet_balance']['percentage'] }}%
                    {{ $monthlyStats['wallet_balance']['trend'] === 'increase' ? '↑' : '↓' }}
                </span>
            </div>
        </div>
    </div>
    @endrole
    <hr class="border-dashed mt-5"/>
    {{-- end of finance overview stat --}}

    @role('super-admin')
    {{-- Human Resource --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4 sm:px-8 mt-5">
        <!-- Total Page Views -->
        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Total Active Users</div>
            <div class="stat-value text-primary dark:text-white">
                {{ $loggedInUsers }}
            </div>
            <div class="stat-desc dark:text-white">21% more than last month</div>
        </div>

        <!-- Customers -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-green-400"><img src="{{asset('icons/customers.svg')}}" class="h-12 w-12"></div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Customers</h3>
                <p class="text-3xl dark:text-white">
                    {{ $customers >= 1000000
                        ? number_format($customers / 1000000, 1) . 'M'
                        : ($customers >= 1000
                            ? number_format($customers / 1000, 1) . 'K'
                            : $customers) }}
                </p>
            </div>
        </div>
        {{-- staff --}}
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-blue-400">
               <img src="{{asset('icons/customers.svg')}}" class="h-12 w-12">
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Staff</h3>
                <p class="text-3xl dark:text-white">
                    {{ $staff >= 1000000
                        ? number_format($staff / 1000000, 1) . 'M'
                        : ($staff >= 1000
                            ? number_format($staff / 1000, 1) . 'K'
                            : $staff) }}
                </p>
            </div>
        </div>
    </div>
    <hr class="border-dashed mt-5"/>
    @endrole


    {{-- accounts--}}
    <h1 class="text-2xl font-sans italic text-pretty text-center">Accounts</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4 sm:px-8 mt-5">
        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Account Types</div>
            <div class="stat-value text-success">{{ $accountTypes }}</div>
            <div class="stat-desc">Available account types</div>
        </div>

        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Pending Accounts</div>
            <div class="stat-value text-warning">
                {{ $pendingAccounts >= 1000000
                    ? number_format($pendingAccounts / 1000000, 1) . 'M'
                    : ($pendingAccounts >= 1000
                        ? number_format($pendingAccounts / 1000, 1) . 'K'
                        : $pendingAccounts) }}
            </div>
            <div class="stat-desc">Awaiting approval</div>
        </div>

        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Approved Accounts</div>
            <div class="stat-value text-info">
                {{ $approvedAccounts >= 1000000
                    ? number_format($approvedAccounts / 1000000, 1) . 'M'
                    : ($approvedAccounts >= 1000
                        ? number_format($approvedAccounts / 1000, 1) . 'K'
                        : $approvedAccounts) }}
            </div>
            <div class="stat-desc">Active accounts</div>
        </div>
    </div>
    <hr class="border-dashed mt-5"/>
    {{-- end of accounts stat --}}

     {{-- Loans--}}
    <h1 class="text-2xl italic font-sans text-center">Loans</h1>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 px-4 sm:px-8 mt-5">
        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Loan Types</div>
            <div class="stat-value text-success">{{ $loanTypes }}</div>
            <div class="stat-desc">Available loan products</div>
        </div>

        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Pending Loans</div>
            <div class="stat-value text-warning">
                {{ $pendingLoans >= 1000000
                    ? number_format($pendingLoans / 1000000, 1) . 'M'
                    : ($pendingLoans >= 1000
                        ? number_format($pendingLoans / 1000, 1) . 'K'
                        : $pendingLoans) }}
            </div>
            <div class="stat-desc">Awaiting approval</div>
        </div>

        <div class="stat dark:bg-inherit dark:text-white dark:shadow-white shadow p-4 bg-white rounded-lg">
            <div class="stat-title">Approved Loans</div>
            <div class="stat-value text-info">
                {{ $approvedLoans >= 1000000
                    ? number_format($approvedLoans / 1000000, 1) . 'M'
                    : ($approvedLoans >= 1000
                        ? number_format($approvedLoans / 1000, 1) . 'K'
                        : $approvedLoans) }}
            </div>
            <div class="stat-desc">Active loans</div>
        </div>
    </div>
    <hr class="border-dashed mt-5"/>
    {{-- end of accounts stat --}}


    {{-- Active Stats Overview --}}
    <h1 class="text-2xl italic font-sans font-extrabold text-center mt-8">Active Status Overview</h1>
    <div class="grid grid-cols-1 gap-4 px-4 mt-8 sm:grid-cols-2 sm:px-8">
        <!-- Active Accounts -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-emerald-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Active Accounts</h3>
                <p class="text-3xl dark:text-white">
                    {{ $activeAccounts >= 1000000
                        ? number_format($activeAccounts / 1000000, 1) . 'M'
                        : ($activeAccounts >= 1000
                            ? number_format($activeAccounts / 1000, 1) . 'K'
                            : number_format($activeAccounts)) }}
                </p>
                <span class="text-xs text-emerald-500">
                    {{ round(($activeAccounts / max($totalAccounts, 1)) * 100, 1) }}% of total
                </span>
            </div>
        </div>

        <!-- Active Loans -->
        <div class="flex items-center bg-white border rounded-sm overflow-hidden shadow dark:bg-inherit">
            <div class="p-4 bg-violet-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="px-4 text-gray-700">
                <h3 class="text-sm tracking-wider dark:text-white">Active Loans</h3>
                <p class="text-3xl dark:text-white">
                    {{ $activeLoans >= 1000000
                        ? number_format($activeLoans / 1000000, 1) . 'M'
                        : ($activeLoans >= 1000
                            ? number_format($activeLoans / 1000, 1) . 'K'
                            : number_format($activeLoans)) }}
                </p>
                <span class="text-xs text-violet-500">
                    {{ round(($activeLoans / max($totalLoans, 1)) * 100, 1) }}% of total
                </span>
            </div>
        </div>
    </div>

    <!-- Monitoring Charts Section -->
    <div class="mt-10 px-4 sm:px-8">
        <h2 class="text-2xl font-semibold mb-6 text-center dark:text-white">Performance Monitoring</h2>

        <!-- Transaction Monitoring -->
        <div class="bg-white p-6 rounded-lg shadow mb-6 dark:bg-gray-800">
            <x-mary-chart wire:model="transactionChart" />
        </div>

        <!-- Loan and Account Monitoring -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow dark:bg-gray-800">
                <x-mary-chart wire:model="loanChart" />
            </div>
            <div class="bg-white p-6 rounded-lg shadow dark:bg-gray-800">
                <x-mary-chart wire:model="accountChart" />
            </div>
        </div>
    </div>

</div>


