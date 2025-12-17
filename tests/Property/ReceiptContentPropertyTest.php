<?php

namespace Tests\Property;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\ReceiptService;
use App\Services\SettingsService;
use App\Services\TransactionService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 9: Receipt Contains Required Information
 * Validates: Requirements 4.5
 * 
 * For any generated receipt, it must contain the current store name, address, 
 * contact, transaction number, all line items, and total amount.
 */
class ReceiptContentPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected ReceiptService $receiptService;
    protected TransactionService $transactionService;
    protected SettingsService $settingsService;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->settingsService = new SettingsService();
        $this->receiptService = new ReceiptService($this->settingsService);
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
     * Property 9: Receipt Contains Required Information
     * 
     * For any generated receipt, it must contain the current store name, address, 
     * contact, transaction number, all line items, and total amount.
     */
    #[Test]
    public function receipt_contains_store_information(): void
    {
        $this->forAll(
            Generators::string(),  // store name
            Generators::string(),  // store address
            Generators::string()   // store contact
        )
        ->withMaxSize(100)
        ->__invoke(function (string $storeName, string $storeAddress, string $storeContact) {
            // Skip empty strings - store name is required
            if (trim($storeName) === '') {
                $storeName = 'Test Store';
            }
            
            // Set up store settings
            Setting::set('store_name', $storeName);
            Setting::set('store_address', $storeAddress);
            Setting::set('store_contact', $storeContact);
            
            // Clear settings cache
            $this->settingsService->clearAllCache();
            
            // Create a simple transaction
            $product = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => 100.00,
                'stock_quantity' => 1000,
                'is_active' => true,
            ]);
            
            $cart = [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => 100.00,
                    'quantity' => 1,
                    'subtotal' => 100.00,
                ],
            ];
            
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id,
                12.0,
                0.0
            );
            
            // Generate receipt data
            $receiptData = $this->receiptService->generateReceiptData($transaction);
            
            // Verify store information is present
            $this->assertArrayHasKey('store', $receiptData);
            $this->assertEquals($storeName, $receiptData['store']['name']);
            $this->assertEquals($storeAddress, $receiptData['store']['address']);
            $this->assertEquals($storeContact, $receiptData['store']['contact']);
        });
    }

    /**
     * Property 9 (Extended): Receipt contains transaction number
     * 
     * For any generated receipt, it must contain the transaction number.
     */
    #[Test]
    public function receipt_contains_transaction_number(): void
    {
        $this->forAll(
            Generators::choose(100, 10000),  // price in cents
            Generators::choose(1, 10)        // quantity
        )
        ->withMaxSize(100)
        ->__invoke(function (int $priceCents, int $quantity) {
            $price = $priceCents / 100;
            
            // Create product and transaction
            $product = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => $price,
                'stock_quantity' => 1000,
                'is_active' => true,
            ]);
            
            $cart = [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $price,
                    'quantity' => $quantity,
                    'subtotal' => round($price * $quantity, 2),
                ],
            ];
            
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id
            );
            
            // Generate receipt data
            $receiptData = $this->receiptService->generateReceiptData($transaction);
            
            // Verify transaction number is present and matches
            $this->assertArrayHasKey('transaction', $receiptData);
            $this->assertArrayHasKey('number', $receiptData['transaction']);
            $this->assertEquals(
                $transaction->transaction_number,
                $receiptData['transaction']['number'],
                "Receipt transaction number should match the transaction"
            );
            $this->assertNotEmpty(
                $receiptData['transaction']['number'],
                "Receipt transaction number should not be empty"
            );
        });
    }


    /**
     * Property 9 (Extended): Receipt contains all line items
     * 
     * For any generated receipt, it must contain all line items from the transaction.
     */
    #[Test]
    public function receipt_contains_all_line_items(): void
    {
        $this->forAll(
            Generators::tuple(
                Generators::choose(100, 5000),  // price1 in cents
                Generators::choose(1, 5)        // quantity1
            ),
            Generators::tuple(
                Generators::choose(100, 5000),  // price2 in cents
                Generators::choose(1, 5)        // quantity2
            ),
            Generators::tuple(
                Generators::choose(100, 5000),  // price3 in cents
                Generators::choose(1, 5)        // quantity3
            )
        )
        ->withMaxSize(100)
        ->__invoke(function (array $item1, array $item2, array $item3) {
            $cart = [];
            $itemsData = [$item1, $item2, $item3];
            $productNames = [];
            
            foreach ($itemsData as $index => [$priceCents, $quantity]) {
                $price = $priceCents / 100;
                
                $product = Product::factory()->create([
                    'category_id' => $this->category->id,
                    'price' => $price,
                    'stock_quantity' => 1000,
                    'is_active' => true,
                ]);
                
                $productNames[$product->id] = $product->name;
                
                $cart[$product->id] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $price,
                    'quantity' => $quantity,
                    'subtotal' => round($price * $quantity, 2),
                ];
            }
            
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id,
                12.0,
                0.0
            );
            
            // Generate receipt data
            $receiptData = $this->receiptService->generateReceiptData($transaction);
            
            // Verify items array exists and has correct count
            $this->assertArrayHasKey('items', $receiptData);
            $this->assertCount(
                count($cart),
                $receiptData['items'],
                "Receipt should contain exactly " . count($cart) . " line items"
            );
            
            // Verify each item has required fields
            foreach ($receiptData['items'] as $item) {
                $this->assertArrayHasKey('name', $item);
                $this->assertArrayHasKey('quantity', $item);
                $this->assertArrayHasKey('unit_price', $item);
                $this->assertArrayHasKey('subtotal', $item);
                
                // Verify item name is one of the products we created
                $this->assertContains(
                    $item['name'],
                    $productNames,
                    "Receipt item name should match a product from the cart"
                );
            }
        });
    }

    /**
     * Property 9 (Extended): Receipt contains total amount
     * 
     * For any generated receipt, it must contain the total amount matching the transaction.
     */
    #[Test]
    public function receipt_contains_total_amount(): void
    {
        $this->forAll(
            Generators::tuple(
                Generators::choose(100, 10000),  // price in cents
                Generators::choose(1, 10)        // quantity
            ),
            Generators::choose(0, 20)  // tax rate
        )
        ->withMaxSize(100)
        ->__invoke(function (array $itemData, int $taxRate) {
            [$priceCents, $quantity] = $itemData;
            $price = $priceCents / 100;
            
            $product = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => $price,
                'stock_quantity' => 1000,
                'is_active' => true,
            ]);
            
            $cart = [
                $product->id => [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $price,
                    'quantity' => $quantity,
                    'subtotal' => round($price * $quantity, 2),
                ],
            ];
            
            $transaction = $this->transactionService->processTransaction(
                $cart,
                PaymentMethod::CASH,
                $this->user->id,
                (float) $taxRate,
                0.0
            );
            
            // Generate receipt data
            $receiptData = $this->receiptService->generateReceiptData($transaction);
            
            // Verify totals section exists
            $this->assertArrayHasKey('totals', $receiptData);
            $this->assertArrayHasKey('subtotal', $receiptData['totals']);
            $this->assertArrayHasKey('tax', $receiptData['totals']);
            $this->assertArrayHasKey('total', $receiptData['totals']);
            
            // Verify total matches transaction total (formatted as string with 2 decimals)
            $expectedTotal = number_format((float) $transaction->total, 2);
            $this->assertEquals(
                $expectedTotal,
                $receiptData['totals']['total'],
                "Receipt total should match transaction total"
            );
            
            // Verify subtotal matches
            $expectedSubtotal = number_format((float) $transaction->subtotal, 2);
            $this->assertEquals(
                $expectedSubtotal,
                $receiptData['totals']['subtotal'],
                "Receipt subtotal should match transaction subtotal"
            );
        });
    }
}
