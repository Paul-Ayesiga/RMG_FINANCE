<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StandingOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessStandingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'standing-orders:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process all standing orders that are due for execution';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        // Fetch all active standing orders that are due for execution
        $standingOrders = StandingOrder::where('status', 'active')
        ->where('start_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                ->orWhere('end_date', '>=', $today);
            })
            ->get();

        foreach ($standingOrders as $order) {
            if ($this->isDueToday($order, $today)) {
                $this->executeStandingOrder($order);
            }
        }

        $this->info('Standing orders processed successfully.');
    }


    private function isDueToday($order, $today)
    {
        $start = Carbon::parse($order->start_date);

        switch ($order->frequency) {
            case 'daily':
                return true;
            case 'weekly':
                return $start->diffInWeeks($today) % 1 === 0;
            case 'monthly':
                return $start->day === $today->day;
            case 'yearly':
                return $start->isSameDay($today);
        }

        return false;
    }

    private function executeStandingOrder($order)
    {
        $hostAccount = $order->host_account;

        if (!$hostAccount) {
            $this->error("Standing order ID {$order->id} has no valid host account.");
            return;
        }

        $totalAmount = $order->amount;

        // Check if the host account has sufficient balance
        if ($hostAccount->balance < $totalAmount) {
            $this->error("Insufficient balance in host account ID {$hostAccount->id} for standing order ID {$order->id}.");
            return;
        }

        DB::transaction(function () use ($order, $hostAccount, $totalAmount) {
            // Process accounts
            foreach ($order->accounts as $account) {
                try {
                    // Transfer funds using the Account model's `transfer` method
                    $transaction = $hostAccount->transfer($account, $totalAmount / $order->accounts->count());

                    $this->info("Transferred funds to account ID: {$account->id}. Transaction ID: {$transaction->id}");
                } catch (\Exception $e) {
                    $this->error("Error transferring funds to account ID: {$account->id}. Reason: {$e->getMessage()}");
                }
            }

            // Process beneficiaries
            foreach ($order->beneficiaries as $beneficiary) {
                try {
                    // For beneficiaries, log the transfer
                    // Implement logic to integrate external banking APIs if needed
                    $this->info("Funds allocated for external beneficiary: {$beneficiary->account_number}");
                } catch (\Exception $e) {
                    $this->error("Error allocating funds for beneficiary ID: {$beneficiary->id}. Reason: {$e->getMessage()}");
                }
            }

            // Optional: Log successful execution
            $this->info("Standing order ID {$order->id} executed successfully.");
        });
    }

}
