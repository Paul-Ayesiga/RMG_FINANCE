<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// use App\Services\ExchangeRateService;

class CurrencyController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'currency' => 'required|in:' . implode(',', array_keys(config('currencies.supported'))),
        ]);

        session(['currency' => $request->currency]);

        $user = Auth::id();

        $userCurrency = User::where('id',$user)->first();

        $userCurrency->update([
            'currency' => $request->currency
        ]);

        // dd('chabge');
        return redirect()->back();
    }


    // public function update(Request $request, ExchangeRateService $exchangeRateService)
    // {
    //     $request->validate([
    //         'currency' => 'required|in:' . implode(',', array_keys(config('currencies.supported'))),
    //     ]);

    //     // Store selected currency in the session
    //     session(['currency' => $request->currency]);

    //     // Fetch the latest rates
    //     $rates = $exchangeRateService->fetchRates();

    //     // Dynamically update the 'currencies.supported' config
    //     $supportedCurrencies = config('currencies.supported');
    //     foreach ($supportedCurrencies as $code => $currency) {
    //         if (isset($rates[$code])) {
    //             $supportedCurrencies[$code]['rate'] = $rates[$code];
    //         }
    //     }

    //     config(['currencies.supported' => $supportedCurrencies]);

    //     return redirect()->back()->with('success', 'Currency updated successfully with dynamic rates.');
    // }

}
