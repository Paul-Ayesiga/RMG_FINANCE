<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return redirect('login');
        }

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user has the specified role
        // if (!$user->hasRole($role)) {
        //     // Redirect or abort with a 403 error if the role does not match
        //     return abort(403, 'Unauthorized access.');
        // }

        // Allow request to proceed if the user has the required role
        return $next($request);
    }
}
