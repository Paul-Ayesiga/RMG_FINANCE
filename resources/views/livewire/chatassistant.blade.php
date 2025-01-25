<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Loan;
use App\Models\Customer;
use App\Models\User;

new class extends Component {
    public $messages = []; // Stores chat history
    public $userInput = ''; // Stores user input

    // Send user message
    public function sendMessage()
    {
        // Add user message to chat history
        $this->messages[] = ['role' => 'user', 'content' => $this->userInput];

        // Handle greetings and financial queries
        $response = $this->handleMessage($this->userInput);

        // Add AI response to chat history
        $this->messages[] = ['role' => 'assistant', 'content' => $response];

        // Clear user input
        $this->userInput = '';
    }

    // Handle user messages
    protected function handleMessage($message)
    {
        // Convert message to lowercase for easier matching
        $message = strtolower($message);

        // Handle greetings
        if ($this->isGreeting($message)) {
            return "Hello! I'm your financial assistant. How can I help you today?";
        }

        // Handle financial queries
        if ($this->isFinancialQuery($message)) {
            return $this->handleFinancialQuery($message);
        }

        // Default response for unrelated queries
        return "I'm here to assist with financial matters. Please ask about your loans, account balance, or other financial details.";
    }

    // Check if the message is a greeting
    protected function isGreeting($message)
    {
        $greetings = ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'];
        return in_array($message, $greetings);
    }

    // Check if the message is a financial query
    protected function isFinancialQuery($message)
    {
        $keywords = ['loan', 'balance', 'account', 'repayment', 'due', 'status'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    // Handle financial queries
    protected function handleFinancialQuery($message)
    {
        $user = auth()->user(); // Get the authenticated user
        $currentCurrency = User::where('id', $user->id)->pluck('currency')->first();

        // Query the database for user-specific data
        $loan = Loan::where('customer_id', $user->customer->id)->first();
        $accountBalance = $user->account_balance; // Assuming this field exists

        // Check for specific keywords in the message
        if (str_contains($message, 'loan')) {
            if ($loan) {
                // Fetch loan details
                $loanDetails = [
                    'amount' => $loan->amount,
                    'interest_rate' => $loan->interest_rate,
                    'total_payable' => $loan->total_payable,
                    'total_interest' => $loan->total_interest,
                ];

                // Convert amounts to the user's currency using your helper function
                $convertedLoanDetails = [
                    'amount' => convertCurrency($loan->amount, 'UGX', $currentCurrency),
                    'total_payable' => convertCurrency($loan->total_payable, 'UGX', $currentCurrency),
                    'total_interest' => convertCurrency($loan->total_interest, 'UGX', $currentCurrency),
                ];

                // Format amounts with 0 decimal places
                $formattedLoanDetails = [
                    'amount' => number_format($convertedLoanDetails['amount'], 0),
                    'total_payable' => number_format($convertedLoanDetails['total_payable'], 0),
                    'total_interest' => number_format($convertedLoanDetails['total_interest'], 0),
                ];

                // Fetch next payment details
                $nextPayment = $loan->schedules()
                    ->where('status', '!=', 'paid')
                    ->orderBy('due_date')
                    ->first();

                $nextPaymentAmount = $nextPayment ? number_format(convertCurrency($nextPayment->total_amount, 'UGX', $currentCurrency), 0) : null;

                // Prepare the response data
                $response = [
                    'type' => 'loan',
                    'currentCurrency' => $currentCurrency,
                    'status' => $loan->status,
                    'amount' => $formattedLoanDetails['amount'],
                    'interest_rate' => $loan->interest_rate,
                    'total_payable' => $formattedLoanDetails['total_payable'],
                    'total_interest' => $formattedLoanDetails['total_interest'],
                    'next_payment' => $nextPayment ? [
                        'amount' => $nextPaymentAmount,
                        'due_date' => $nextPayment->due_date->format('M d, Y'),
                    ] : null,
                ];

                return $response;
            } else {
                return [
                    'type' => 'message',
                    'content' => 'You don\'t have any active loans.'
                ];
            }
        }
    }


    // Call DeepSeek API for advanced responses
    protected function callDeepSeekAPI($message)
    {
        $apiKey = config('services.deepseek.key');
        $endpoint = 'https://api.deepseek.com/v1/chat/completions'; // Replace with actual endpoint

        // Include customer data for personalized responses
        $customer = auth()->user(); // Assuming the customer is authenticated
        $customerData = [
            'balance' => $customer->account_balance,
            'loan_status' => $customer->loan_status,
        ];

        // Prepare the prompt
        $prompt = "You are a financial assistant. The customer's account balance is {$customerData['balance']}, and their loan status is {$customerData['loan_status']}. Respond to the following message: {$message}";

        // Make the API request
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'model' => 'deepseek-chat', // Replace with the correct model name
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful financial assistant.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        // Handle the response
        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        } else {
            return 'Sorry, I am unable to process your request at the moment.';
        }
    }

}; ?>

<div x-data="{ open: false }" class="fixed bottom-4 right-4 z-50">
    <!-- Chatbot toggle button -->
    <button @click="open = !open" class="bg-blue-600 text-white p-4 rounded-full shadow-lg hover:bg-blue-700 transition-transform transform hover:scale-110">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
        </svg>
    </button>

    <!-- Chatbot window -->
    <div x-show="open" @click.away="open = false" class="fixed bottom-20 right-4 w-96 bg-white rounded-lg shadow-xl overflow-hidden flex flex-col" style="height: 500px;">
        <!-- Chatbot header -->
        <div class="bg-blue-600 text-white p-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">AI Assistant</h2>
            <button @click="open = false" class="text-white hover:text-gray-200 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Chatbot body -->
        <div class="flex-1 p-4 overflow-y-auto bg-gray-50">
            <div class="space-y-2">
                @foreach($messages as $message)
                    <div class="message {{ $message['role'] === 'user' ? 'user' : 'assistant' }}">
                        @if(is_array($message['content']) && $message['content']['type'] === 'loan')
                            <div class="bg-white rounded-lg shadow-md p-4">
                                <p class="text-gray-700">Loan Details:</p>
                                <ul>
                                    <li>Amount: {{ $message['content']['amount'] }} {{ $message['content']['currentCurrency'] }}</li>
                                    <li>Interest Rate: {{ $message['content']['interest_rate'] }}%</li>
                                    <li>Total Payable: {{ $message['content']['total_payable'] }} {{ $message['content']['currentCurrency'] }}</li>
                                    <li>Total Interest: {{ $message['content']['total_interest'] }} {{ $message['content']['currentCurrency'] }}</li>
                                    @if($message['content']['next_payment'])
                                        <li>Next Payment: {{ $message['content']['next_payment']['amount'] }} {{ $message['content']['currentCurrency'] }} due on {{ $message['content']['next_payment']['due_date'] }}</li>
                                    @endif
                                </ul>
                            </div>
                        @else
                            {{ $message['content'] }}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Chatbot input -->
        <div class="p-4 border-t bg-white">
            <input
                type="text"
                wire:model="userInput"
                wire:keydown.enter="sendMessage"
                placeholder="Type your message..."
                class="w-full p-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>
    </div>
</div>

@assets
<!-- Chat message styling -->
<style>
   .message {
    max-width: 80%;
    padding: 10px 16px;
    border-radius: 12px;
    margin-bottom: 8px;
    word-wrap: break-word;
}
.message.user {
    background-color: #3b82f6; /* Blue */
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}
.message.assistant {
    background-color: #f3f4f6; /* Light gray */
    color: #1f2937; /* Dark gray */
    margin-right: auto;
    border-bottom-left-radius: 4px;
}
</style>
@endassets

