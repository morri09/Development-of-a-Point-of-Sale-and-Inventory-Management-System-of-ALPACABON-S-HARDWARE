<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Adjust stock for a product.
     *
     * Creates a stock adjustment record and updates the product's stock_quantity.
     * If stock reaches zero, the product is marked as out of stock (is_active = false).
     *
     * @param int $productId
     * @param int $quantity Positive for additions, negative for reductions
     * @param string $reason
     * @param int $userId
     * @return StockAdjustment
     * @throws \InvalidArgumentException
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function adjustStock(int $productId, int $quantity, string $reason, int $userId): StockAdjustment
    {
        return DB::transaction(function () use ($productId, $quantity, $reason, $userId) {
            // Find the product or throw exception
            $product = Product::findOrFail($productId);

            // Calculate new stock quantity
            $newStockQuantity = $product->stock_quantity + $quantity;

            // Prevent negative stock
            if ($newStockQuantity < 0) {
                throw new \InvalidArgumentException(
                    "Cannot reduce stock below zero. Current stock: {$product->stock_quantity}, attempted change: {$quantity}"
                );
            }

            // Create stock adjustment record
            $adjustment = StockAdjustment::create([
                'product_id' => $productId,
                'user_id' => $userId,
                'quantity_change' => $quantity,
                'reason' => $reason,
            ]);

            // Update product stock quantity
            $product->stock_quantity = $newStockQuantity;

            // Mark product as inactive if stock reaches zero (Requirement 5.4)
            if ($newStockQuantity === 0) {
                $product->is_active = false;
            }

            $product->save();

            return $adjustment;
        });
    }

    /**
     * Get stock adjustment history for a product.
     *
     * @param int $productId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAdjustmentHistory(int $productId, int $limit = 50)
    {
        return StockAdjustment::where('product_id', $productId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all products with low stock (below threshold).
     *
     * @param int $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(int $threshold = 10)
    {
        return Product::where('stock_quantity', '<=', $threshold)
            ->where('stock_quantity', '>', 0)
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    /**
     * Get all out of stock products.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOutOfStockProducts()
    {
        return Product::where('stock_quantity', 0)
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Check if a product has sufficient stock.
     *
     * @param int $productId
     * @param int $requiredQuantity
     * @return bool
     */
    public function hasSufficientStock(int $productId, int $requiredQuantity): bool
    {
        $product = Product::find($productId);

        if (!$product) {
            return false;
        }

        return $product->stock_quantity >= $requiredQuantity;
    }
}
