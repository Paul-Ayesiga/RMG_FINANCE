<?php

namespace App\Livewire\Actions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomerLogout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        // if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        // } else {
        //     Auth::guard('admin')->logout();
        // }

        Session::invalidate();
        Session::regenerateToken();
        // return redirect()->route('login-customer');
    }
}
