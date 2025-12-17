<?php

namespace Tests\Property;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 6: Cart Total Accuracy
 * Validates: Requirements 4.2
 * 
 * For any cart with products, the cart total must equal the sum of 
 * (unit_price × quantity) for all items in the cart.
 */
class CartTotalPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    protected function tearDown(): void
    {
        $this->cartService->clear();
        parent::tearDown();
    }

    /**
     * Property 6: Cart Total Accuracy
     * 
     * For any cart with products, the cart subtotal must equal the sum of 
     * (unit_price × quantity) for all items in the cart.
     */
    #[Test]
    public function cart_subtotal_equals_sum_of_item_subtotals(): void
    {
        // Create a category for products
        $category = Category::factory()->create();

        $this->forAll(
            Generators::tuple(
                Generators::choose(1, 1000),  // price in cents (to avoid float issues)
                Generators::choose(1, 10)     // quantity
            ),
            Generators::tuple(
                Generators::choose(1, 1000),
                Generators::choose(1, 10)
            ),
            Generators::tuple(
                Generators::choose(1, 1000),
                Generators::choose(1, 10)
            )
        )
        ->withMaxSize(100)
        ->__invoke(function (array $item1, array $item2, array $item3) use ($category): void {
            // Clear cart before each test
            $this->cartService->clear();
            
            $items = [$item1, $item2, $item3];
            $expectedSubtotal = 0.0;
            
            foreach ($items as [$priceCents, $quantity]) {
                // Convert cents to dollars for realistic prices
                $price = $priceCents / 100;
                
                // Create a product with sufficient stock
                $product = Product::factory()->create([
                    'category_id' => $category->id,
                    'price' => $price,
                    'stock_quantity' => 1000, // Ensure enough stock
                    'is_active' => true,
                ]);
                
                // Add to cart
                $this->cartService->add($product->id, $quantity);
                
                // Calculate expected subtotal for this item
                $expectedSubtotal += round($price * $quantity, 2);
            }
            
            // Get actual subtotal from cart
            $actualSubtotal = $this->cartService->getSubtotal();
            
            // Assert subtotal accuracy
            $this->assertEquals(
                round($expectedSubtotal, 2),
                $actualSubtotal,
                "Cart subtotal mismatch: expected {$expectedSubtotal}, got {$actualSubtotal}"
            );
            
            // Verify each item's subtotal is correct
            $cart = $this->cartService->getCart();
            foreach ($cart as $item) {
                $expectedItemSubtotal = round($item['price'] * $item['quantity'], 2);
                $this->assertEquals(
                    $expectedItemSubtotal,
                    $item['subtotal'],
                    "Item subtotal mismatch for product {$item['product_id']}"
                );
            }
        });
    }

    /**
     * Property 6 (Extended): Cart total equals subtotal plus tax
     * 
     * For any cart, the total must equal subtotal + (subtotal * tax_rate / 100).
     */
    #[Test]
    public function cart_total_equals_subtotal_plus_tax(): void
    {
        $category = Category::factory()->create();

        $this->forAll(
            Generators::tuple(
                Generators::choose(100, 10000),  // price in cents
                Generators::choose(1, 5)         // quantity
            ),
            Generators::tuple(
                Generators::choose(100, 10000),
                Generators::choose(1, 5)
            ),
            Generators::choose(0, 25)  // tax rate percentage
        )
        ->withMaxSize(50)
        ->__invoke(function (array $item1, array $item2, int $taxRate) use ($category): void {
            $this->cartService->clear();
            
            $items = [$item1, $item2];
            
            foreach ($items as [$priceCents, $quantity]) {
                $price = $priceCents / 100;
                
                $product = Product::factory()->create([
                    'category_id' => $category->id,
                    'price' => $price,
                    'stock_quantity' => 1000,
                    'is_active' => true,
                ]);
                
                $this->cartService->add($product->id, $quantity);
            }
            
            $subtotal = $this->cartService->getSubtotal();
            $expectedTax = round($subtotal * ($taxRate / 100), 2);
            $expectedTotal = round($subtotal + $expectedTax, 2);
            
            $actualTax = $this->cartService->getTax((float) $taxRate);
            $actualTotal = $this->cartService->getTotal((float) $taxRate);
            
            $this->assertEquals(
                $expectedTax,
                $actualTax,
                "Tax calculation mismatch: expected {$expectedTax}, got {$actualTax}"
            );
            
            $this->assertEquals(
                $expectedTotal,
                $actualTotal,
                "Total calculation mismatch: expected {$expectedTotal}, got {$actualTotal}"
            );
        });
    }

    /**
     * Property 6 (Item Count): Cart item count equals sum of quantities
     * 
     * For any cart, the item count must equal the sum of all item quantities.
     */
    #[Test]
    public function cart_item_count_equals_sum_of_quantities(): void
    {
        $category = Category::factory()->create();

        $this->forAll(
            Generators::choose(1, 10),  // quantity for item 1
            Generators::choose(1, 10),  // quantity for item 2
            Generators::choose(1, 10)   // quantity for item 3
        )
        ->withMaxSize(50)
        ->__invoke(function (int $qty1, int $qty2, int $qty3) use ($category): void {
            $this->cartService->clear();
            
            $quantities = [$qty1, $qty2, $qty3];
            $expectedCount = 0;
            
            foreach ($quantities as $quantity) {
                $product = Product::factory()->create([
                    'category_id' => $category->id,
                    'price' => 10.00,
                    'stock_quantity' => 1000,
                    'is_active' => true,
                ]);
                
                $this->cartService->add($product->id, $quantity);
                $expectedCount += $quantity;
            }
            
            $actualCount = $this->cartService->getItemCount();
            
            $this->assertEquals(
                $expectedCount,
                $actualCount,
                "Item count mismatch: expected {$expectedCount}, got {$actualCount}"
            );
        });
    }
}
