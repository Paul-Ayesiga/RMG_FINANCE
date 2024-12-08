<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;

class ProtectUserAccount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $AuthenticatedUser = Auth::id();

        $Account = $request->route('account');
        $AccountId = $Account->id;

        $VisitedAccount = Account::where('id',$AccountId)->first();

        if($VisitedAccount == null){
            return redirect()->route('my-accounts');
        }else{
            if($AuthenticatedUser == $VisitedAccount->customer_id){
                return $next($request);
            }
        }
            return redirect()->route('my-accounts');
            abort(403);


    }
}
