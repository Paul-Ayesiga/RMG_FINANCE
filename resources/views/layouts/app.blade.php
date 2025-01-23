<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" sizes="512x512" href="{{ asset('logos/rmg.png') }}" type="image/png">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>
    <link rel="stylesheet" href="node_modules/@fortawesome/fontawesome-free/css/all.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
        {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        {{-- Vanilla Calendar --}}
    <script src="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/vanilla-calendar-pro@2.9.6/build/vanilla-calendar.min.css" rel="stylesheet">
        {{-- Flatpickr  --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  {{-- <link href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.min.css" rel="stylesheet" /> --}}

  </style>
</head>
<body class="min-h-screen font-sans bg-fuchsia-50 dark:bg-gradient-to-l from-gray-700 via-zinc-800 to-gray-900">

    {{-- NAVBAR mobile only --}}
    <x-mary-nav  sticky full-width class="bg-fuchsia-50  dark:bg-zinc-950 z-20 border-none">
        <x-slot:brand>
            <a href="{{route('dashboard')}}" wire:navigate>
            <x-app-brand />
            </a>
        </x-slot:brand>
        <x-slot:actions>
                <x-mary-theme-toggle class="btn btn-circle btn-xs btn-ghost"/>
                <livewire:layout.navigation />
                <label for="main-drawer" class="lg:hidden me-3">
                    <x-mary-icon name="o-bars-3" class="cursor-pointer" />
                </label>
        </x-slot:actions>
    </x-mary-nav>

    {{-- MAIN --}}
    <x-mary-main  with-nav full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit z-50 pr-15">

            {{-- MENU --}}
            <x-mary-menu activate-by-route  active-bg-color="bg-gradient-to-r from-blue-300 to-blue-100 text-white font-bold shadow-lg">

                {{-- User --}}
                @if($user = auth()->user())
                        <!-- Profile Link -->
                        <x-mary-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded sm:hidden"  link="{{ route('profile')}}">
                            <x-slot:actions>
                                @if (isProfileIncomplete(Auth::user()))
                                    <x-heroicon-o-shield-exclamation class="h-6 w-6 text-red-600 animate-emphasis" solid/>
                                @else
                                    @if(Auth::user()->customer)
                                        <x-heroicon-o-check-badge class="h-6 w-6 text-blue-600 mr-3" solid/>
                                    @else
                                        <x-heroicon-o-check-badge class="h-6 w-6 text-amber-600" solid/>
                                    @endif
                                @endif

                            </x-slot:actions>
                        </x-mary-list-item>


                       <div class="lg:hidden flex justify-center items-center mt-5">
                            <x-mary-menu-separator />
                            <!-- Logout Button -->
                            <form method="POST" action="{{ route('logout') }}" class="mr-2">
                                @csrf
                                <x-mary-button type="submit" icon="o-power" class="bg-red-600 hover:bg-red-700 text-white btn-sm " tooltip-left="logoff" no-wire-navigate />
                            </form>

                            <!-- Border Between Buttons -->
                            @if (isProfileIncomplete(Auth::user()))
                                <div class="border-r-2 border-blue-900 h-6 mx-2"></div>
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600" solid/>
                            @else
                                <div class="border-r-2 border-blue-900 h-6 mx-2"></div>
                                <!-- Notification Button with Count and Drawer Toggle -->
                                <livewire:notifications-drawer />
                            @endif


                            <div class="border-r-2 border-blue-900 h-6 mx-2"></div>

                            <!-- Settings Button -->
                            <x-mary-button icon="o-wrench" class="bg-gray-600 text-white btn-sm dark:bg-emerald-600 dark:text-white" tooltip-left="settings"  link="{{ route('profile')}}" wire-navigate label="Settings" />
                        </div>

                    <x-mary-menu-separator />
                @endif

                @role('super-admin')
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                <x-mary-menu-item title="Clients" icon="o-users" link="{{ route('clients')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                <x-mary-menu-item title="Staff" icon="o-users" link="{{ route('staff')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>

                <x-mary-menu-sub title="Accounts" icon="o-credit-card" >
                    <x-mary-menu-item title="Account_types" icon="o-tag" link="{{ route('account-types')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                    <x-mary-menu-item title="Accounts Overview" icon="o-eye" link="{{ route('accounts-overview')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                </x-mary-menu-sub>
                 <x-mary-menu-sub title="Loan Management" icon="o-banknotes">
                    <x-mary-menu-item title="Loan Products" icon="o-tag" link="{{ route('loan-products')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                    <x-mary-menu-item title="loans" icon="o-eye" link="{{ route('loans')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                </x-mary-menu-sub>
                <x-mary-menu-sub title="Group Management" icon="o-users" >
                    <x-mary-menu-item title="groups" icon="o-tag" link=""  badge="beta" badge-classes="bg-blue-200"/>
                    <x-mary-menu-item title="Loans" icon="o-tag" link=""  badge="beta" badge-classes="bg-blue-200" />
                    <x-mary-menu-item title="Insurances" icon="o-eye" link=""  badge="beta" badge-classes="bg-blue-200"/>
                </x-mary-menu-sub>
                <x-mary-menu-sub title="Transactions" icon="o-arrows-right-left">
                    <x-mary-menu-item title="Transactions Overview" icon="o-eye" link="{{ route('transactions-overview')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                </x-mary-menu-sub>
                <x-mary-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-mary-menu-item title="Bank Charges" icon="o-wifi" link="{{ route('bank-charges')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                    <x-mary-menu-item title="Taxes" icon="o-archive-box" link="{{ route('taxes')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                    <x-mary-menu-item title="Roles & Permissions" icon="o-rectangle-group" link="{{ route('admin.roles')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                </x-mary-menu-sub>

                <x-mary-menu-item title="Send Notification" icon="o-bell-alert" link="{{ route('admin.notifications.send')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                <x-mary-menu-item title="Reports" icon="o-bell-alert" link="{{ route('admin.reports')}}"  style="font-weight:700" class="  text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900 text-zinc-500 group"/>
                @endrole

                @role('staff')
                <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('dashboard')}}"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                <x-mary-menu-item title="Clients" icon="o-users" link="{{ route('clients')}}"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                <x-mary-menu-sub title="Accounts" icon="o-credit-card">
                    <x-mary-menu-item title="Account_types" icon="o-tag" link="{{ route('account-types')}}"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                    <x-mary-menu-item title="Accounts Overview" icon="o-eye" link="{{ route('accounts-overview')}}"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                </x-mary-menu-sub>
                 <x-mary-menu-sub title="Loan Management" icon="o-banknotes">
                    <x-mary-menu-item title="Loan Products" icon="o-tag" link="{{ route('loan-products')}}"  style="font-weight:700" class="text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                    <x-mary-menu-item title="loans" icon="o-eye" link="{{ route('loans')}}"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                </x-mary-menu-sub>
                <x-mary-menu-sub title="Group Management" icon="o-users" >
                    <x-mary-menu-item title="groups" icon="o-tag" link=""  badge="beta"/>
                    <x-mary-menu-item title="Loans" icon="o-tag" link=""  badge="beta"/>
                    <x-mary-menu-item title="Insurances" icon="o-eye" link=""  badge="beta"/>
                </x-mary-menu-sub>
                <x-mary-menu-sub title="Transactions" icon="o-arrows-right-left">
                    <x-mary-menu-item title="Transactions Overview" icon="o-eye" link="{{ route('transactions-overview')}}"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                </x-mary-menu-sub>
                @endrole

                @role('customer')
                    @php
                        $customer = Auth::user()->customer;
                        // Check if any required field is empty
                        $isCustomerIncomplete = !$customer || empty($customer->date_of_birth) || empty($customer->gender) || empty($customer->phone_number) || empty($customer->address) ||
                                                empty($customer->occupation) || empty($customer->employer) || empty($customer->annual_income) || empty($customer->marital_status);
                    @endphp
                        <x-mary-menu-item title="Dashboard" icon="o-home" link="{{ route('customer-dashboard')}}"  style="font-weight:700" class="text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900"/>
                    @if(!$isCustomerIncomplete)
                        <x-mary-menu-item title="My Accounts" icon="o-credit-card" link="{{ route('my-accounts')}}" wire:current="bg-gradient-to-r from-blue-300 to-blue-100 text-white font-bold shadow-lg"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                        <x-mary-menu-item title="My Loans" icon="o-banknotes" link="{{ route('my-loans')}}" wire:current="bg-gradient-to-r from-blue-300 to-blue-100 text-white font-bold shadow-lg"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                        <x-mary-menu-item title="Set Scheduled or Standing Order" icon="o-clock" link="{{ route('standing-order')}}" wire:current="bg-gradient-to-r from-blue-300 to-blue-100 text-white font-bold shadow-lg"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                        <x-mary-menu-item title="Groups" icon="o-users" link="{{ route('group.management')}}" wire:current="bg-gradient-to-r from-blue-300 to-blue-100 text-white font-bold shadow-lg"  badge="beta" badge-classes="bg-blue-200"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                        <x-mary-menu-item title="RMG PAY" icon="o-rectangle-group" link="{{ route('rmgpay')}}" badge="NEW" badge-classes="bg-gradient-to-r from-pink-500 via-red-500 to-yellow-500 text-white font-bold animate-pulse shadow-lg" class="mt-auto mb-4"  style="font-weight:700" class=" text-sm font-medium leading-6 rounded-md hover:bg-zinc-200/70 hover:text-zinc-900  group"/>
                    @endif
                @endrole
            </x-mary-menu>
        </x-slot:sidebar>


        {{-- The `$slot` goes here --}}
        <x-slot:content>
            <div class="w-full h-full bg-fuchsia-100  rounded-tl-xl border-l border-zinc-200 dark:bg-inherit p-5 ">
                {{ $slot }}
            </div>
            {{-- @livewire('chatbot') --}}
        </x-slot:content>

    </x-mary-main>

    {{--  TOAST area --}}
    <x-mary-toast />
    <x-mary-spotlight />
    <x-wireui-notifications  z-index="z-50" />
    <wireui:scripts />
    <script>
        window.userId = @json(auth()->id()); // Pass the logged-in user's ID to JavaScript
    </script>

    {{-- <script src="toastr.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/gesture" defer></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.plugin(Gesture)
    })

</script>
</body>
</html>
