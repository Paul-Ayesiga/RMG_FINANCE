<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" href="{{asset('logos/rmg.png')}}" type="image/gif" />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])


        {{-- Flatpickr  --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        {{-- PhotoSwipe --}}
        <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.4.3/dist/umd/photoswipe.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.4.3/dist/umd/photoswipe-lightbox.umd.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/photoswipe@5.4.3/dist/photoswipe.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>

        @yield('styles')

        <style>
            .lg\:px-10 {
                padding-left: 0px;
                padding-right: 0px;
                /* 2.5rem */
            }
            .lg\:py-5 {
                padding-top: 0px;

                padding-bottom: 1.25rem;
                /* 1.25rem */
            }

        </style>
    </head>
    <body class="font-sans antialiased bg-gradient-to-r from-blue-100 to-gray-100 dark:bg-black">

        {{-- The navbar with `sticky` and `full-width` --}}
        {{-- The main content with `full-width` --}}
        <x-mary-main with-nav full-width>
            {{-- This is a sidebar that works also as a drawer on small screens --}}
            {{-- Notice the `main-drawer` reference here --}}
            @livewire('customers.customer-sidebar')
            {{-- The `$slot` goes here --}}
            <x-slot:content>
                {{ $slot }}
            </x-slot:content>
        </x-mary-main>

        {{--  TOAST area --}}
        <x-mary-toast />
        <x-mary-spotlight />
        <x-support-bubble />
    </body>
</html>
