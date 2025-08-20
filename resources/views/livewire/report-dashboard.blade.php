<div class="container mx-auto p-6 bg-gray-50 min-h-screen">
    <!-- Page Header -->
    <header class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Reports Dashboard</h1>
        <p class="text-gray-600 text-sm">Generate and export reports for RMG Finance system.</p>
    </header>

    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Report Type -->
            @php
                $reportOptions = [
                    ['id' => 'loans' , 'name' => 'Loan Disbursements'],
                    ['id' => 'transactions' , 'name' => 'Transactions']
                ];
            @endphp
            <div>
                <label for="reportType" class="block text-sm font-medium text-gray-700">Report Type</label>
                <x-wireui-select
                    wire:model="reportType"
                    id="reportType"
                    :options="$reportOptions"
                    option-value="id"
                    option-label="name"
                />
            </div>

            <!-- Start Date -->
            <div>
                <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                <x-wireui-input
                    type="date"
                    wire:model="startDate"
                    id="startDate"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <!-- End Date -->
            <div>
                <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                <x-wireui-input
                    type="date"
                    wire:model="endDate"
                    id="endDate"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                />
            </div>

            <!-- Actions -->
            <div class="flex items-end">
                <button
                    wire:click="generateReport"
                    class="px-4 py-2 bg-indigo-600 text-white font-medium rounded-md shadow hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-indigo-300">
                    Generate Report
                </button>
            </div>
        </div>
    </div>

    <!-- Export Section -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Report Data</h2>
        <div class="lg:space-x-2 flex-wrap sm:space-x-0 lg:space-y-0 sm:space-y-2">
            <button
                wire:click="exportPdf"
                class="px-4 py-2 bg-green-600 text-white text-sm rounded-md shadow hover:bg-green-700 focus:ring focus:ring-green-300">
                Export as PDF
            </button>
            <button
                wire:click="exportExcel"
                class="px-4 py-2 bg-yellow-500 text-white text-sm rounded-md shadow hover:bg-yellow-600 focus:ring focus:ring-yellow-300">
                Export as Excel
            </button>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        @if($reportType == 'loans' && count($loanChartData) > 0)
            <canvas id="loanChart" class="w-full h-64"></canvas>
            <script>
                const loanChartData = @js($loanChartData);
                const ctx = document.getElementById('loanChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: loanChartData.map(item => item.date),
                        datasets: [{
                            label: 'Total Loan Disbursed',
                            data: loanChartData.map(item => item.total_disbursed),
                            borderColor: 'rgba(79, 70, 229, 1)', // Tailwind's Indigo
                            backgroundColor: 'rgba(79, 70, 229, 0.2)',
                            borderWidth: 2,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true },
                        },
                    }
                });
            </script>
        @else
            <p class="text-gray-500 text-center">No chart data available for the selected report type and date range.</p>
        @endif
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow p-4 overflow-x-auto">
        @if($reportData && count($reportData) > 0)
           <!-- Table Section -->
<div class="bg-white rounded-lg shadow p-4 overflow-x-auto">
    @if($reportData && count($reportData) > 0)
        <table class="min-w-full divide-y divide-gray-200">
            <!-- Table Header -->
            <thead class="bg-gray-50">
                <tr>
                    @if($reportType === 'transactions')
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Count</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Charges</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Taxes</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total</th>
                    @elseif($reportType === 'loans')
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Disbursed</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Interest</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Loans</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Avg Interest Rate (%)</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 uppercase">Total Processing Fees</th>
                    @endif
                </tr>
            </thead>

            <!-- Table Body -->
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($reportData as $row)
                    <tr>
                        @if($reportType === 'transactions')
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row->type }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row->count }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total_amount, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total_charges, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total_taxes, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total, 2) }}</td>
                        @elseif($reportType === 'loans')
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row->date }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total_disbursed, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total_interest, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ $row->total_loans }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->avg_interest_rate, 2) }}</td>
                            <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($row->total_processing_fees, 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $reportData->links() }}
        </div>
    @else
        <p class="text-gray-500 text-center">
            No {{ $reportType === 'transactions' ? 'transaction' : 'loan' }} data available for the selected date range.
        </p>
    @endif
</div>

        @else
            <p class="text-gray-500 text-center">No data available for the selected report type and date range.</p>
        @endif
    </div>
</div>
