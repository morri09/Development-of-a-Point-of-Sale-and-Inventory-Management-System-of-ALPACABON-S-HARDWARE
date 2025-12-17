<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Report Type Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button
                    wire:click="setReportType('daily')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $reportType === 'daily' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Daily
                </button>
                <button
                    wire:click="setReportType('weekly')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $reportType === 'weekly' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Weekly
                </button>
                <button
                    wire:click="setReportType('monthly')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $reportType === 'monthly' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Monthly
                </button>
                <button
                    wire:click="setReportType('custom')"
                    class="py-2 px-1 border-b-2 font-medium text-sm {{ $reportType === 'custom' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
                >
                    Custom Range
                </button>
            </nav>
        </div>

        <!-- Date Range Picker (visible for custom) -->
        @if ($reportType === 'custom')
            <div class="mb-6 flex flex-col sm:flex-row gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input
                        type="date"
                        wire:model.live="dateFrom"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input
                        type="date"
                        wire:model.live="dateTo"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    />
                </div>
                <button
                    wire:click="clearFilters"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                >
                    Reset
                </button>
            </div>
        @endif

        <!-- Date Range Display & Export -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-sm text-gray-600">
                Showing data from <span class="font-medium">{{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }}</span>
                to <span class="font-medium">{{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</span>
            </div>
            <button
                wire:click="exportCsv"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <!-- Total Sales -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Sales</p>
                        <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($summary['total_sales'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Transactions -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Transactions</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ number_format($summary['total_transactions']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Average Transaction -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Avg. Transaction</p>
                        <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($summary['average_transaction'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Tax -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Tax</p>
                        <p class="text-2xl font-semibold text-gray-900">₱{{ number_format($summary['total_tax'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two Column Layout: Daily Sales Chart & Top Products -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Daily Sales Chart -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Sales by Day</h3>
                @if ($dailySales->count() > 0)
                    <div class="space-y-3">
                        @php
                            $maxSales = $dailySales->max('total_sales') ?: 1;
                        @endphp
                        @foreach ($dailySales as $day)
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-600">{{ \Carbon\Carbon::parse($day['date'])->format('M d') }}</span>
                                    <span class="font-medium">₱{{ number_format($day['total_sales'], 2) }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-indigo-600 h-2 rounded-full" 
                                        style="width: {{ ($day['total_sales'] / $maxSales) * 100 }}%"
                                    ></div>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $day['transaction_count'] }} {{ Str::plural('transaction', $day['transaction_count']) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="mt-2">No sales data for this period</p>
                    </div>
                @endif
            </div>

            <!-- Top Selling Products -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Top Selling Products</h3>
                @if ($topProducts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Qty Sold</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($topProducts as $index => $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-sm text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-3 py-2">
                                            <div class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</div>
                                            @if ($product['product_sku'])
                                                <div class="text-xs text-gray-500">{{ $product['product_sku'] }}</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right">{{ number_format($product['total_quantity']) }}</td>
                                        <td class="px-3 py-2 text-sm font-medium text-gray-900 text-right">₱{{ number_format($product['total_revenue'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        <p class="mt-2">No product sales for this period</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
