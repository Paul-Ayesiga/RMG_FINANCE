<?php

namespace App\Livewire;

use Livewire\Component;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Auth;


class Chatbot extends Component
{
    public $messages = []; // To store chat history
    public $input = '';    // User input

    public function sendMessage()
    {
        if (empty($this->input)) return;

        // Add user message to chat history
        $this->messages[] = ['sender' => 'user', 'text' => $this->input];

        // Analyze user input
        $responseText = $this->generateResponse($this->input);

        // Add bot response to chat history
        $this->messages[] = ['sender' => 'bot', 'text' => $responseText];

        // Clear input field
        $this->input = '';
    }

    protected function generateResponse($input)
    {
        $user = Auth::user()->customer;

        if (!$user) {
            return "Please log in to access your account details.";
        }

        // Check for "balance" query
        if (stripos($input, 'balance') !== false) {
            $savingsBalance = $user->savings->sum('balance');
            return "Your total savings balance is $" . number_format($savingsBalance, 2) . ".";
        }

        // Check for "loan" query
        if (stripos($input, 'loan') !== false) {
            $loans = $user->loans;

            if ($loans->isEmpty()) {
                return "You have no active loans.";
            }

            $response = "Here are your loan details:\n";
            foreach ($loans as $loan) {
                // Check if there is a valid next payment date
                $nextEMI = $loan->last_payment_date
                    ? $loan->last_payment_date->format('Y-m-d')
                    : "N/A";

                // Format the loan details response
                $response .= "- Loan #{$loan->id}:\n";
                $response .= "  Balance: $" . number_format($loan->balance, 2) . "\n";
                $response .= "  Next EMI Due: " . $nextEMI . "\n";
                $response .= "  Status: {$loan->status}\n";
                $response .= "  Total Payable: $" . number_format($loan->total_payable, 2) . "\n";
                $response .= "  Interest Rate: {$loan->interest_rate}%\n";
                $response .= "  Term: {$loan->term} months ({$loan->payment_frequency})\n";

                // Fetch and include the schedule
                $schedules = $loan->schedules()->orderBy('due_date', 'asc')->get();
                if ($schedules->isNotEmpty()) {
                    $response .= "  Payment Schedule:\n";
                    foreach ($schedules as $schedule) {
                        $dueDate = $schedule->due_date->format('Y-m-d');
                        $status = ucfirst($schedule->status);
                        $response .= "    - Due Date: {$dueDate}\n";
                        $response .= "      Principal: $" . number_format($schedule->principal_amount, 2) . "\n";
                        $response .= "      Interest: $" . number_format($schedule->interest_amount, 2) . "\n";
                        $response .= "      Total Due: $" . number_format($schedule->total_amount, 2) . "\n";
                        $response .= "      Paid: $" . number_format($schedule->paid_amount, 2) . "\n";
                        $response .= "      Remaining: $" . number_format($schedule->remaining_amount, 2) . "\n";
                        $response .= "      Late Fee: $" . number_format($schedule->late_fee, 2) . "\n";
                        $response .= "      Status: {$status}\n";

                        if ($schedule->status === 'paid' && $schedule->paid_at) {
                            $paidAt = $schedule->paid_at->format('Y-m-d H:i:s');
                            $response .= "      Paid At: {$paidAt}\n";
                        }
                    }
                } else {
                    $response .= "  Payment Schedule: No upcoming payments.\n";
                }

                $response .= "\n"; // Add spacing between loans
            }

            return $response;
        }

        // Check for "transactions" query
        if (stripos($input, 'transactions') !== false) {
            $transactions = $user->transactions()->latest()->take(5)->get();

            if ($transactions->isEmpty()) {
                return "No recent transactions found.";
            }

            $response = "Here are your last 5 transactions:\n";
            foreach ($transactions as $transaction) {
                $transactionDate = $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : "Unknown date";
                $response .= "- " . ucfirst($transaction->type) . " of $" . number_format($transaction->amount, 2) . " on " . $transactionDate . ".\n";
            }
            return $response;
        }

        // Check for account status using account number
        if (preg_match('/account\s*number\s*:\s*(\d+)/i', $input, $matches)) {
            $accountNumber = $matches[1];
            $account = $user->accounts()->where('account_number', $accountNumber)->first();

            if (!$account) {
                return "No account found with account number $accountNumber.";
            }

            $status = $account->status ?? "Unknown";
            $balance = $account->balance ?? 0;

            return "Account #$accountNumber\nStatus: $status\nBalance: $" . number_format($balance, 2) . ".";
        }

        // Default to AI response
        try {
            $response = OpenAI::completions()->create([
                'model' => 'gpt-3.5-turbo',
                'prompt' => "You are a financial assistant. Respond to the following user query: " . $input,
                'max_tokens' => 150,
            ]);

            return trim($response->choices[0]->text);
        } catch (\Exception $e) {
            return "Sorry, I couldn't process your request at the moment. Please try again later.";
        }
    }


    public function render()
    {
        return view('livewire.chatbot');
    }
}
