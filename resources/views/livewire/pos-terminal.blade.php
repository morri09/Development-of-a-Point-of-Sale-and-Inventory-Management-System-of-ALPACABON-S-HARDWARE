<div class="h-[calc(100vh-8rem)] flex flex-col lg:flex-row gap-4">
    <!-- Product Search Section -->
    <div class="lg:w-2/3 flex flex-col min-h-0">
        <div class="card flex-1 flex flex-col min-h-0">
            <!-- Search Header -->
            <div class="card-header">
                <h2 class="text-base font-semibold text-slate-900 mb-2">Products</h2>
                <div class="flex gap-2">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.150ms="search"
                            placeholder="Search products..."
                            class="form-input pl-10"
                            autofocus
                        />
                    </div>
                    <select
                        wire:model.live="categoryFilter"
                        class="form-input w-40"
                    >
                        <option value="">All Categories</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Flash Messages -->
            @if (session()->has('error'))
                <div class="mx-5 mb-4 alert-danger">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if (session()->has('message'))
                <div class="mx-5 mb-4 alert-success">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm font-medium">{{ session('message') }}</p>
                    </div>
                </div>
            @endif

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto p-5 pt-0 scrollbar-thin relative">
                <!-- Loading Overlay -->
                <div wire:loading.flex wire:target="search, categoryFilter, confirmAddToCart, removeFromCart" class="absolute inset-0 bg-white/50 z-10 items-center justify-center">
                    <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @forelse ($products as $product)
                        @php
                            $availableStock = $this->getAvailableStock($product->id, $product->stock_quantity);
                            $inCart = isset($cart[$product->id]);
                            $isDisabled = $availableStock <= 0;
                        @endphp
                        <button wire:click="openAddModal({{ $product->id }})" wire:loading.attr="disabled" {{ $isDisabled ? 'disabled' : '' }} class="group bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 rounded-xl p-3 text-left transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ $isDisabled ? 'opacity-50 cursor-not-allowed' : '' }}">
                            <div class="flex flex-col h-full">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="flex-shrink-0 h-16 w-full object-cover rounded-lg mb-2">
                                @else
                                    <div class="flex-shrink-0 h-16 w-full bg-slate-200 group-hover:bg-indigo-100 rounded-lg flex items-center justify-center mb-2 transition-colors">
                                        <svg class="h-8 w-8 text-slate-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $product->name }}</p>
                                    @if($inCart)
                                        <p class="text-xs text-amber-600">Stock: {{ $availableStock }} ({{ $cart[$product->id]['quantity'] }} in cart)</p>
                                    @elseif($isDisabled)
                                        <p class="text-xs text-red-500">Stock: {{ $availableStock }}</p>
                                    @else
                                        <p class="text-xs text-slate-500">Stock: {{ $availableStock }}</p>
                                    @endif
                                </div>
                                <p class="text-sm font-semibold text-indigo-600 mt-2">₱{{ number_format($product->price, 2) }}</p>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full empty-state">
                            <svg class="empty-state-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                            @if ($search)
                                <p class="empty-state-title">No products found</p>
                                <p class="empty-state-description">No products matching "{{ $search }}"</p>
                            @else
                                <p class="empty-state-title">No products available</p>
                                <p class="empty-state-description">Add products to get started</p>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Cart Section -->
    <div class="lg:w-1/3 flex flex-col min-h-0">
        <div class="card flex-1 flex flex-col min-h-0 overflow-hidden">
            <!-- Cart Header -->
            <div class="px-4 py-3 border-b border-slate-100 flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-900">Cart</h2>
                    @if (count($cart) > 0)
                        <button
                            wire:click="clearCart"
                            class="text-sm text-red-600 hover:text-red-700 font-medium"
                        >
                            Clear All
                        </button>
                    @endif
                </div>
                <p class="text-xs text-slate-500">{{ $totals['item_count'] ?? 0 }} item(s)</p>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-3 scrollbar-thin min-h-0">
                @forelse ($cart as $productId => $item)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <div class="flex-1 min-w-0 mr-2">
                            <p class="text-xs font-medium text-slate-900 truncate">{{ $item['name'] }}</p>
                            <p class="text-xs text-slate-500">₱{{ number_format($item['price'], 2) }} each</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button
                                wire:click="decrementQuantity({{ $productId }})"
                                class="w-6 h-6 flex items-center justify-center rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                </svg>
                            </button>
                            <span class="w-6 text-center text-xs font-medium text-slate-900">{{ $item['quantity'] }}</span>
                            <button
                                wire:click="incrementQuantity({{ $productId }})"
                                class="w-6 h-6 flex items-center justify-center rounded bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </button>
                            <button
                                wire:click="removeFromCart({{ $productId }})"
                                class="w-6 h-6 flex items-center justify-center rounded text-red-500 hover:bg-red-50 transition-colors"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="w-16 text-right">
                            <p class="text-xs font-medium text-slate-900">₱{{ number_format($item['subtotal'], 2) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="empty-state h-full">
                        <svg class="w-16 h-16 text-slate-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="empty-state-title">Cart is empty</p>
                        <p class="empty-state-description">Click on products to add them</p>
                    </div>
                @endforelse
            </div>

            <!-- Cart Totals -->
            <div class="p-3 border-t border-slate-100 bg-slate-50/50 flex-shrink-0">
                <div class="space-y-1 mb-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-600">Subtotal</span>
                        <span class="font-medium text-slate-900">₱{{ number_format($totals['subtotal'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-600">Tax ({{ $totals['tax_rate'] ?? 12 }}%)</span>
                        <span class="font-medium text-slate-900">₱{{ number_format($totals['tax'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-base font-semibold border-t border-slate-200 pt-2 mt-2">
                        <span class="text-slate-900">Total</span>
                        <span class="text-indigo-600">₱{{ number_format($totals['total'] ?? 0, 2) }}</span>
                    </div>
                </div>

                <button
                    wire:click="openCheckout"
                    @if (count($cart) === 0) disabled @endif
                    class="btn-primary w-full py-2.5 text-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Checkout
                </button>
            </div>
        </div>
    </div>

    <!-- Add to Cart Modal -->
    @if ($showAddModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="add-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAddModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="add-modal-title">
                                    Add to Cart
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $selectedProductName }}</p>
                                    <p class="text-sm text-indigo-600 font-semibold">₱{{ number_format($selectedProductPrice ?? 0, 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-1">Available stock: {{ $selectedProductStock }}</p>
                                </div>
                                
                                <!-- Quantity Selector -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            wire:click="$set('selectedQuantity', Math.max(1, {{ $selectedQuantity }} - 1))"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors"
                                            @if($selectedQuantity <= 1) disabled @endif
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>
                                        <input
                                            type="number"
                                            wire:model.live="selectedQuantity"
                                            min="1"
                                            max="{{ $selectedProductStock }}"
                                            class="w-20 text-center px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                            onkeydown="if(event.key==='-'||event.key==='e')event.preventDefault()"
                                        >
                                        <button
                                            type="button"
                                            wire:click="$set('selectedQuantity', Math.min({{ $selectedProductStock }}, {{ $selectedQuantity }} + 1))"
                                            class="w-10 h-10 flex items-center justify-center rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-600 transition-colors"
                                            @if($selectedQuantity >= $selectedProductStock) disabled @endif
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Subtotal -->
                                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600">Subtotal</span>
                                        <span class="text-lg font-semibold text-indigo-600">₱{{ number_format(($selectedProductPrice ?? 0) * $selectedQuantity, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="button"
                            wire:click="confirmAddToCart"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Add to Cart
                        </button>
                        <button
                            type="button"
                            wire:click="closeAddModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Checkout Modal -->
    @if ($showCheckoutModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeCheckout"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Checkout</h3>
                        
                        <!-- Order Summary -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Order Summary</h4>
                            <div class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Items ({{ $totals['item_count'] ?? 0 }})</span>
                                    <span>₱{{ number_format($totals['subtotal'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Tax</span>
                                    <span>₱{{ number_format($totals['tax'] ?? 0, 2) }}</span>
                                </div>
                                <div class="flex justify-between font-semibold text-base border-t border-gray-200 pt-2 mt-2">
                                    <span>Total</span>
                                    <span class="text-indigo-600">₱{{ number_format($totals['total'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" wire:click="$set('paymentMethod', 'cash')" class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all {{ $paymentMethod === 'cash' ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-300 hover:border-gray-400' }}">
                                    <span class="flex flex-1 items-center gap-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">Cash</span>
                                        </span>
                                    </span>
                                </button>
                                <button type="button" wire:click="$set('paymentMethod', 'gcash')" class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none transition-all {{ $paymentMethod === 'gcash' ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-300 hover:border-gray-400' }}">
                                    <span class="flex flex-1 items-center gap-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="flex flex-col">
                                            <span class="block text-sm font-medium text-gray-900">GCash</span>
                                        </span>
                                    </span>
                                </button>
                            </div>
                        </div>

                        <!-- Cash Payment - Amount Paid -->
                        @if($paymentMethod === 'cash')
                            <div class="mb-4">
                                <label for="amountPaid" class="block text-sm font-medium text-gray-700 mb-2">Amount Paid (₱)</label>
                                <input
                                    type="number"
                                    id="amountPaid"
                                    wire:model.live="amountPaid"
                                    placeholder="Enter amount received"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg font-medium"
                                />
                                <!-- Quick Amount Buttons -->
                                <div class="mt-2 grid grid-cols-4 gap-2">
                                    @php
                                        $total = $totals['total'] ?? 0;
                                        $quickAmounts = [
                                            ceil($total / 50) * 50,
                                            ceil($total / 100) * 100,
                                            ceil($total / 500) * 500,
                                            ceil($total / 1000) * 1000,
                                        ];
                                        $quickAmounts = array_unique($quickAmounts);
                                        sort($quickAmounts);
                                        $quickAmounts = array_slice($quickAmounts, 0, 4);
                                    @endphp
                                    @foreach($quickAmounts as $amount)
                                        <button
                                            type="button"
                                            wire:click="setQuickAmount({{ $amount }})"
                                            class="px-2 py-1 text-xs font-medium rounded border border-gray-300 hover:bg-indigo-50 hover:border-indigo-300 transition-colors"
                                        >
                                            ₱{{ number_format($amount, 0) }}
                                        </button>
                                    @endforeach
                                </div>
                                
                                <!-- Change Calculation -->
                                @if($amountPaid)
                                    <div class="mt-3 p-3 rounded-lg {{ (float)$amountPaid >= ($totals['total'] ?? 0) ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                                        @if((float)$amountPaid >= ($totals['total'] ?? 0))
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-green-700">Change:</span>
                                                <span class="text-xl font-bold text-green-600">₱{{ number_format((float)$amountPaid - ($totals['total'] ?? 0), 2) }}</span>
                                            </div>
                                        @else
                                            <div class="flex justify-between items-center">
                                                <span class="text-sm font-medium text-red-700">Insufficient:</span>
                                                <span class="text-xl font-bold text-red-600">₱{{ number_format(($totals['total'] ?? 0) - (float)$amountPaid, 2) }} short</span>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- GCash Reference Number -->
                        @if($paymentMethod === 'gcash')
                            <div class="mb-4">
                                <label for="referenceNumber" class="block text-sm font-medium text-gray-700 mb-2">GCash Reference Number</label>
                                <input
                                    type="text"
                                    id="referenceNumber"
                                    wire:model="referenceNumber"
                                    placeholder="Enter GCash reference number"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                />
                                <p class="mt-1 text-xs text-gray-500">Enter the reference number from the GCash payment confirmation</p>
                            </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        @php
                            $canComplete = $paymentMethod === 'gcash' || ((float)$amountPaid >= ($totals['total'] ?? 0));
                        @endphp
                        <button
                            type="button"
                            wire:click="processCheckout"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm {{ $canComplete ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed' }}"
                            wire:loading.attr="disabled"
                            {{ !$canComplete ? 'disabled' : '' }}
                        >
                            <span wire:loading.remove>Complete Sale</span>
                            <span wire:loading>Processing...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="closeCheckout"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Modal -->
    @if ($showSuccessModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="success-modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSuccessModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="success-modal-title">
                                    Transaction Complete!
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Your transaction has been processed successfully.
                                    </p>
                                    <p class="mt-2 text-sm font-medium text-gray-900">
                                        Transaction #: <span class="text-indigo-600">{{ $lastTransactionNumber }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button
                            type="button"
                            wire:click="closeSuccessModal"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            New Transaction
                        </button>
                        <a
                            href="{{ route('receipt.showByNumber', ['transactionNumber' => $lastTransactionNumber]) }}"
                            target="_blank"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Receipt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
