<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Loan;
use App\Models\Transaction;
use App\Exports\LoanReportExport;
use Carbon\Carbon;

class ReportDashboard extends Component
{
    use WithPagination;

    public $reportType = 'loans';
    public $startDate;
    public $endDate;
    public $reportData;
    public $loanChartData = [];

    // Define reports
    public function mount()
    {
        $this->startDate = now()->startOfMonth();
        $this->endDate = now()->endOfMonth();
        $this->generateReport();
    }

    public function generateReport()
    {
        $this->loanChartData = [];

        switch ($this->reportType) {
            case 'loans':
                $this->generateLoanReport();
                break;
            case 'transactions':
                $this->generateTransactionReport();
                break;
                // Add more reports as needed
        }
    }

    // public function generateLoanReport()
    // {
    //     $this->reportData = Loan::whereBetween('created_at', [$this->startDate, $this->endDate])
    //         ->selectRaw('DATE(created_at) as date, SUM(amount) as total_disbursed')
    //         ->groupBy('date')
    //         ->get();

    //     // Prepare chart data
    //     $this->loanChartData = $this->reportData->map(function ($loan) {
    //         return [
    //             'date' => $loan->date,
    //             'total_disbursed' => $loan->total_disbursed,
    //         ];
    //     })->toArray();
    // }

    public function generateLoanReport()
{
    // Ensure startDate and endDate are set, or provide defaults
    $this->startDate = $this->startDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
    $this->endDate = $this->endDate ?: Carbon::now()->endOfMonth()->format('Y-m-d');

    // Validate date inputs
    if (Carbon::parse($this->startDate)->greaterThan(Carbon::parse($this->endDate))) {
        $this->addError('dateRange', 'Start date must be before or equal to the end date.');
        $this->reportData = [];
        $this->loanChartData = [];
        return;
    }

    // Fetch and group loan data
    $this->reportData = Loan::whereBetween('disbursement_date', [$this->startDate, $this->endDate])
        ->where('status', 'approved') // Include only approved loans
        ->selectRaw('DATE(disbursement_date) as date,
                     SUM(amount) as total_disbursed,
                     SUM(total_interest) as total_interest,
                     COUNT(*) as total_loans,
                     AVG(interest_rate) as avg_interest_rate,
                     SUM(processing_fee) as total_processing_fees')
        ->groupBy('date')
        ->orderBy('date', 'asc')
        ->get();

    // Prepare chart data
    $this->loanChartData = $this->reportData->map(function ($loan) {
        return [
            'date' => $loan->date,
            'total_disbursed' => $loan->total_disbursed,
            'total_interest' => $loan->total_interest,
            'total_loans' => $loan->total_loans,
            'avg_interest_rate' => round($loan->avg_interest_rate, 2),
            'total_processing_fees' => $loan->total_processing_fees,
        ];
    })->toArray();

    // Handle case where no data is found
    if ($this->reportData->isEmpty()) {
        $this->loanChartData = [];
    }
}

    // public function generateTransactionReport()
    // {
    //     $this->reportData = Transaction::whereBetween('created_at', [$this->startDate, $this->endDate])
    //         ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
    //         ->groupBy('type')
    //         ->get();
    // }

    public function generateTransactionReport()
    {

        $this->reportData = Transaction::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('
                type,
                COUNT(*) as count,
                SUM(amount) as total_amount,
                SUM(charges) as total_charges,
                SUM(taxes) as total_taxes,
                SUM(total_amount) as total
            ')
            ->groupBy('type')
            ->get();

        // Optionally, process the breakdown data if needed for visualization.
        $this->reportData->each(function ($transaction) {
            $transaction->charges_breakdown = json_decode($transaction->charges_breakdown, true);
            $transaction->taxes_breakdown = json_decode($transaction->taxes_breakdown, true);
        });
    }

    public function exportPdf()
    {
        $pdf = Pdf::loadView('reports.loans', ['data' => $this->reportData]);
        return $pdf->download('loan-report.pdf');
    }

    public function exportExcel()
    {
        // return Excel::download(new LoanReportExport, 'loan-report.xlsx');
    }

    public function render()
    {
        return view('livewire.report-dashboard', [
            'reportData' => $this->reportData,
            'loanChartData' => $this->loanChartData,
        ]);
    }
}
