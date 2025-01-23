<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Loan;
use App\Models\Transaction;
use App\Exports\LoanReportExport;

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

    public function generateLoanReport()
    {
        $this->reportData = Loan::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total_disbursed')
            ->groupBy('date')
            ->get();

        // Prepare chart data
        $this->loanChartData = $this->reportData->map(function ($loan) {
            return [
                'date' => $loan->date,
                'total_disbursed' => $loan->total_disbursed,
            ];
        })->toArray();
    }

    public function generateTransactionReport()
    {
        $this->reportData = Transaction::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('type')
            ->get();
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
