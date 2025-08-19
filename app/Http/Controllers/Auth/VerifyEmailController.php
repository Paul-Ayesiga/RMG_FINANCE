<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if (auth()->user()->hasRole(['staff', 'super-admin', 'manager'])) {
                return redirect()->intended('/dashboard?verified=1');
            } elseif (auth()->user()->hasRole('customer')) {
                return redirect()->intended('/customer-dashboard?verified=1');
            } else {
                return redirect()->intended('/?verified=1');
            }
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        if (auth()->user()->hasRole(['staff', 'super-admin', 'manager'])) {
            return redirect()->intended('/dashboard?verified=1');
        } elseif (auth()->user()->hasRole('customer')) {
            return redirect()->intended('/customer-dashboard?verified=1');
        } else {
            return redirect()->intended('/?verified=1');
        }
    }
}
