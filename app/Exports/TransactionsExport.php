<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(
        protected ?string $search = null,
        protected ?string $type = null,
        protected ?string $status = null,
        protected ?string $dateRange = null,
        protected string $sortField = 'created_at',
        protected string $sortDirection = 'desc'
    ) {}

    public function query()
    {
        return Transaction::query()
            ->when($this->search, function ($query) {
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
    }

    public function headings(): array
    {
        return [
            'Reference',
            'Type',
            'Amount',
            'Account Number',
            'Status',
            'Date',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->reference_number,
            ucfirst($transaction->type),
            number_format($transaction->amount, 2),
            $transaction->account->account_number,
            ucfirst($transaction->status),
            $transaction->created_at->format('M d, Y H:i'),
        ];
    }
} 