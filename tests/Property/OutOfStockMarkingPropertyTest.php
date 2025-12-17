<?php

namespace Tests\Property;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 12: Out of Stock Marking
 * Validates: Requirements 5.4
 * 
 * For any product where stock_quantity equals zero, the product must be 
 * marked or treated as out of stock in the system.
 */
class OutOfStockMarkingPropertyTest extends TestCase
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
     * Property 12: Out of Stock Marking
     * 
     * For any product where stock_quantity reaches zero through stock adjustment,
     * the product must be marked as inactive (is_active = false).
     */
    #[Test]
    public function product_is_marked_inactive_when_stock_reaches_zero(): void
    {
        $this->forAll(
            Generators::choose(1, 100)  // initial stock quantity
        )
        ->withMaxSize(50)
        ->__invoke(function (int $initialStock): void {
            // Create a user and product with the generated initial stock
            $user = User::factory()->create();
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock_quantity' => $initialStock,
                'is_active' => true,
            ]);

            // Reduce stock to exactly zero
            $this->inventoryService->adjustStock(
                $product->id,
                -$initialStock,  // Reduce by exact amount to reach zero
                'Sold out',
                $user->id
            );

            // Refresh product from database
            $product->refresh();

            // Assert: Product stock is zero
            $this->assertSame(
                0,
                $product->stock_quantity,
                "Product stock should be 0 after full reduction"
            );

            // Assert: Product is marked as inactive (out of stock)
            $this->assertFalse(
                $product->is_active,
                "Product should be marked as inactive (is_active = false) when stock reaches zero"
            );

            // Assert: Database reflects the inactive state
            $this->assertDatabaseHas('products', [
                'id' => $product->id,
                'stock_quantity' => 0,
                'is_active' => false,
            ]);
        });
    }

    /**
     * Property 12 (Extended): Products with zero stock are treated as out of stock
     * 
     * For any product created with zero stock, it should be retrievable
     * via the out of stock products query.
     */
    #[Test]
    public function zero_stock_products_appear_in_out_of_stock_query(): void
    {
        $this->forAll(
            Generators::choose(1, 10)  // number of out-of-stock products to create
        )
        ->withMaxSize(20)
        ->__invoke(function (int $outOfStockCount): void {
            // Clean up products from previous iterations
            Product::query()->delete();
            Category::query()->delete();
            
            $category = Category::factory()->create();
            
            // Create products with zero stock
            $outOfStockProducts = [];
            for ($i = 0; $i < $outOfStockCount; $i++) {
                $outOfStockProducts[] = Product::factory()->create([
                    'category_id' => $category->id,
                    'stock_quantity' => 0,
                    'is_active' => true, // Even if active, zero stock means out of stock
                ]);
            }

            // Create some products with stock (should not appear in out of stock)
            Product::factory()->count(3)->create([
                'category_id' => $category->id,
                'stock_quantity' => 10,
                'is_active' => true,
            ]);

            // Get out of stock products
            $retrievedOutOfStock = $this->inventoryService->getOutOfStockProducts();

            // Assert: All zero-stock products are in the out of stock list
            foreach ($outOfStockProducts as $product) {
                $this->assertTrue(
                    $retrievedOutOfStock->contains('id', $product->id),
                    "Product with zero stock (ID: {$product->id}) should appear in out of stock query"
                );
            }

            // Assert: Count matches expected out of stock products
            $this->assertSame(
                $outOfStockCount,
                $retrievedOutOfStock->count(),
                "Out of stock query should return exactly {$outOfStockCount} products"
            );
        });
    }

    /**
     * Property 12 (Extended): Product remains active when stock is above zero
     * 
     * For any product where stock_quantity is greater than zero after adjustment,
     * the product should remain active.
     */
    #[Test]
    public function product_remains_active_when_stock_above_zero(): void
    {
        $this->forAll(
            Generators::choose(10, 100),  // initial stock
            Generators::choose(1, 9)       // amount to reduce (less than initial)
        )
        ->withMaxSize(50)
        ->__invoke(function (int $initialStock, int $reduceAmount): void {
            // Ensure we don't reduce to zero or below
            if ($reduceAmount >= $initialStock) {
                $reduceAmount = $initialStock - 1;
            }

            $user = User::factory()->create();
            $category = Category::factory()->create();
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'stock_quantity' => $initialStock,
                'is_active' => true,
            ]);

            // Reduce stock but not to zero
            $this->inventoryService->adjustStock(
                $product->id,
                -$reduceAmount,
                'Partial sale',
                $user->id
            );

            // Refresh product from database
            $product->refresh();

            $expectedStock = $initialStock - $reduceAmount;

            // Assert: Product stock is above zero
            $this->assertGreaterThan(
                0,
                $product->stock_quantity,
                "Product stock should be above zero"
            );

            // Assert: Product remains active
            $this->assertTrue(
                $product->is_active,
                "Product should remain active (is_active = true) when stock is above zero"
            );

            // Assert: Stock is correct
            $this->assertSame(
                $expectedStock,
                $product->stock_quantity,
                "Product stock should be {$expectedStock}"
            );
        });
    }
}
