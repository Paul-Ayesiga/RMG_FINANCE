<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\LoanChecks;

class CheckPendingLoans
{
    use LoanChecks;

    public function handle(Request $request, Closure $next)
    {
        if ($request->is('loans/*') || $request->is('api/loans/*')) {
            $loanCheck = $this->canApplyForLoan();
            if (!$loanCheck['can_apply']) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => $loanCheck['message']
                    ], 403);
                }
                
                return redirect()->back()->with('error', $loanCheck['message']);
            }
        }

        return $next($request);
    }
}
