<?php

// if (!function_exists('convertCurrency')) {
//     function convertCurrency($amount, $fromCurrency = 'UGX', $toCurrency = null)
//     {
//         // Use session currency or default to 'UGX'
//         $toCurrency = $toCurrency ?? session('currency', config('currencies.default'));

//         // Get rates from config (fallback to 1 if not found)
//         $fromRate = config("currencies.supported.$fromCurrency.rate", 1);
//         $toRate = config("currencies.supported.$toCurrency.rate", 1);

//         // If converting from UGX to another currency
//         if ($fromCurrency === 'UGX' && $toCurrency !== 'UGX') {
//             // Convert UGX to the target currency by multiplying by the rate for the target currency
//             return $amount / $toRate;  // Example: 15,000 * 0.00027 for UGX to USD
//         }

//         // If converting to UGX from another currency
//         if ($toCurrency === 'UGX' && $fromCurrency !== 'UGX') {
//             // Convert the other currency to UGX by dividing by its rate
//             return $amount * $fromRate;  // Example: 4 USD / 0.00027 for USD to UGX
//         }

//         // General conversion logic for other currencies
//         return ($amount / $fromRate) * $toRate;
//     }
// }

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

// if (!function_exists('convertCurrency')) {
//     function convertCurrency($amount, $fromCurrency = 'UGX', $toCurrency = null)
//     {
//         // Use session currency or default to 'UGX'
//         $toCurrency = $toCurrency ?? session('currency', config('currencies.default'));

//         // Get the rates from the cache (fallback to config if not cached)
//         $rates = Cache::get('currency_rates', config('currencies.supported'));

//         // Get the conversion rates for the currencies
//         $fromRate = $rates[$fromCurrency] ?? 0;

//         $toRate = $rates[$toCurrency] ?? 0;

//         // Convert the amount
//         return ($amount / $fromRate) * $toRate;
//     }
// }

if (!function_exists('convertCurrency')) {
    function convertCurrency($amount, $fromCurrency = 'UGX', $toCurrency = null)
    {

        $user = Auth::id();
        $currentCurrency = User::where('id', $user)->get()->pluck('currency');


        // Use session currency or default to 'UGX'
        $toCurrency = $toCurrency ?? $currentCurrency[0];
        // $toCurrency = $toCurrency ?? session('currency', config('currencies.default'));

        // If the from and to currencies are the same, no conversion is needed
        if ($fromCurrency === $toCurrency) {
            return $amount;
        }

        // Get the rates from the cache (fallback to config if not cached)
        $rates = Cache::get('currency_rates', config('currencies.supported'));

        // Get the conversion rates for the currencies
        $fromRate = $rates[$fromCurrency] ?? 0;
        $toRate = $rates[$toCurrency] ?? 0;

        // If rates are not available for the currencies, return 0
        if ($fromRate == 0 || $toRate == 0) {
            return 0;
        }

        // Convert the amount
        return ($amount / $fromRate) * $toRate;
    }
}


if (!function_exists('convertCurrencyToUGX')) {
    function convertCurrencyToUGX($amount, $fromCurrency = 'UGX', $toCurrency = null)
    {

        // If the from and to currencies are the same, no conversion is needed
        if ($fromCurrency === $toCurrency[0]) {
            return $amount;
        }

        // Get the rates from the cache (fallback to config if not cached)
        $rates = Cache::get('currency_rates', config('currencies.supported'));

        // Get the conversion rates for the currencies
        $fromRate = $rates[$fromCurrency] ?? 0;
        $toRate = $rates[$toCurrency[0]] ?? 0;


        if ($toCurrency[0] !== 'UGX') {
            // Convert the other currency to UGX by dividing by its rate
            return $amount / $toRate;  // Example: 4 USD / 0.00027 for USD to UGX
        }
        // Convert the amount
        return ($amount / $fromRate) * $toRate;
    }
}


