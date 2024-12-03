<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

// Route::middleware(['guest'])->group(function () {
//     Volt::route('customer_register', 'client.register')
//         ->name('register-customer');

//     Volt::route('customer_login', 'client.login')
//         ->name('login-customer');

//     Volt::route('customer-forgot-password', 'client.forgot-password')
//         ->name('customer.password.request');

//     Volt::route('customer-reset-password/{token}', 'client.reset-password')
//         ->name('customer.password.reset');
// });

Route::middleware('guest')->group(function(){
    Volt::route('register', 'pages.auth.register')
        ->name('register');
    Volt::route('login', 'pages.auth.login')
        ->name('login');
    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');
    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

});

Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
