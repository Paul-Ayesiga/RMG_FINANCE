<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\ExchangeRateService;

class UpdateExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-exchange-rates';

    /**
     * The console command description.
     *
     * @var string
     */

    private $currencyUpdater;

    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     *
     */


    public function handle(ExchangeRateService $exchangeRateService)
    {
        $this->info('Fetching latest exchange rates...');

        // Fetch and update the rates
        $exchangeRateService->fetchLatestRates();

        $this->info('Currency exchange rates updated successfully!');
    }
}
