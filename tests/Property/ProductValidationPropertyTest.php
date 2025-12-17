<?php

namespace Tests\Property;

use App\Livewire\ProductForm;
use App\Models\Category;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 10: Product Validation Enforcement
 * Validates: Requirements 5.2
 * 
 * For any product creation attempt, the system must reject submissions 
 * missing name, price, or initial stock quantity.
 */
class ProductValidationPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Property 10: Product Validation Enforcement - Missing Name
     * 
     * For any product creation attempt with an empty or whitespace-only name,
     * the system must reject the submission.
     */
    #[Test]
    public function product_creation_rejects_missing_name(): void
    {
        $this->forAll(
            Generators::elements(['', ' ', '  ', "\t", "\n", '   ']),  // empty/whitespace names
            Generators::pos(),  // valid price (positive number)
            Generators::pos()   // valid stock quantity (positive number)
        )
        ->withMaxSize(100)
        ->__invoke(function (string $invalidName, int $validPrice, int $validStock): void {
            $component = Livewire::test(ProductForm::class)
                ->set('name', $invalidName)
                ->set('price', (string) $validPrice)
                ->set('stock_quantity', (string) $validStock)
                ->call('save');

            // Should have validation error for name
            $component->assertHasErrors(['name']);
        });
    }

    /**
     * Property 10: Product Validation Enforcement - Missing Price
     * 
     * For any product creation attempt with an empty price,
     * the system must reject the submission.
     */
    #[Test]
    public function product_creation_rejects_missing_price(): void
    {
        $this->forAll(
            Generators::string(),  // valid name
            Generators::pos()      // valid stock quantity
        )
        ->withMaxSize(50)
        ->__invoke(function (string $validName, int $validStock): void {
            // Skip empty names as they would fail name validation
            if (empty(trim($validName))) {
                return;
            }

            $component = Livewire::test(ProductForm::class)
                ->set('name', $validName)
                ->set('price', '')  // empty price
                ->set('stock_quantity', (string) $validStock)
                ->call('save');

            // Should have validation error for price
            $component->assertHasErrors(['price']);
        });
    }

    /**
     * Property 10: Product Validation Enforcement - Missing Stock Quantity
     * 
     * For any product creation attempt with an empty stock quantity,
     * the system must reject the submission.
     */
    #[Test]
    public function product_creation_rejects_missing_stock_quantity(): void
    {
        $this->forAll(
            Generators::string(),  // valid name
            Generators::pos()      // valid price
        )
        ->withMaxSize(50)
        ->__invoke(function (string $validName, int $validPrice): void {
            // Skip empty names as they would fail name validation
            if (empty(trim($validName))) {
                return;
            }

            $component = Livewire::test(ProductForm::class)
                ->set('name', $validName)
                ->set('price', (string) $validPrice)
                ->set('stock_quantity', '')  // empty stock quantity
                ->call('save');

            // Should have validation error for stock_quantity
            $component->assertHasErrors(['stock_quantity']);
        });
    }

    /**
     * Property 10: Product Validation Enforcement - Invalid Price (Non-numeric)
     * 
     * For any product creation attempt with a non-numeric price,
     * the system must reject the submission.
     */
    #[Test]
    public function product_creation_rejects_non_numeric_price(): void
    {
        $this->forAll(
            Generators::string(),  // valid name
            Generators::elements(['abc', 'not-a-number', '12.34.56', 'NaN', 'infinity']),  // invalid prices
            Generators::pos()      // valid stock quantity
        )
        ->withMaxSize(50)
        ->__invoke(function (string $validName, string $invalidPrice, int $validStock): void {
            // Skip empty names as they would fail name validation
            if (empty(trim($validName))) {
                return;
            }

            $component = Livewire::test(ProductForm::class)
                ->set('name', $validName)
                ->set('price', $invalidPrice)
                ->set('stock_quantity', (string) $validStock)
                ->call('save');

            // Should have validation error for price
            $component->assertHasErrors(['price']);
        });
    }

    /**
     * Property 10: Product Validation Enforcement - Negative Price
     * 
     * For any product creation attempt with a negative price,
     * the system must reject the submission.
     */
    #[Test]
    public function product_creation_rejects_negative_price(): void
    {
        $this->forAll(
            Generators::string(),  // valid name
            Generators::neg(),     // negative price
            Generators::pos()      // valid stock quantity
        )
        ->withMaxSize(50)
        ->__invoke(function (string $validName, int $negativePrice, int $validStock): void {
            // Skip empty names as they would fail name validation
            if (empty(trim($validName))) {
                return;
            }

            $component = Livewire::test(ProductForm::class)
                ->set('name', $validName)
                ->set('price', (string) $negativePrice)
                ->set('stock_quantity', (string) $validStock)
                ->call('save');

            // Should have validation error for price
            $component->assertHasErrors(['price']);
        });
    }

    /**
     * Property 10: Product Validation Enforcement - Negative Stock Quantity
     * 
     * For any product creation attempt with a negative stock quantity,
     * the system must reject the submission.
     */
    #[Test]
    public function product_creation_rejects_negative_stock_quantity(): void
    {
        $this->forAll(
            Generators::string(),  // valid name
            Generators::pos(),     // valid price
            Generators::neg()      // negative stock quantity
        )
        ->withMaxSize(50)
        ->__invoke(function (string $validName, int $validPrice, int $negativeStock): void {
            // Skip empty names as they would fail name validation
            if (empty(trim($validName))) {
                return;
            }

            $component = Livewire::test(ProductForm::class)
                ->set('name', $validName)
                ->set('price', (string) $validPrice)
                ->set('stock_quantity', (string) $negativeStock)
                ->call('save');

            // Should have validation error for stock_quantity
            $component->assertHasErrors(['stock_quantity']);
        });
    }

    /**
     * Property 10: Product Validation Enforcement - Valid Product Accepted
     * 
     * For any product creation attempt with valid name, price, and stock quantity,
     * the system must accept the submission.
     */
    #[Test]
    public function product_creation_accepts_valid_data(): void
    {
        // Create a category for valid product creation (required by database schema)
        $category = Category::factory()->create();

        $this->forAll(
            Generators::string(),  // name
            Generators::pos(),     // price
            Generators::pos()      // stock quantity
        )
        ->withMaxSize(50)
        ->__invoke(function (string $name, int $price, int $stockQuantity) use ($category): void {
            // Skip empty names
            if (empty(trim($name))) {
                return;
            }

            // Truncate name to max 255 characters
            $name = substr($name, 0, 255);

            $component = Livewire::test(ProductForm::class)
                ->set('name', $name)
                ->set('category_id', $category->id)
                ->set('price', (string) $price)
                ->set('stock_quantity', (string) $stockQuantity)
                ->call('save');

            // Should NOT have validation errors for required fields
            $component->assertHasNoErrors(['name', 'price', 'stock_quantity']);
        });
    }
}
