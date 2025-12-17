<?php

namespace Tests\Property;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\TransactionService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 8: Inventory Decrement on Sale
 * Validates: Requirements 4.4
 * 
 * For any completed transaction, each product's stock_quantity must be 
 * decremented by exactly the quantity sold in that transaction.
 */
class InventoryDecrementPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected TransactionService $transactionService;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = new TransactionService();
        
        // Create a user for transactions
        $this->user = User::factory()->create([
            'role' => UserRole::USER,
            'menu_permissions' => ['pos', 'transactions'],
        ]);
        
        // Create a category for products
        $this->category = Category::factory()->create();
    }

    /**
     * Property 8: Inventory Decrement on Sale
     * 
     * For any completed transaction, each product's stock_quantity must be 
     * decremented by exactly the quantity sold in that transaction.
     */
    #[Test]
    public function inventory_decremented_by_exact_quantity_sold(): void
    {
        $this->forAll(
            // Generate cart items: 3 items with initial stock and quantity to sell
            Generators::tuple(
                Generators::choose(50, 200),    // initial stock 1
                Generators::choose(1, 20),      // quantity to sell 1
                Generators::choose(50, 200),    // initial stock 2
                Generators::choose(1, 20),      // quantity to sell 2
                Generators::choose(50, 200),    // initial stock 3
                Generators::choose(1, 20)       // quantity to sell 3
            )
        )
        ->withMaxSize(100)
        ->__invoke(function (array $data) {
            [$stock1, $qty1, $stock2, $qty2, $stock3, $qty3] = $data;

            // Track initial stocks and expected final stocks
            $productData = [];
            $cart = [];

            $itemsData = [
                [$stock1, $qty1],
                [$stock2, $qty2],
                [$stock3, $qty3],
            ];

            foreach ($itemsData as [$initialStock, $quantityToSell]) {
                $price = 10.00; // Fixed price for simplicity
                
                // Create product with known initial stock
                $product = Product::factory()->create([
                    'category_id' => $this->category->id,
                    'price' => $price,
                    'stock_quantity' => $initialStock,
                    'is_active' => true,
                ]);

                // Store initial stock and expected final stock
                $productData[$product->id] = [
                    'initial_stock' => $initialStock,
                    'quantity_sold' => $quantityToSell,
                    'expected_final_stock' => $initialStock - $quantityToSell,
                ];

                // Build cart item
                $cart[$product->id] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $price,
                    'quantity' => $quantityToSell,
                    'subtotal' => round($price * $quantityToSell, 2),
                ];
            }

            // Process transaction
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id,
                12.0,
                0.0
            );

            // Verify transaction was created
            $this->assertNotNull($transaction);

            // Verify each product's stock was decremented by exactly the quantity sold
            foreach ($productData as $productId => $data) {
                // Reload product from database to get updated stock
                $product = Product::find($productId);
                
                $this->assertEquals(
                    $data['expected_final_stock'],
                    $product->stock_quantity,
                    "Product {$productId} stock should be decremented from {$data['initial_stock']} " .
                    "by {$data['quantity_sold']} to {$data['expected_final_stock']}, " .
                    "but got {$product->stock_quantity}"
                );
            }
        });
    }

    /**
     * Property 8 (Extended): Multiple transactions decrement inventory cumulatively
     * 
     * For any sequence of transactions on the same product, the final stock 
     * should equal initial stock minus the sum of all quantities sold.
     */
    #[Test]
    public function multiple_transactions_decrement_inventory_cumulatively(): void
    {
        $this->forAll(
            Generators::tuple(
                Generators::choose(100, 500),   // initial stock
                Generators::choose(1, 10),      // quantity for transaction 1
                Generators::choose(1, 10),      // quantity for transaction 2
                Generators::choose(1, 10)       // quantity for transaction 3
            )
        )
        ->withMaxSize(100)
        ->__invoke(function (array $data) {
            [$initialStock, $qty1, $qty2, $qty3] = $data;

            $price = 15.00;
            
            // Create product with known initial stock
            $product = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => $price,
                'stock_quantity' => $initialStock,
                'is_active' => true,
            ]);

            $quantities = [$qty1, $qty2, $qty3];
            $totalSold = 0;

            // Process multiple transactions
            foreach ($quantities as $quantity) {
                $cart = [
                    $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => (float) $price,
                        'quantity' => $quantity,
                        'subtotal' => round($price * $quantity, 2),
                    ],
                ];

                $this->transactionService->processTransaction(
                    $cart,
                    PaymentMethod::CASH,
                    $this->user->id,
                    12.0,
                    0.0
                );

                $totalSold += $quantity;
            }

            // Reload product from database
            $product->refresh();

            $expectedFinalStock = $initialStock - $totalSold;

            $this->assertEquals(
                $expectedFinalStock,
                $product->stock_quantity,
                "After selling {$totalSold} units from initial stock of {$initialStock}, " .
                "expected {$expectedFinalStock} but got {$product->stock_quantity}"
            );
        });
    }

    /**
     * Property 8 (Extended): Stock decrement is atomic within transaction
     * 
     * For any transaction with multiple items, either all stock decrements 
     * succeed or none do (transaction atomicity).
     */
    #[Test]
    public function stock_decrement_is_atomic(): void
    {
        $this->forAll(
            Generators::tuple(
                Generators::choose(50, 100),    // stock for product 1 (sufficient)
                Generators::choose(1, 10),      // quantity for product 1
                Generators::choose(5, 10)       // stock for product 2 (will be insufficient)
            )
        )
        ->withMaxSize(50)
        ->__invoke(function (array $data) {
            [$stock1, $qty1, $stock2] = $data;

            // Product 2 will have insufficient stock
            $qty2 = $stock2 + 10; // Request more than available

            $price = 10.00;

            // Create products
            $product1 = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => $price,
                'stock_quantity' => $stock1,
                'is_active' => true,
            ]);

            $product2 = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => $price,
                'stock_quantity' => $stock2,
                'is_active' => true,
            ]);

            $cart = [
                $product1->id => [
                    'product_id' => $product1->id,
                    'name' => $product1->name,
                    'price' => (float) $price,
                    'quantity' => $qty1,
                    'subtotal' => round($price * $qty1, 2),
                ],
                $product2->id => [
                    'product_id' => $product2->id,
                    'name' => $product2->name,
                    'price' => (float) $price,
                    'quantity' => $qty2,
                    'subtotal' => round($price * $qty2, 2),
                ],
            ];

            // Attempt transaction - should fail due to insufficient stock
            $exceptionThrown = false;
            try {
                $this->transactionService->processTransaction(
                    $cart,
                    PaymentMethod::CASH,
                    $this->user->id,
                    12.0,
                    0.0
                );
            } catch (\RuntimeException $e) {
                $exceptionThrown = true;
            }

            $this->assertTrue($exceptionThrown, "Transaction should fail with insufficient stock");

            // Verify product 1's stock was NOT decremented (atomicity)
            $product1->refresh();
            $this->assertEquals(
                $stock1,
                $product1->stock_quantity,
                "Product 1 stock should remain unchanged due to transaction rollback"
            );

            // Verify product 2's stock was NOT decremented
            $product2->refresh();
            $this->assertEquals(
                $stock2,
                $product2->stock_quantity,
                "Product 2 stock should remain unchanged"
            );
        });
    }
}
