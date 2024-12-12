<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Models\Account;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        protected ?string $search = null,
        protected ?string $type = null,
        protected ?string $status = null,
        protected ?string $dateRange = null,
        protected string $sortField = 'created_at',
        protected string $sortDirection = 'desc',
        protected array $selectedIds = []  // Added selectedIds parameter
    ) {}

    public function query()
    {
        $query = Transaction::query();

        // Filter by selected transaction IDs if provided
        if (!empty($this->selectedIds)) {
            $query->whereIn('id', $this->selectedIds);
        }

        // Apply other filters based on search, type, status, and date range
        $query->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('reference', 'like', '%' . $this->search . '%')
                    ->orWhere('amount', 'like', '%' . $this->search . '%')
                    ->orWhereHas('account', function ($accountQuery) {
                        $accountQuery->where('account_number', 'like', '%' . $this->search . '%');
                    });
            });
        })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->dateRange, function ($query) {
                if (str_contains($this->dateRange, ' to ')) {
                    [$startDate, $endDate] = explode(' to ', $this->dateRange);
                    $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Type',
            'Amount',
            'Source Account Number',
            'Status',
            'Date',
            'Destination Account Number',  // New column for destination account number
        ];
    }

    public function map($transaction): array
    {
        // Get the source account from the relationship
        $sourceAccount = $transaction->account; // Assuming this gets the source account

        // For transfer transactions, check if there's a destination account
        $destinationAccount = null;
        if ($transaction->type === 'transfer') {
            $destinationAccount = Account::find($transaction->destination_account_id); // Find the destination account by its ID
        }

        $data = [
            $transaction->reference_number,
            ucfirst($transaction->type),
            number_format($transaction->amount, 2),
            $sourceAccount ? $sourceAccount->account_number : 'N/A', // Source account number
            ucfirst($transaction->status),
            $transaction->created_at->format('M d, Y H:i'),
        ];

        // If it's a transfer, add the destination account number
        if ($transaction->type === 'transfer' && $destinationAccount) {
            $data[] = $destinationAccount->account_number;
        } else {
            // For non-transfer transactions, add 'N/A' or leave the field empty
            $data[] = 'N/A';
        }

        return $data;
    }
}
