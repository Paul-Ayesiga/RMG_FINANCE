<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Staff;
use App\Models\Event;
use App\Models\LoanProduct;
use App\Models\Loan;
use App\Models\Account;
use App\Models\AccountType;
use Carbon\Carbon;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Helpers\currencyHelper;


#[Lazy()]
class Dashboard extends Component
{
    public $myChart;
    public $category;
    public $recentEvents;
    public $monthlyStats;
    public $transactionChart;
    public $loanChart;
    public $accountChart;

    #[On('refresh')]
    public function mount()
    {
        $this->loadCharts();
        $this->calculateMonthlyStats();

    }

    protected function loadCharts()
    {

        $user = Auth::id();
        $currencyArray = User::where('id', $user)->get()->pluck('currency');
        $currency = $currencyArray[0];

        // Loan Distribution Chart (convert data)
        $loanDistributionData = $this->getLoanDistribution();
        $this->myChart = [
            'chart' => [
                'type' => 'pie',
                'height' => 300
            ],
            'title' => [
                'text' => 'Loan Distribution by Type',
                'align' => 'center'
            ],
            'series' => array_map(function ($amount) use ($currency) {
                return convertCurrency($amount, 'UGX', $currency); // Use helper function here
            }, $loanDistributionData),
            'labels' => LoanProduct::pluck('name')->toArray(),
            'legend' => [
                'position' => 'bottom'
            ]
        ];

        // Monthly Applications Chart (convert data)
        $loanData = $this->getMonthlyApplications('loan');
        $accountData = $this->getMonthlyApplications('account');
        $this->category = [
            'chart' => [
                'type' => 'bar',
                'height' => 300
            ],
            'title' => [
                'text' => 'Monthly Applications',
                'align' => 'center'
            ],
            'series' => [
                [
                    'name' => 'Loans',
                    'data' => array_map(function ($amount) use ($currency) {
                        return convertCurrency($amount, 'UGX', $currency); // Use helper function here
                    }, $loanData)
                ],
                [
                    'name' => 'Accounts',
                    'data' => array_map(function ($amount) use ($currency) {
                        return convertCurrency($amount, 'UGX', $currency); // Use helper function here
                    }, $accountData)
                ]
            ],
            'xaxis' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
            ]
        ];

        // Transaction Monitoring Chart (convert data)
        $transactionTypes = ['deposit', 'withdrawal', 'transfer'];
        $this->transactionChart = [
            'type' => 'line',
            'data' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => array_map(function ($type) use ($currency) {
                    return [
                        'label' => ucfirst($type),
                        'data' => array_map(function ($amount) use ($currency) {
                            return convertCurrency($amount, 'UGX', $currency); // Use helper function here
                        }, $this->getMonthlyTransactions($type)),
                        'borderColor' => $this->getTransactionColor($type),
                        'tension' => 0.1
                    ];
                }, $transactionTypes)
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                    'title' => [
                        'display' => true,
                        'text' => 'Transaction Monitoring'
                    ]
                ]
            ]
        ];

        // Loan Monitoring Chart (convert data)
        $this->loanChart = [
            'type' => 'bar',
            'data' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'datasets' => [
                    $this->createLoanDataset('new', $currency),
                    $this->createLoanDataset('active', $currency),
                    $this->createLoanDataset('completed', $currency),
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                    'title' => [
                        'display' => true,
                        'text' => 'Loan Status Monitoring'
                    ]
                ]
            ]
        ];

        // Account Status Chart (convert data)
        $this->accountChart = [
            'type' => 'doughnut',
            'data' => [
                'labels' => ['Active', 'Pending', 'Suspended', 'Closed'],
                'datasets' => [
                    [
                        'data' => $this->getAccountStatusDistribution(),
                        'backgroundColor' => [
                            '#10B981', // green
                            '#F59E0B', // amber
                            '#EF4444', // red
                            '#6B7280', // gray
                        ]
                    ]
                ]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['position' => 'bottom'],
                    'title' => [
                        'display' => true,
                        'text' => 'Account Status Distribution'
                    ]
                ]
            ]
        ];
    }

    protected function getLoanDistribution()
    {
        return Loan::selectRaw('loan_product_id, COUNT(*) as count')
            ->groupBy('loan_product_id')
            ->pluck('count')
            ->toArray();
    }

    protected function getTransactionColor($type)
    {
        switch ($type) {
            case 'deposit':
                return '#10B981'; // green
            case 'withdrawal':
                return '#EF4444'; // red
            case 'transfer':
                return '#3B82F6'; // blue
            default:
                return '#6B7280'; // gray
        }
    }

    protected function createLoanDataset($status, $currency)
    {
        return [
            'label' => ucfirst($status) . ' Loans',
            'data' => array_map(function ($amount) use ($currency) {
                return convertCurrency($amount, 'UGX', $currency); // Use helper function here
            }, $this->getMonthlyLoans($status)),
            'backgroundColor' => $this->getLoanStatusColor($status),
        ];
    }

    protected function getLoanStatusColor($status)
    {
        switch ($status) {
            case 'new':
                return '#8B5CF6'; // violet
            case 'active':
                return '#059669'; // emerald
            case 'completed':
                return '#2563EB'; // blue
            default:
                return '#6B7280'; // gray
        }
    }

    protected function getMonthlyApplications($type)
    {
        $year = Carbon::now()->year;
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            if ($type === 'loan') {
                $count = Loan::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count();
            } else {
                $count = Account::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->count();
            }
            $data[] = $count;
        }

        return $data;
    }

    protected function getMonthlyTransactions($type)
    {
        $year = Carbon::now()->year;
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $amount = Transaction::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('type', $type)
                ->sum('amount');
            $data[] = $amount;
        }

        return $data;
    }

    protected function getMonthlyLoans($status)
    {
        $year = Carbon::now()->year;
        $data = [];

        for ($month = 1; $month <= 12; $month++) {
            $query = Loan::whereYear('created_at', $year)
                ->whereMonth('created_at', $month);

            if ($status === 'new') {
                $count = $query->where('status', 'pending')->count();
            } elseif ($status === 'active') {
                $count = $query->where('status', 'active')->count();
            } else {
                $count = $query->where('status', 'paid')->count();
            }

            $data[] = $count;
        }

        return $data;
    }

    protected function getAccountStatusDistribution()
    {
        return [
            Account::where('status', 'active')->count(),
            Account::where('status', 'pending')->count(),
            Account::where('status', 'inactive')->count(),
            Account::where('status', 'closed')->count(),
        ];
    }

    protected function calculateMonthlyStats()
    {
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;

        // Calculate total deposits
        $currentDeposits = Transaction::whereMonth('created_at', $currentMonth)
            ->where('type', 'deposit')
            ->sum('amount');

        $currenctCurrency = Auth::user()->currency;

        $lastMonthDeposits = Transaction::whereMonth('created_at', $lastMonth)
            ->where('type', 'deposit')
            ->sum('amount');

        // Calculate total withdrawals
        $currentWithdrawals = Transaction::whereMonth('created_at', $currentMonth)
            ->where('type', 'withdrawal')
            ->sum('amount');
        $lastMonthWithdrawals = Transaction::whereMonth('created_at', $lastMonth)
            ->where('type', 'withdrawal')
            ->sum('amount');

        // calculate total taxes collected
        $totalTaxes = Transaction::where('status','completed')->sum('taxes');

        // calculate total charges collected
        $totalCharges = Transaction::where('status','completed')->sum('charges');

        // Calculate outstanding loans
        $currentOutstandingLoans = Loan::where('status', 'active')->sum('amount');

        $lastMonthOutstandingLoans = Loan::where('status', 'active')
            ->whereMonth('created_at', '<=', $lastMonth)
            ->sum('amount');

        // Calculate loan repayments
        $currentLoanRepayments = Transaction::whereMonth('created_at', $currentMonth)
            ->where('type', 'loanPayment')
            ->sum('amount');

        $lastMonthLoanRepayments = Transaction::whereMonth('created_at', $lastMonth)
            ->where('type', 'loanPayment')
            ->sum('amount');


        // Calculate other income
        $currentOtherIncome = Transaction::whereMonth('created_at', $currentMonth)
            ->where('type', 'other_income')
            ->sum('amount');
        $lastMonthOtherIncome = Transaction::whereMonth('created_at', $lastMonth)
            ->where('type', 'other_income')
            ->sum('amount');

        // Calculate operational expenses
        $currentOperationalExpenses = Transaction::whereMonth('created_at', $currentMonth)
            ->where('type', 'operational_expense')
            ->sum('amount');
        $lastMonthOperationalExpenses = Transaction::whereMonth('created_at', $lastMonth)
            ->where('type', 'operational_expense')
            ->sum('amount');

        // Calculate current wallet balance using the formula
        $currentBalance =$currentDeposits - $currentWithdrawals - $currentOutstandingLoans +
                         $currentLoanRepayments + $currentOtherIncome + $totalTaxes + $totalCharges - $currentOperationalExpenses;

        //  dd(convertCurrency($currentOutstandingLoans, 'UGX', $currenctCurrency));
        // Calculate last month's wallet balance
        $lastMonthBalance = $lastMonthDeposits - $lastMonthWithdrawals - $lastMonthOutstandingLoans +
                           $lastMonthLoanRepayments + $lastMonthOtherIncome - $lastMonthOperationalExpenses;

        // Calculate percentage changes
        $depositsPercentage = $lastMonthDeposits > 0 ?
            (($currentDeposits - $lastMonthDeposits) / $lastMonthDeposits) * 100 : 0;
        $withdrawalsPercentage = $lastMonthWithdrawals > 0 ?
            (($currentWithdrawals - $lastMonthWithdrawals) / $lastMonthWithdrawals) * 100 : 0;
        $balancePercentage = $lastMonthBalance > 0 ?
            (($currentBalance - $lastMonthBalance) / $lastMonthBalance) * 100 : 0;

        // Calculate transfers (keeping existing transfer calculations)
        $currentTransfers = Transaction::whereMonth('created_at', $currentMonth)
            ->where('type', 'transfer')
            ->sum('amount');
        $lastMonthTransfers = Transaction::whereMonth('created_at', $lastMonth)
            ->where('type', 'transfer')
            ->sum('amount');
        $transfersPercentage = $lastMonthTransfers > 0 ?
            (($currentTransfers - $lastMonthTransfers) / $lastMonthTransfers) * 100 : 0;

        // Add active stats trends
        $lastMonthActiveAccounts = Account::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
        $currentActiveAccounts = Account::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $lastMonthActiveLoans = Loan::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();
        $currentActiveLoans = Loan::where('status', 'active')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        $this->monthlyStats = [
            'deposits' => [
                'current' => $currentDeposits,
                'percentage' => round($depositsPercentage, 1),
                'trend' => $depositsPercentage >= 0 ? 'increase' : 'decrease'
            ],
            'withdrawals' => [
                'current' => $currentWithdrawals,
                'percentage' => round($withdrawalsPercentage, 1),
                'trend' => $withdrawalsPercentage >= 0 ? 'increase' : 'decrease'
            ],
            'transfers' => [
                'current' => $currentTransfers,
                'percentage' => round($transfersPercentage, 1),
                'trend' => $transfersPercentage >= 0 ? 'increase' : 'decrease'
            ],
            'wallet_balance' => [
                'current' => $currentBalance,
                'percentage' => round($balancePercentage, 1),
                'trend' => $balancePercentage >= 0 ? 'increase' : 'decrease'
            ],
            'active_accounts' => [
                'current' => $currentActiveAccounts,
                'percentage' => $lastMonthActiveAccounts > 0 ?
                    round((($currentActiveAccounts - $lastMonthActiveAccounts) / $lastMonthActiveAccounts) * 100, 1) : 0,
                'trend' => $currentActiveAccounts >= $lastMonthActiveAccounts ? 'increase' : 'decrease'
            ],
            'active_loans' => [
                'current' => $currentActiveLoans,
                'percentage' => $lastMonthActiveLoans > 0 ?
                    round((($currentActiveLoans - $lastMonthActiveLoans) / $lastMonthActiveLoans) * 100, 1) : 0,
                'trend' => $currentActiveLoans >= $lastMonthActiveLoans ? 'increase' : 'decrease'
            ]
        ];
    }

    public function render()
    {
        // Calculate totals
        $totalAccounts = Account::count();
        $totalLoans = Loan::count();

        $loggedInUsers = Cache::remember('loggedInUsersCount', now()->addMinutes(5), function () {
            return DB::table('sessions')->whereNotNull('user_id')->distinct()->count('user_id');
        });

        return view('livewire.dashboard', [
            'customers' => User::where('role', 'customer')->count(),
            'staff' => Staff::count(),
            'accountTypes' => AccountType::count(),
            'pendingAccounts' => Account::where('status', 'pending')->count(),
            'approvedAccounts' => Account::where('status', 'approved')->count(),
            'activeAccounts' => Account::where('status', 'active')->count(),
            'totalAccounts' => $totalAccounts,
            'loanTypes' => LoanProduct::count(),
            'pendingLoans' => Loan::where('status', 'pending')->count(),
            'approvedLoans' => Loan::where('status', 'approved')->count(),
            'activeLoans' => Loan::where('status', 'active')->count(),
            'totalLoans' => $totalLoans,
            'loggedInUsers' => $loggedInUsers
        ]);
    }
}
