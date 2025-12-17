<?php

namespace Tests\Property;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Services\InventoryService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 11: Stock Adjustment Logging
 * Validates: Requirements 5.3
 * 
 * For any stock quantity change, a corresponding stock_adjustment record must be 
 * created with the correct quantity_change, user_id, and timestamp.
 */
class StockAdjustmentLoggingPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    private InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService();
    }

    /**
     * Property 11: Stock Adjustment Logging
     * 
     * For any stock quantity change, a corresponding stock_adjustment record must be
     * created with the correct quantity_change, user_id, and timestamp.
     */
    #[Test]
    public function stock_adjustment_creates_log_record(): void
    {
        $this->forAll(
            Generators::choose(1, 100),   // quantity change (positive - stock addition)
            Generators::string()           // reason
        )
        ->withMaxSize(50)
        ->__invoke(function (int $quantityChange, string $reason): void {
            // Skip empty reasons
            if (empty(trim($reason))) {
                $reason = 'Stock adjustment';
            }

            // Create a user and product for this test iteration
            $user = User::factory()->create();
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock_quantity' => 50, // Start with enough stock
            ]);

            $beforeTimestamp = now();

            // Perform stock adjustment
            $adjustment = $this->inventoryService->adjustStock(
                $product->id,
                $quantityChange,
                $reason,
                $user->id
            );

            $afterTimestamp = now();

            // Assert: A stock adjustment record was created
            $this->assertInstanceOf(StockAdjustment::class, $adjustment);

            // Assert: The adjustment has the correct quantity_change
            $this->assertSame(
                $quantityChange,
                $adjustment->quantity_change,
                "Stock adjustment quantity_change should be {$quantityChange}, got {$adjustment->quantity_change}"
            );

            // Assert: The adjustment has the correct user_id
            $this->assertSame(
                $user->id,
                $adjustment->user_id,
                "Stock adjustment user_id should be {$user->id}, got {$adjustment->user_id}"
            );

            // Assert: The adjustment has the correct product_id
            $this->assertSame(
                $product->id,
                $adjustment->product_id,
                "Stock adjustment product_id should be {$product->id}, got {$adjustment->product_id}"
            );

            // Assert: The adjustment has the correct reason
            $this->assertSame(
                $reason,
                $adjustment->reason,
                "Stock adjustment reason should be '{$reason}', got '{$adjustment->reason}'"
            );

            // Assert: The adjustment has a valid timestamp (within 5 seconds of now)
            $this->assertNotNull($adjustment->created_at);
            $this->assertTrue(
                $adjustment->created_at->diffInSeconds(now()) <= 5,
                "Stock adjustment timestamp should be within 5 seconds of current time"
            );

            // Assert: The record exists in the database
            $this->assertDatabaseHas('stock_adjustments', [
                'id' => $adjustment->id,
                'product_id' => $product->id,
                'user_id' => $user->id,
                'quantity_change' => $quantityChange,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Property 11 (Extended): Stock reduction also creates log record
     * 
     * For any valid stock reduction, a corresponding stock_adjustment record must be
     * created with the correct negative quantity_change.
     */
    #[Test]
    public function stock_reduction_creates_log_record(): void
    {
        $this->forAll(
            Generators::choose(1, 30),    // quantity to reduce (will be negated)
            Generators::string()           // reason
        )
        ->withMaxSize(50)
        ->__invoke(function (int $quantityToReduce, string $reason): void {
            // Skip empty reasons
            if (empty(trim($reason))) {
                $reason = 'Stock reduction';
            }

            // Create a user and product with sufficient stock
            $user = User::factory()->create();
            $category = Category::factory()->create();
            $initialStock = 50;
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock_quantity' => $initialStock,
            ]);

            $negativeQuantity = -$quantityToReduce;

            // Perform stock reduction
            $adjustment = $this->inventoryService->adjustStock(
                $product->id,
                $negativeQuantity,
                $reason,
                $user->id
            );

            // Assert: A stock adjustment record was created with negative quantity
            $this->assertInstanceOf(StockAdjustment::class, $adjustment);
            $this->assertSame(
                $negativeQuantity,
                $adjustment->quantity_change,
                "Stock reduction quantity_change should be {$negativeQuantity}, got {$adjustment->quantity_change}"
            );

            // Assert: The adjustment has the correct user_id
            $this->assertSame($user->id, $adjustment->user_id);

            // Assert: The record exists in the database
            $this->assertDatabaseHas('stock_adjustments', [
                'id' => $adjustment->id,
                'product_id' => $product->id,
                'user_id' => $user->id,
                'quantity_change' => $negativeQuantity,
            ]);
        });
    }

    /**
     * Property 11 (Extended): Product stock is updated correctly
     * 
     * For any stock adjustment, the product's stock_quantity must be updated
     * by exactly the quantity_change amount.
     */
    #[Test]
    public function stock_adjustment_updates_product_stock_correctly(): void
    {
        $this->forAll(
            Generators::choose(10, 100),  // initial stock
            Generators::choose(-9, 50)    // quantity change (can be negative but not more than initial)
        )
        ->withMaxSize(50)
        ->__invoke(function (int $initialStock, int $quantityChange): void {
            // Skip if the change would result in negative stock
            if ($initialStock + $quantityChange < 0) {
                return;
            }

            $user = User::factory()->create();
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock_quantity' => $initialStock,
            ]);

            // Perform stock adjustment
            $this->inventoryService->adjustStock(
                $product->id,
                $quantityChange,
                'Test adjustment',
                $user->id
            );

            // Refresh product from database
            $product->refresh();

            $expectedStock = $initialStock + $quantityChange;

            // Assert: Product stock is updated correctly
            $this->assertSame(
                $expectedStock,
                $product->stock_quantity,
                "Product stock should be {$expectedStock} after adjustment, got {$product->stock_quantity}"
            );
        });
    }
}
