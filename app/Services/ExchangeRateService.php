<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExchangeRateService
{
    // The API endpoint for fetching exchange rates
    private $apiUrl = 'https://api.exchangerate-api.com/v4/latest/UGX'; // Example API (replace with your actual API)

    // Fetch the latest exchange rates from the API
    public function fetchLatestRates()
    {
        // Fetch the latest rates using the API
        $response = Http::get($this->apiUrl);

        if ($response->successful()) {
            $rates = $response->json()['rates'];

            // Cache the new rates (set an expiration of 24 hours)
            Cache::put('currency_rates', $rates, now()->addHours(24));
        }
    }
}
