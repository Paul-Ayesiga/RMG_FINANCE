<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {


        Session::invalidate();
        Session::regenerateToken();

        Auth::logout();

        Session::forget('url.intended');

    }
}
