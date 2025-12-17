<?php

namespace App\Livewire;

use App\Enums\PaymentMethod;
use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\SettingsService;
use App\Services\TransactionService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app-with-sidebar')]
class PosTerminal extends Component
{
    public string $search = '';
    public string $categoryFilter = '';
    
    // Cache categories to avoid repeated queries
    public ?Collection $cachedCategories = null;
    public array $cart = [];
    public array $totals = [];
    public bool $showCheckoutModal = false;
    public string $paymentMethod = 'cash';
    public string $referenceNumber = '';
    public string $amountPaid = '';
    public ?string $lastTransactionNumber = null;
    public bool $showSuccessModal = false;
    
    // Add to cart modal
    public bool $showAddModal = false;
    public ?int $selectedProductId = null;
    public ?string $selectedProductName = null;
    public ?float $selectedProductPrice = null;
    public ?int $selectedProductStock = null;
    public int $selectedQuantity = 1;

    protected CartService $cartService;
    protected SettingsService $settingsService;
    protected TransactionService $transactionService;

    public function boot(
        CartService $cartService,
        SettingsService $settingsService,
        TransactionService $transactionService
    ): void {
        $this->cartService = $cartService;
        $this->settingsService = $settingsService;
        $this->transactionService = $transactionService;
    }

    public function mount(): void
    {
        $this->loadCart();
    }

    /**
     * Load cart data from session.
     */
    protected function loadCart(): void
    {
        $this->cart = $this->cartService->getCart();
        $this->totals = $this->cartService->getTotals();
    }

    /**
     * Open add to cart modal for a product.
     */
    public function openAddModal(int $productId): void
    {
        $product = Product::find($productId);
        if (!$product) {
            session()->flash('error', 'Product not found.');
            return;
        }
        
        // Calculate available stock (actual stock minus what's already in cart)
        $cartQty = $this->cart[$productId]['quantity'] ?? 0;
        $availableStock = $product->stock_quantity - $cartQty;
        
        if ($availableStock <= 0) {
            session()->flash('error', 'No more stock available for this product.');
            return;
        }
        
        $this->selectedProductId = $productId;
        $this->selectedProductName = $product->name;
        $this->selectedProductPrice = $product->price;
        $this->selectedProductStock = $availableStock;
        $this->selectedQuantity = 1;
        $this->showAddModal = true;
    }
    
    /**
     * Close add to cart modal.
     */
    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->selectedProductId = null;
        $this->selectedProductName = null;
        $this->selectedProductPrice = null;
        $this->selectedProductStock = null;
        $this->selectedQuantity = 1;
    }
    
    /**
     * Confirm add to cart from modal.
     */
    public function confirmAddToCart(): void
    {
        if (!$this->selectedProductId) {
            return;
        }
        
        // Validate quantity is at least 1
        if ($this->selectedQuantity < 1) {
            session()->flash('error', 'Quantity must be at least 1.');
            return;
        }
        
        // Validate quantity doesn't exceed available stock
        if ($this->selectedQuantity > $this->selectedProductStock) {
            session()->flash('error', 'Quantity cannot exceed available stock (' . $this->selectedProductStock . ').');
            return;
        }
        
        try {
            $this->cartService->add($this->selectedProductId, $this->selectedQuantity);
            $this->loadCart();
            $this->closeAddModal();
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }
    
    /**
     * Update selected quantity with validation.
     */
    public function updatedSelectedQuantity($value): void
    {
        $value = (int) $value;
        
        if ($value < 1) {
            $this->selectedQuantity = 1;
        } elseif ($this->selectedProductStock && $value > $this->selectedProductStock) {
            $this->selectedQuantity = $this->selectedProductStock;
        } else {
            $this->selectedQuantity = $value;
        }
    }

    /**
     * Add a product to the cart (direct add with quantity 1).
     */
    public function addToCart(int $productId): void
    {
        try {
            $this->cartService->add($productId, 1);
            $this->loadCart();
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Remove a product from the cart.
     */
    public function removeFromCart(int $productId): void
    {
        $this->cartService->remove($productId);
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    /**
     * Update quantity of a cart item.
     */
    public function updateQuantity(int $productId, int $quantity): void
    {
        try {
            $this->cartService->updateQuantity($productId, $quantity);
            $this->loadCart();
            $this->dispatch('cart-updated');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    /**
     * Increment quantity of a cart item.
     */
    public function incrementQuantity(int $productId): void
    {
        $currentQty = $this->cart[$productId]['quantity'] ?? 0;
        $this->updateQuantity($productId, $currentQty + 1);
    }

    /**
     * Decrement quantity of a cart item.
     */
    public function decrementQuantity(int $productId): void
    {
        $currentQty = $this->cart[$productId]['quantity'] ?? 0;
        if ($currentQty > 1) {
            $this->updateQuantity($productId, $currentQty - 1);
        } else {
            $this->removeFromCart($productId);
        }
    }

    /**
     * Clear the entire cart.
     */
    public function clearCart(): void
    {
        $this->cartService->clear();
        $this->loadCart();
        $this->dispatch('cart-updated');
    }

    /**
     * Open checkout modal.
     */
    public function openCheckout(): void
    {
        if ($this->cartService->isEmpty()) {
            session()->flash('error', 'Cart is empty. Add products before checkout.');
            return;
        }
        $this->showCheckoutModal = true;
    }

    /**
     * Close checkout modal.
     */
    public function closeCheckout(): void
    {
        $this->showCheckoutModal = false;
        $this->paymentMethod = 'cash';
        $this->referenceNumber = '';
        $this->amountPaid = '';
    }

    /**
     * Calculate change amount.
     */
    public function getChangeAmount(): float
    {
        $paid = (float) $this->amountPaid;
        $total = $this->totals['total'] ?? 0;
        return max(0, $paid - $total);
    }

    /**
     * Check if amount paid is sufficient.
     */
    public function isAmountSufficient(): bool
    {
        if ($this->paymentMethod === 'gcash') {
            return true; // GCash doesn't need change calculation
        }
        $paid = (float) $this->amountPaid;
        $total = $this->totals['total'] ?? 0;
        return $paid >= $total;
    }

    /**
     * Set quick amount (for quick cash buttons).
     */
    public function setQuickAmount(float $amount): void
    {
        $this->amountPaid = (string) $amount;
    }

    /**
     * Process checkout and create transaction.
     */
    public function processCheckout(): void
    {
        if ($this->cartService->isEmpty()) {
            session()->flash('error', 'Cart is empty. Add products before checkout.');
            $this->closeCheckout();
            return;
        }

        try {
            // Get cart data and tax rate
            $cart = $this->cartService->getCart();
            $taxRate = (float) $this->settingsService->get('tax_rate', 12);
            $userId = auth()->id();

            // Process the transaction
            $transaction = $this->transactionService->processTransaction(
                $cart,
                $this->paymentMethod,
                $userId,
                $taxRate,
                0.0,
                $this->paymentMethod === 'gcash' ? $this->referenceNumber : null
            );

            // Store transaction number for success message
            $this->lastTransactionNumber = $transaction->transaction_number;

            // Clear the cart after successful transaction
            $this->cartService->clear();
            $this->loadCart();

            // Close checkout modal and show success
            $this->closeCheckout();
            $this->showSuccessModal = true;

            $this->dispatch('transaction-completed', transactionNumber: $transaction->transaction_number);
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\RuntimeException $e) {
            session()->flash('error', 'Transaction failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'An unexpected error occurred. Please try again.');
            report($e);
        }
    }

    /**
     * Close success modal.
     */
    public function closeSuccessModal(): void
    {
        $this->showSuccessModal = false;
        $this->lastTransactionNumber = null;
    }

    /**
     * Get categories (cached).
     */
    #[Computed(persist: true)]
    public function categories(): Collection
    {
        return Category::orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get available stock for a product (actual stock minus quantity in cart).
     */
    public function getAvailableStock(int $productId, int $actualStock): int
    {
        $cartQty = $this->cart[$productId]['quantity'] ?? 0;
        return max(0, $actualStock - $cartQty);
    }

    public function render()
    {
        $products = Product::query()
            ->select(['id', 'name', 'price', 'stock_quantity', 'sku'])
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return view('livewire.pos-terminal', [
            'products' => $products,
        ]);
    }
}
