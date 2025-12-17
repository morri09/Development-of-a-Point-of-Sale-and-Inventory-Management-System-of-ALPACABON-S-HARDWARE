<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Process a transaction from cart data.
     *
     * @param array $cart Cart items array from CartService
     * @param PaymentMethod|string $paymentMethod Payment method used
     * @param int $userId User ID processing the transaction
     * @param float $taxRate Tax rate percentage
     * @param float $discount Discount amount (default 0)
     * @return Transaction The created transaction
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function processTransaction(
        array $cart,
        PaymentMethod|string $paymentMethod,
        int $userId,
        float $taxRate = 12.0,
        float $discount = 0.0,
        ?string $referenceNumber = null
    ): Transaction {
        if (empty($cart)) {
            throw new \InvalidArgumentException('Cannot process transaction with empty cart.');
        }

        // Convert string payment method to enum if needed
        if (is_string($paymentMethod)) {
            $paymentMethod = PaymentMethod::from($paymentMethod);
        }

        return DB::transaction(function () use ($cart, $paymentMethod, $userId, $taxRate, $discount, $referenceNumber) {
            // Calculate totals
            $subtotal = $this->calculateSubtotal($cart);
            $tax = round($subtotal * ($taxRate / 100), 2);
            $total = round($subtotal + $tax - $discount, 2);

            // Generate transaction number
            $transactionNumber = $this->generateTransactionNumber();

            // Create transaction record
            $transaction = Transaction::create([
                'user_id' => $userId,
                'transaction_number' => $transactionNumber,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber,
            ]);

            // Create transaction items and decrement inventory
            foreach ($cart as $productId => $item) {
                $this->createTransactionItem($transaction, $productId, $item);
                $this->decrementInventory($productId, $item['quantity']);
            }

            return $transaction;
        });
    }

    /**
     * Calculate subtotal from cart items.
     *
     * @param array $cart
     * @return float
     */
    protected function calculateSubtotal(array $cart): float
    {
        return round(array_sum(array_column($cart, 'subtotal')), 2);
    }

    /**
     * Generate a unique transaction number.
     *
     * @return string
     */
    public function generateTransactionNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TXN-' . $date . '-';
        
        // Get the last transaction number for today
        $lastTransaction = Transaction::where('transaction_number', 'like', $prefix . '%')
            ->orderBy('transaction_number', 'desc')
            ->first();

        if ($lastTransaction) {
            // Extract the sequence number and increment
            $lastNumber = (int) substr($lastTransaction->transaction_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a transaction item record.
     *
     * @param Transaction $transaction
     * @param int $productId
     * @param array $item
     * @return TransactionItem
     */
    protected function createTransactionItem(Transaction $transaction, int $productId, array $item): TransactionItem
    {
        return TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $productId,
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'subtotal' => $item['subtotal'],
        ]);
    }

    /**
     * Decrement product inventory.
     *
     * @param int $productId
     * @param int $quantity
     * @return void
     * @throws \RuntimeException
     */
    protected function decrementInventory(int $productId, int $quantity): void
    {
        $product = Product::lockForUpdate()->find($productId);

        if (!$product) {
            throw new \RuntimeException("Product with ID {$productId} not found.");
        }

        if ($product->stock_quantity < $quantity) {
            throw new \RuntimeException(
                "Insufficient stock for product '{$product->name}'. " .
                "Available: {$product->stock_quantity}, Requested: {$quantity}"
            );
        }

        $product->stock_quantity -= $quantity;
        $product->save();
    }

    /**
     * Get transaction by ID with items.
     *
     * @param int $transactionId
     * @return Transaction|null
     */
    public function getTransaction(int $transactionId): ?Transaction
    {
        return Transaction::with(['items.product', 'user'])->find($transactionId);
    }

    /**
     * Get transaction by transaction number.
     *
     * @param string $transactionNumber
     * @return Transaction|null
     */
    public function getTransactionByNumber(string $transactionNumber): ?Transaction
    {
        return Transaction::with(['items.product', 'user'])
            ->where('transaction_number', $transactionNumber)
            ->first();
    }
}
