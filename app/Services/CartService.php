<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Session key for storing cart data.
     */
    protected const CART_SESSION_KEY = 'pos_cart';

    /**
     * Get the current cart from session.
     *
     * @return array<int, array{product_id: int, name: string, price: float, quantity: int, subtotal: float}>
     */
    public function getCart(): array
    {
        return Session::get(self::CART_SESSION_KEY, []);
    }

    /**
     * Add a product to the cart.
     *
     * @param int $productId
     * @param int $quantity
     * @return array The updated cart
     * @throws \InvalidArgumentException
     */
    public function add(int $productId, int $quantity = 1): array
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        $product = Product::findOrFail($productId);

        if (!$product->is_active) {
            throw new \InvalidArgumentException('Product is not available for sale.');
        }

        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            // Update existing item quantity
            $newQuantity = $cart[$productId]['quantity'] + $quantity;
            
            // Check stock availability
            if ($newQuantity > $product->stock_quantity) {
                throw new \InvalidArgumentException(
                    "Insufficient stock. Available: {$product->stock_quantity}"
                );
            }

            $cart[$productId]['quantity'] = $newQuantity;
            $cart[$productId]['subtotal'] = round($cart[$productId]['price'] * $newQuantity, 2);
        } else {
            // Check stock availability for new item
            if ($quantity > $product->stock_quantity) {
                throw new \InvalidArgumentException(
                    "Insufficient stock. Available: {$product->stock_quantity}"
                );
            }

            // Add new item to cart
            $cart[$productId] = [
                'product_id' => $productId,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => $quantity,
                'subtotal' => round((float) $product->price * $quantity, 2),
            ];
        }

        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Remove a product from the cart.
     *
     * @param int $productId
     * @return array The updated cart
     */
    public function remove(int $productId): array
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->saveCart($cart);
        }

        return $cart;
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param int $productId
     * @param int $quantity
     * @return array The updated cart
     * @throws \InvalidArgumentException
     */
    public function updateQuantity(int $productId, int $quantity): array
    {
        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            throw new \InvalidArgumentException('Product not found in cart.');
        }

        $product = Product::findOrFail($productId);

        // Check stock availability
        if ($quantity > $product->stock_quantity) {
            throw new \InvalidArgumentException(
                "Insufficient stock. Available: {$product->stock_quantity}"
            );
        }

        $cart[$productId]['quantity'] = $quantity;
        $cart[$productId]['subtotal'] = round($cart[$productId]['price'] * $quantity, 2);

        $this->saveCart($cart);

        return $cart;
    }

    /**
     * Clear the entire cart.
     *
     * @return void
     */
    public function clear(): void
    {
        Session::forget(self::CART_SESSION_KEY);
    }

    /**
     * Calculate cart subtotal (sum of all item subtotals).
     *
     * @return float
     */
    public function getSubtotal(): float
    {
        $cart = $this->getCart();
        
        return round(array_sum(array_column($cart, 'subtotal')), 2);
    }

    /**
     * Calculate tax amount based on subtotal and tax rate.
     *
     * @param float|null $taxRate Tax rate percentage (e.g., 12 for 12%). If null, fetches from settings.
     * @return float
     */
    public function getTax(?float $taxRate = null): float
    {
        if ($taxRate === null) {
            $settingsService = app(SettingsService::class);
            $taxRate = (float) $settingsService->get('tax_rate', 12);
        }

        $subtotal = $this->getSubtotal();
        
        return round($subtotal * ($taxRate / 100), 2);
    }

    /**
     * Calculate cart total (subtotal + tax).
     *
     * @param float|null $taxRate Tax rate percentage. If null, fetches from settings.
     * @return float
     */
    public function getTotal(?float $taxRate = null): float
    {
        return round($this->getSubtotal() + $this->getTax($taxRate), 2);
    }

    /**
     * Get cart totals breakdown.
     *
     * @param float|null $taxRate Tax rate percentage. If null, fetches from settings.
     * @return array{subtotal: float, tax: float, tax_rate: float, total: float, item_count: int}
     */
    public function getTotals(?float $taxRate = null): array
    {
        if ($taxRate === null) {
            $settingsService = app(SettingsService::class);
            $taxRate = (float) $settingsService->get('tax_rate', 12);
        }

        $subtotal = $this->getSubtotal();
        $tax = round($subtotal * ($taxRate / 100), 2);
        $total = round($subtotal + $tax, 2);

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'tax_rate' => $taxRate,
            'total' => $total,
            'item_count' => $this->getItemCount(),
        ];
    }

    /**
     * Get total number of items in cart.
     *
     * @return int
     */
    public function getItemCount(): int
    {
        $cart = $this->getCart();
        
        return array_sum(array_column($cart, 'quantity'));
    }

    /**
     * Check if cart is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }

    /**
     * Save cart to session.
     *
     * @param array $cart
     * @return void
     */
    protected function saveCart(array $cart): void
    {
        Session::put(self::CART_SESSION_KEY, $cart);
    }
}
