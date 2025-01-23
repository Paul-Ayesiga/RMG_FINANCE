<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\Event;
use App\Models\User;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use WireUi\Traits\WireUiActions;

#[Lazy()]
class CustomerDashboard extends Component
{
    use WireUiActions;

    public $stats;

    public array $transactionChart = [
        'type' => 'line',
        'data' => [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Deposits',
                    'data' => [],
                    'borderColor' => '#4ade80', // green
                    'backgroundColor' => 'rgba(74, 222, 128, 0.5)', // transparent green
                    'fill' => false
                ],
                [
                    'label' => 'Withdrawals',
                    'data' => [],
                    'borderColor' => '#ef4444', // red
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)', // transparent red
                    'fill' => false
                ],
                [
                    'label' => 'Transfers',
                    'data' => [],
                    'borderColor' => '#818cf8', // indigo
                    'backgroundColor' => 'rgba(129, 140, 248, 0.5)', // transparent indigo
                    'fill' => false
                ]
            ]
        ],
        'options' => [
            'scales' => [
                'y' => [
                    'beginAtZero' => true
                ]
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top'
                ]
            ]
        ]
    ];

    public array $distributionChart = [
        'type' => 'pie',
        'data' => [
            'labels' => ['Deposits', 'Withdrawals', 'Transfers'],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [
                        'rgba(74, 222, 128, 0.8)', // green
                        'rgba(239, 68, 68, 0.8)',  // red
                        'rgba(129, 140, 248, 0.8)'  // indigo
                    ],
                    'borderColor' => [
                        '#4ade80', // green
                        '#ef4444', // red
                        '#818cf8'  // indigo
                    ],
                    'borderWidth' => 1
                ]
            ]
        ],
        'options' => [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'top'
                ]
            ]
        ]
    ];

    public $events = [];
    public $showEventModal = false;

    // Form properties
    public $eventLabel;
    public $eventType;
    public $eventDescription;
    public $eventDate;
    public $eventEndDate;

    // Define event type configurations
    protected $eventTypes = [
        'payment' => [
            'css' => '!bg-amber-400',
            'icon' => '⏰'
        ],
        'meeting' => [
            'css' => '!bg-blue-400',
            'icon' => '📅'
        ],
        'deadline' => [
            'css' => '!bg-red-400',
            'icon' => '⚠️'
        ],
        'disbursement' => [
            'css' => '!bg-emerald-400',
            'icon' => '💰'
        ],
        'other' => [
            'css' => '!bg-gray-400',
            'icon' => '📌'
        ]
    ];

    #[On('refresh')]
    public function mount()
    {
        $this->loadStats();
        $this->loadChartData();
        $this->loadEvents();
    }

    // #[On('refresh')]
    public function loadStats()
    {
        $customer = Auth::user()->customer;
        $accountIds = $customer->accounts()->pluck('id');

        $user = Auth::id();
        $currencyArray = User::where('id', $user)->get()->pluck('currency');
        $currency = $currencyArray[0];

        // Deposit Stats
        $depositStats = DB::table('transactions')
        ->whereIn('account_id', $accountIds)
            ->where('type', 'deposit')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Withdrawal Stats
        $withdrawalStats = DB::table('transactions')
        ->whereIn('account_id', $accountIds)
            ->where('type', 'withdrawal')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Transfer Stats
        $transferStats = DB::table('transactions')
        ->whereIn('account_id', $accountIds)
            ->where('type', 'transfer')
            ->selectRaw('COUNT(*) as count, SUM(amount) as total')
            ->first();

        // Loan Stats
        $loanStats = [
            'active' => $customer->loans()->where('status', 'active')->count(),
            'approved' => $customer->loans()->where('status', 'approved')->count(),
            'rejected' => $customer->loans()->where('status', 'rejected')->count(),
            'paid' => $customer->loans()->where('status', 'paid')->count(),
            'total_amount' => $customer->loans()
                ->whereIn('status', ['active'])
                ->sum('amount'),
            'paid_amount' => $customer->loans()
                ->where('status', 'paid')
                ->sum('amount')
        ];

        // Convert amounts to the selected currency
        $this->stats = [
            'deposits' => [
                'count' => $depositStats->count ?? 0,
                'amount' => convertCurrency($depositStats->total ?? 0, 'UGX', $currency) // Convert deposit amount
            ],
            'withdrawals' => [
                'count' => $withdrawalStats->count ?? 0,
                'amount' => convertCurrency($withdrawalStats->total ?? 0, 'UGX', $currency) // Convert withdrawal amount
            ],
            'transfers' => [
                'count' => $transferStats->count ?? 0,
                'amount' => convertCurrency($transferStats->total ?? 0, 'UGX', $currency) // Convert transfer amount
            ],
            'balance' => convertCurrency($customer->accounts()->sum('balance'), 'UGX', $currency), // Convert total balance
            'loans' => [
                'active' => $loanStats['active'],
                'approved' => $loanStats['approved'],
                'rejected' => $loanStats['rejected'],
                'paid' => $loanStats['paid'],
                'total_amount' => convertCurrency($loanStats['total_amount'], 'UGX', $currency), // Convert total loan amount
                'paid_amount' => convertCurrency($loanStats['paid_amount'], 'UGX', $currency) // Convert paid loan amount
            ]
        ];
    }


    // protected function loadChartData()
    // {
    //     $customer = Auth::user()->customer;
    //     $accountIds = $customer->accounts()->pluck('id');

    //     // Get last 6 months of transaction data
    //     $months = collect(range(5, 0))->map(function($i) {
    //         return Carbon::now()->subMonths($i)->format('M Y');
    //     });

    //     $transactions = DB::table('transactions')
    //         ->whereIn('account_id', $accountIds)
    //         ->whereDate('created_at', '>=', Carbon::now()->subMonths(6))
    //         ->selectRaw('EXTRACT(MONTH FROM created_at) as month')
    //         ->selectRaw('EXTRACT(YEAR FROM created_at) as year')
    //         ->selectRaw('type')
    //         ->selectRaw('SUM(amount) as total')
    //         ->groupBy('year', 'month', 'type')
    //         ->get();

    //     // Prepare data for line chart
    //     $deposits = array_fill(0, 6, 0);
    //     $withdrawals = array_fill(0, 6, 0);
    //     $transfers = array_fill(0, 6, 0);

    //     foreach ($transactions as $transaction) {
    //         $index = 5 - (Carbon::now()->startOfMonth()->diffInMonths(
    //             Carbon::create($transaction->year, $transaction->month, 1)
    //         ));

    //         if ($index >= 0 && $index < 6) {
    //             switch ($transaction->type) {
    //                 case 'deposit':
    //                     $deposits[$index] = $transaction->total;
    //                     break;
    //                 case 'withdrawal':
    //                     $withdrawals[$index] = $transaction->total;
    //                     break;
    //                 case 'transfer':
    //                     $transfers[$index] = $transaction->total;
    //                     break;
    //             }
    //         }
    //     }

    //     // Update line chart data
    //     $this->transactionChart['data']['labels'] = $months->toArray();
    //     $this->transactionChart['data']['datasets'][0]['data'] = $deposits;
    //     $this->transactionChart['data']['datasets'][1]['data'] = $withdrawals;
    //     $this->transactionChart['data']['datasets'][2]['data'] = $transfers;

    //     // Update pie chart data
    //     $this->distributionChart['data']['datasets'][0]['data'] = [
    //         $this->stats['deposits']['amount'],
    //         $this->stats['withdrawals']['amount'],
    //         $this->stats['transfers']['amount']
    //     ];
    // }

    // #[On('refresh')]
    public function loadChartData()
    {
        $customer = Auth::user()->customer;
        $accountIds = $customer->accounts()->pluck('id');

        // Get last 6 months of transaction data
        $months = collect(range(5, 0))->map(function ($i) {
            return Carbon::now()->subMonths($i)->format('M Y');
        });

        $user = Auth::id();
        $currencyArray = User::where('id', $user)->get()->pluck('currency');
        $currency = $currencyArray[0];

        $transactions = DB::table('transactions')
        ->whereIn('account_id', $accountIds)
            ->whereDate('created_at', '>=', Carbon::now()->subMonths(6))
            ->selectRaw('EXTRACT(MONTH FROM created_at) as month')
            ->selectRaw('EXTRACT(YEAR FROM created_at) as year')
            ->selectRaw('type')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('year', 'month', 'type')
            ->get();

        // Prepare data for line chart
        $deposits = array_fill(0, 6, 0);
        $withdrawals = array_fill(0, 6, 0);
        $transfers = array_fill(0, 6, 0);

        foreach ($transactions as $transaction) {
            $index = 5 - (Carbon::now()->startOfMonth()->diffInMonths(
                Carbon::create($transaction->year, $transaction->month, 1)
            ));

            if ($index >= 0 && $index < 6) {
                $convertedAmount = convertCurrency($transaction->total, 'UGX', $currency); // Convert amount to selected currency

                switch ($transaction->type) {
                    case 'deposit':
                        $deposits[$index] = $convertedAmount;
                        break;
                    case 'withdrawal':
                        $withdrawals[$index] = $convertedAmount;
                        break;
                    case 'transfer':
                        $transfers[$index] = $convertedAmount;
                        break;
                }
            }
        }

        // Update line chart data with converted amounts
        $this->transactionChart['data']['labels'] = $months->toArray();
        $this->transactionChart['data']['datasets'][0]['data'] = $deposits;
        $this->transactionChart['data']['datasets'][1]['data'] = $withdrawals;
        $this->transactionChart['data']['datasets'][2]['data'] = $transfers;

        // Update pie chart data with converted amounts
        $this->distributionChart['data']['datasets'][0]['data'] = [
            convertCurrency($this->stats['deposits']['amount'], 'UGX', $currency), // Convert deposits
            convertCurrency($this->stats['withdrawals']['amount'], 'UGX', $currency), // Convert withdrawals
            convertCurrency($this->stats['transfers']['amount'], 'UGX', $currency)  // Convert transfers
        ];
    }


    public function switchTrendChart()
    {
        $type = $this->transactionChart['type'] === 'line' ? 'bar' : 'line';
        Arr::set($this->transactionChart, 'type', $type);

        $this->loadEvents();
    }

    public function switchDistributionChart()
    {
        $type = $this->distributionChart['type'] === 'pie' ? 'doughnut' : 'pie';
        Arr::set($this->distributionChart, 'type', $type);

        $this->loadEvents();
    }

    public function loadEvents()
    {
        $user = Auth::id();
        $currencyArray = User::where('id', $user)->get()->pluck('currency');
        $currency = $currencyArray[0];
        
        $customer = Auth::user()->customer;
        $this->events = [];

        // Get active loans and their schedules
        $activeLoans = $customer->loans()
            ->with(['schedules' => function ($query) {
                $query->where('status', 'pending')
                ->orderBy('due_date', 'asc');
            }])
            ->where('status', 'active')
            ->get();

        // Add loan-related events
        foreach ($activeLoans as $loan) {
            // Add loan disbursement date
            $this->events[] = [
                'label' => 'Loan Disbursed: #' . $loan->reference_number,
                'description' => "Amount: $" . number_format(convertCurrency($loan->amount,'UGX', $currency), 2) .
                    "\nDisbursed on: " . Carbon::parse($loan->disbursement_date)->format('M d, Y h:i A'),
                'css' => '!bg-emerald-200',
                'date' => Carbon::parse($loan->disbursement_date),
            ];

            // Add pending schedules
            foreach ($loan->schedules as $schedule) {
                $dueStatus = $this->getPaymentDueStatus($schedule->due_date);
                $statusLabel = match ($dueStatus) {
                    'overdue' => '⚠️ OVERDUE',
                    'upcoming' => '⏰ DUE SOON',
                    'future' => '📅 SCHEDULED'
                };

                $this->events[] = [
                    'label' => $statusLabel . ' - Payment Due: #' . $loan->reference_number,
                    'description' => "Amount Due: $" . number_format(convertCurrency($schedule->total_amount,'UGX', $currency), 2) .
                        "\nLoan Type: " . ucwords(str_replace('_', ' ', $loan->loanProduct->name)),
                    'css' => $this->getPaymentScheduleColor($schedule->due_date),
                    'date' => Carbon::parse($schedule->due_date),
                ];
            }
        }

        // Get user-created events
        $userEvents = $customer->events()->get();
        foreach ($userEvents as $event) {
            // Get the configuration for the event type
            $typeConfig = $this->eventTypes[$event->type];

            // Check if the event has an end date
            if ($event->end_date) {
                $this->events[] = [
                    'id' => $event->id,
                    'label' => $typeConfig['icon'] . ' ' . $event->label,
                    'description' => $event->description,
                    'range' => [
                        Carbon::parse($event->start_date),
                        Carbon::parse($event->end_date)
                    ],
                    'css' => $typeConfig['css'],
                ];
            } else {
                $this->events[] = [
                    'id' => $event->id,
                    'label' => $typeConfig['icon'] . ' ' . $event->label,
                    'description' => $event->description,
                    'date' => Carbon::parse($event->start_date),
                    'css' => $typeConfig['css'],
                ];
            }
        }

        // Sort events by date
        $this->events = collect($this->events)->sortBy(function ($event) {
            return isset($event['date'])
            ? Carbon::parse($event['date'])
            : Carbon::parse($event['range'][0]);
        })->values()->all();
    }


    protected function getPaymentDueStatus($dueDate)
    {
        $dueDate = Carbon::parse($dueDate);
        $today = Carbon::today();

        if ($dueDate->isPast()) {
            return 'overdue';
        }

        if ($dueDate->diffInDays($today) <= 7) {
            return 'upcoming';
        }

        return 'future';
    }

    protected function getPaymentScheduleColor($dueDate)
    {
        $status = $this->getPaymentDueStatus($dueDate);

        return match($status) {
            'overdue' => '!bg-red-200',
            'upcoming' => '!bg-amber-200',
            'future' => '!bg-blue-200',
        };
    }

    public function openEventModal()
    {
        $this->showEventModal = true;
        $this->resetEventForm();
    }

    public function closeEventModal()
    {
        $this->showEventModal = false;
        $this->reset(['eventLabel', 'eventType', 'eventDescription', 'eventDate', 'eventEndDate']);

        $this->notification()->send([
            'title' => 'Event Creation Cancelled',
            'icon' => 'info'
        ]);
         $this->loadEvents();
    }

    public function resetEventForm()
    {
        $this->reset(['eventLabel', 'eventType', 'eventDescription', 'eventDate', 'eventEndDate']);
    }

    public function saveEvent()
    {
        try {
            DB::beginTransaction();
            $this->validate([
                'eventLabel' => 'required',
                'eventType' => 'required|in:payment,meeting,deadline,disbursement,other',
                'eventDescription' => 'required',
                'eventDate' => 'required|date',
                'eventEndDate' => 'nullable|date|after:eventDate',
            ]);

            $typeConfig = $this->eventTypes[$this->eventType];

            // Create event in database
            Event::create([
                'customer_id' => Auth::user()->customer->id,
                'label' => $this->eventLabel,
                'description' => $this->eventDescription,
                'type' => $this->eventType,
                'start_date' => Carbon::parse($this->eventDate),
                'end_date' => $this->eventEndDate ? Carbon::parse($this->eventEndDate) : null,
            ]);

        DB::commit();

            // Clear form and close modal
            $this->reset(['eventLabel', 'eventType', 'eventDescription', 'eventDate', 'eventEndDate']);
            $this->showEventModal = false;

            // Refresh events
            $this->loadEvents();

            // Show success notification
            $this->notification()->send([
                'title' => 'Event Added Successfully',
                'icon' => 'success'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error
            // \Log::error('Failed to update account status: ' . $e->getMessage());
            $this->notification()->send([
                'title' => 'Failed to create event, Try again!',
                'icon' => 'error'
            ]);

        }
    }

    // Add method to delete events
    public function deleteEvent($eventId)
    {
        $event = Event::find($eventId);

        if ($event && $event->customer_id === Auth::user()->customer->id) {
            $event->delete();

            // Refresh events
            $this->loadEvents();

            // Show warning notification
            $this->notification()->send([
                'title' => 'Event Deleted',
                'icon' => 'warning'
            ]);


        } else {
            // Show error notitification
            $this->notification()->send([
                'title' => 'Unable to Delete Event',
                'icon' => 'error'
            ]);
        }
    }

    // Add validation error handling
    public function hydrate()
    {
        $this->resetErrorBag();
    }

    protected function onValidationError($message)
    {
        $this->notification()->send([
            'title' => 'Validation Error',
            'description' => $message,
            'icon' => 'error'
        ]);
    }

    protected function getEventTypeOptions()
    {
        return [
            [
                'id' => 'payment',
                'name' => 'Payment Reminder',
                'icon' => '⏰'
            ],
            [
                'id' => 'meeting',
                'name' => 'Meeting/Appointment',
                'icon' => '📅'
            ],
            [
                'id' => 'deadline',
                'name' => 'Deadline',
                'icon' => '⚠️'
            ],
            [
                'id' => 'disbursement',
                'name' => 'Loan Disbursement',
                'icon' => '💰'
            ],
            [
                'id' => 'other',
                'name' => 'Other',
                'icon' => '📌'
            ]
        ];
    }

    public function render()
    {
        return view('livewire.customer-dashboard', [
            'eventTypeOptions' => $this->getEventTypeOptions()
        ]);
    }
}
