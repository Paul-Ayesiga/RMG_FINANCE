<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\SocialLoginController;
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


Route::middleware(['auth'])->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');

    Route::get('/logout', function () {
        return response("
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Page Not Found</title>
            <style>
                body {
                    background-color: #f4f4f9;
                    font-family: 'Arial', sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                    color: #333;
                }
                .error-container {
                    text-align: center;
                    background-color: white;
                    padding: 30px;
                    border-radius: 8px;
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    width: 80%;
                    max-width: 500px;
                }
                .error-container h1 {
                    color: #e74c3c;
                    font-size: 2.5rem;
                    margin-bottom: 20px;
                }
                .error-container p {
                    color: #555;
                    font-size: 1.1rem;
                }
                .home-link {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background-color: #3498db;
                    color: white;
                    text-decoration: none;
                    border-radius: 5px;
                    font-weight: bold;
                }
                .home-link:hover {
                    background-color: #2980b9;
                }
            </style>
        </head>
        <body>
            <div class='error-container'>
                <h1>404 - Not Found</h1>
                <p>You cannot directly access the logout route.</p>
                <a href='/' class='home-link'>Return to Home</a>
            </div>
        </body>
        </html>
    ", 404);
    });

    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});


Route::get('/authenticate/redirect/{driver}', [SocialLoginController::class, 'toProvider'])->where('driver', 'google|github|facebook')->name('socialite.redirect');
Route::get('/auth/{driver}/login', [SocialLoginController::class, 'handleCallback'])->name('socialite.callback');
