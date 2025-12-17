<?php

namespace Tests\Property;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\TransactionService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 7: Transaction Record Completeness
 * Validates: Requirements 4.3
 * 
 * For any completed transaction, the database must contain a transaction record 
 * with all line items matching the cart contents at checkout time.
 */
class TransactionRecordPropertyTest extends TestCase
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
     * Property 7: Transaction Record Completeness
     * 
     * For any completed transaction, the database must contain a transaction record 
     * with all line items matching the cart contents at checkout time.
     */
    #[Test]
    public function transaction_record_contains_all_cart_items(): void
    {
        $this->forAll(
            // Generate cart items: 3 items with price (cents) and quantity
            Generators::tuple(
                Generators::choose(100, 10000),  // price1 in cents
                Generators::choose(1, 10),       // quantity1
                Generators::choose(100, 10000),  // price2 in cents
                Generators::choose(1, 10),       // quantity2
                Generators::choose(100, 10000),  // price3 in cents
                Generators::choose(1, 10)        // quantity3
            )
        )
        ->withMaxSize(100)
        ->__invoke(function (array $data) {
            [$price1Cents, $qty1, $price2Cents, $qty2, $price3Cents, $qty3] = $data;

            // Build cart array matching CartService format
            $cart = [];
            $itemsData = [
                [$price1Cents, $qty1],
                [$price2Cents, $qty2],
                [$price3Cents, $qty3],
            ];
            
            foreach ($itemsData as [$priceCents, $quantity]) {
                $price = $priceCents / 100;
                
                // Create product with sufficient stock
                $product = Product::factory()->create([
                    'category_id' => $this->category->id,
                    'price' => $price,
                    'stock_quantity' => 1000, // Ensure enough stock
                    'is_active' => true,
                ]);
                
                // Build cart item in CartService format
                $cart[$product->id] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $price,
                    'quantity' => $quantity,
                    'subtotal' => round($price * $quantity, 2),
                ];
            }

            // Process transaction
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id,
                12.0, // tax rate
                0.0   // discount
            );

            // Verify transaction was created
            $this->assertNotNull($transaction);
            $this->assertNotNull($transaction->id);
            
            // Reload transaction with items from database
            $savedTransaction = Transaction::with('items')->find($transaction->id);
            $this->assertNotNull($savedTransaction);

            // Verify number of line items matches cart
            $this->assertCount(
                count($cart),
                $savedTransaction->items,
                "Transaction should have exactly " . count($cart) . " line items"
            );

            // Verify each cart item has a corresponding transaction item
            foreach ($cart as $productId => $cartItem) {
                $transactionItem = $savedTransaction->items
                    ->where('product_id', $productId)
                    ->first();

                $this->assertNotNull(
                    $transactionItem,
                    "Transaction item for product {$productId} should exist"
                );

                // Verify quantity matches
                $this->assertEquals(
                    $cartItem['quantity'],
                    $transactionItem->quantity,
                    "Quantity mismatch for product {$productId}"
                );

                // Verify unit price matches
                $this->assertEquals(
                    $cartItem['price'],
                    (float) $transactionItem->unit_price,
                    "Unit price mismatch for product {$productId}"
                );

                // Verify subtotal matches
                $this->assertEquals(
                    $cartItem['subtotal'],
                    (float) $transactionItem->subtotal,
                    "Subtotal mismatch for product {$productId}"
                );
            }
        });
    }

    /**
     * Property 7 (Extended): Transaction totals match cart calculations
     * 
     * For any completed transaction, the transaction subtotal must equal 
     * the sum of all line item subtotals.
     */
    #[Test]
    public function transaction_subtotal_equals_sum_of_item_subtotals(): void
    {
        $this->forAll(
            Generators::tuple(
                Generators::choose(100, 5000),   // price1 in cents
                Generators::choose(1, 5),        // quantity1
                Generators::choose(100, 5000),   // price2 in cents
                Generators::choose(1, 5),        // quantity2
                Generators::choose(100, 5000),   // price3 in cents
                Generators::choose(1, 5)         // quantity3
            )
        )
        ->withMaxSize(100)
        ->__invoke(function (array $data) {
            [$price1Cents, $qty1, $price2Cents, $qty2, $price3Cents, $qty3] = $data;

            $cart = [];
            $expectedSubtotal = 0.0;

            $itemsData = [
                [$price1Cents, $qty1],
                [$price2Cents, $qty2],
                [$price3Cents, $qty3],
            ];

            foreach ($itemsData as [$priceCents, $quantity]) {
                $price = $priceCents / 100;
                
                $product = Product::factory()->create([
                    'category_id' => $this->category->id,
                    'price' => $price,
                    'stock_quantity' => 1000,
                    'is_active' => true,
                ]);

                $itemSubtotal = round($price * $quantity, 2);
                $expectedSubtotal += $itemSubtotal;

                $cart[$product->id] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $price,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $expectedSubtotal = round($expectedSubtotal, 2);

            // Process transaction
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id,
                12.0,
                0.0
            );

            // Verify transaction subtotal matches expected
            $this->assertEquals(
                $expectedSubtotal,
                (float) $transaction->subtotal,
                "Transaction subtotal should equal sum of item subtotals"
            );

            // Verify sum of transaction items equals transaction subtotal
            $savedTransaction = Transaction::with('items')->find($transaction->id);
            $itemsSubtotalSum = $savedTransaction->items->sum(fn($item) => (float) $item->subtotal);

            $this->assertEquals(
                (float) $savedTransaction->subtotal,
                round($itemsSubtotalSum, 2),
                "Sum of transaction item subtotals should equal transaction subtotal"
            );
        });
    }

    /**
     * Property 7 (Extended): Transaction has valid transaction number
     * 
     * For any completed transaction, a unique transaction number must be generated.
     */
    #[Test]
    public function transaction_has_unique_transaction_number(): void
    {
        $this->forAll(
            Generators::choose(2, 5) // Number of transactions to create
        )
        ->withMaxSize(50)
        ->__invoke(function (int $numTransactions) {
            $transactionNumbers = [];

            for ($i = 0; $i < $numTransactions; $i++) {
                $product = Product::factory()->create([
                    'category_id' => $this->category->id,
                    'price' => 10.00,
                    'stock_quantity' => 1000,
                    'is_active' => true,
                ]);

                $cart = [
                    $product->id => [
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => 10.00,
                        'quantity' => 1,
                        'subtotal' => 10.00,
                    ],
                ];

                $transaction = $this->transactionService->processTransaction(
                    $cart,
                    PaymentMethod::CASH,
                    $this->user->id
                );

                // Verify transaction number is not empty
                $this->assertNotEmpty(
                    $transaction->transaction_number,
                    "Transaction number should not be empty"
                );

                // Verify transaction number is unique
                $this->assertNotContains(
                    $transaction->transaction_number,
                    $transactionNumbers,
                    "Transaction number should be unique"
                );

                $transactionNumbers[] = $transaction->transaction_number;
            }
        });
    }
}
