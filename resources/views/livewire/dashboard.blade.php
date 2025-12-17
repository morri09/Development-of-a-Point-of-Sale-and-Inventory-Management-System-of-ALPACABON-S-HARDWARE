<div class="py-3">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Today's Sales Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
            <!-- Today's Sales -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-indigo-500 rounded-md p-2">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Today's Sales</p>
                        <p class="text-lg font-semibold text-gray-900">₱{{ number_format($todaySummary['total_sales'], 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Today's Transactions -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-500 rounded-md p-2">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Transactions</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($todaySummary['total_transactions']) }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Products -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-500 rounded-md p-2">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Active Products</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($totalProducts) }}</p>
                    </div>
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-3">
                <div class="flex items-center">
                    <div class="flex-shrink-0 {{ $outOfStockCount > 0 ? 'bg-red-500' : 'bg-gray-400' }} rounded-md p-2">
                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-500">Out of Stock</p>
                        <p class="text-lg font-semibold {{ $outOfStockCount > 0 ? 'text-red-600' : 'text-gray-900' }}">{{ number_format($outOfStockCount) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-4">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Quick Actions</h3>
            <div class="grid grid-cols-4 gap-2">
                <a href="{{ route('pos') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-2.5 hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <div class="flex-shrink-0 bg-indigo-100 rounded p-1.5">
                        <svg class="h-4 w-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-900">New Sale</span>
                </a>

                <a href="{{ route('products.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-2.5 hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <div class="flex-shrink-0 bg-green-100 rounded p-1.5">
                        <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-900">Add Product</span>
                </a>

                <a href="{{ route('inventory.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-2.5 hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <div class="flex-shrink-0 bg-yellow-100 rounded p-1.5">
                        <svg class="h-4 w-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-900">Inventory</span>
                </a>

                <a href="{{ route('reports.index') }}" class="bg-white overflow-hidden shadow-sm rounded-lg p-2.5 hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <div class="flex-shrink-0 bg-purple-100 rounded p-1.5">
                        <svg class="h-4 w-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-medium text-gray-900">Reports</span>
                </a>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="mb-8">
            <x-dashboard-chart :chartData="$chartData" />
        </div>

        <!-- Two Column Layout: Recent Transactions & Low Stock Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Recent Transactions</h3>
                        <a href="{{ route('transactions.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">View all</a>
                    </div>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse ($recentTransactions as $transaction)
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $transaction->transaction_number }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $transaction->user?->name ?? 'Unknown' }} • 
                                        {{ $transaction->created_at->format('M d, g:i A') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">₱{{ number_format($transaction->total, 2) }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->items->count() }} {{ Str::plural('item', $transaction->items->count()) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <p class="mt-2">No transactions yet</p>
                            <a href="{{ route('pos') }}" class="mt-2 inline-block text-sm text-indigo-600 hover:text-indigo-500">Start a new sale</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">Low Stock Alerts</h3>
                        <a href="{{ route('inventory.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">Manage inventory</a>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Products with {{ $lowStockThreshold }} or fewer items in stock</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse ($lowStockProducts as $product)
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                    @if ($product->sku)
                                        <p class="text-xs text-gray-500">SKU: {{ $product->sku }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->stock_quantity <= 0 ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $product->stock_quantity <= 0 ? 'Out of Stock' : $product->stock_quantity . ' left' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-green-600">All products are well stocked!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
