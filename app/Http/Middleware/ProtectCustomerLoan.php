<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Loan;

class ProtectCustomerLoan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $AuthenticatedUser = Customer::where('user_id',Auth::id())->first();

        $Loan = $request->route('loan');

        $LoanId = $Loan->id;

        $VisitedLoan = Loan::where('id', $LoanId)->first();

        if ($VisitedLoan == null) {
            return redirect()->route('my-loans');
        } else {
            if ($AuthenticatedUser->id == $VisitedLoan->customer_id) {
                return $next($request);
            }
        }
        return redirect()->route('my-loans');
        abort(403);
    }
}
