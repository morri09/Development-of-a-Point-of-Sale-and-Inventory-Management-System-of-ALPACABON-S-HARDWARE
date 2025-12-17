<?php

namespace Tests\Property;

use App\Models\Category;
use App\Models\Product;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 13: Product Search Accuracy
 * Validates: Requirements 5.5
 * 
 * For any search query, all returned products must match the query 
 * against name, SKU, or category name.
 */
class ProductSearchPropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    /**
     * Property 13: Product Search Accuracy - All Results Match Query
     * 
     * For any search query, all returned products must contain the search term
     * in either their name, SKU, or category name.
     */
    #[Test]
    public function search_results_match_query_in_name_sku_or_category(): void
    {
        // Create categories with known names that include search terms
        $categoryNames = ['Tools', 'Plumbing Supplies', 'Electrical', 'Hardware', 'Paint'];
        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::factory()->create(['name' => $name]);
        }
        
        // Create products with names containing known search terms
        $productNames = [
            'Hammer Steel', 'Nail Set', 'Screw Driver', 'Bolt Cutter', 'Pipe Wrench',
            'Wire Stripper', 'Tool Box', 'Electric Drill', 'Paint Brush', 'Hardware Kit'
        ];
        
        foreach ($productNames as $index => $name) {
            Product::factory()->create([
                'category_id' => $categories[$index % count($categories)]->id,
                'name' => $name,
            ]);
        }

        $this->forAll(
            Generators::elements(['Hammer', 'Nail', 'Screw', 'Bolt', 'Pipe', 'Wire', 'Tool', 'Electric', 'Paint', 'Hardware'])
        )
        ->withMaxSize(100)
        ->__invoke(function (string $searchTerm): void {
            // Perform the search query (same logic as ProductTable)
            $results = Product::query()
                ->with('category')
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                          $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                      });
                })
                ->get();

            // Ensure we have results to test
            $this->assertGreaterThan(0, $results->count(), "No results found for search term '{$searchTerm}'");

            // Verify each result matches the search term in name, SKU, or category
            foreach ($results as $product) {
                $matchesName = stripos($product->name, $searchTerm) !== false;
                $matchesSku = stripos($product->sku, $searchTerm) !== false;
                $matchesCategory = $product->category && stripos($product->category->name, $searchTerm) !== false;

                $this->assertTrue(
                    $matchesName || $matchesSku || $matchesCategory,
                    "Product '{$product->name}' (SKU: {$product->sku}, Category: {$product->category?->name}) " .
                    "does not match search term '{$searchTerm}'"
                );
            }
        });
    }

    /**
     * Property 13: Product Search Accuracy - Products Matching Name Are Found
     * 
     * For any product with a specific name, searching for a substring of that name
     * must return that product in the results.
     */
    #[Test]
    public function products_matching_name_are_found(): void
    {
        $category = Category::factory()->create();

        $this->forAll(
            Generators::elements([
                'Hammer Steel',
                'Electric Drill',
                'Screwdriver Set',
                'Pipe Wrench',
                'Wire Cutter'
            ])
        )
        ->withMaxSize(100)
        ->__invoke(function (string $productName) use ($category): void {
            // Create a product with the specific name
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'name' => $productName,
            ]);

            // Extract a search term from the product name (first word)
            $searchTerm = explode(' ', $productName)[0];

            // Perform the search
            $results = Product::query()
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                          $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                      });
                })
                ->get();

            // The created product must be in the results
            $this->assertTrue(
                $results->contains('id', $product->id),
                "Product '{$productName}' was not found when searching for '{$searchTerm}'"
            );

            // Clean up for next iteration
            $product->delete();
        });
    }

    /**
     * Property 13: Product Search Accuracy - Products Matching SKU Are Found
     * 
     * For any product with a specific SKU, searching for that SKU
     * must return that product in the results.
     */
    #[Test]
    public function products_matching_sku_are_found(): void
    {
        $category = Category::factory()->create();

        $this->forAll(
            Generators::elements(['ABC-1234', 'XYZ-5678', 'HW-9999', 'TOOL-0001', 'PIPE-2222'])
        )
        ->withMaxSize(100)
        ->__invoke(function (string $sku) use ($category): void {
            // Create a product with the specific SKU
            $product = Product::factory()->create([
                'category_id' => $category->id,
                'sku' => $sku,
            ]);

            // Search for the SKU prefix
            $searchTerm = substr($sku, 0, 3);

            // Perform the search
            $results = Product::query()
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                          $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                      });
                })
                ->get();

            // The created product must be in the results
            $this->assertTrue(
                $results->contains('id', $product->id),
                "Product with SKU '{$sku}' was not found when searching for '{$searchTerm}'"
            );

            // Clean up for next iteration
            $product->delete();
        });
    }

    /**
     * Property 13: Product Search Accuracy - Products Matching Category Are Found
     * 
     * For any product in a category, searching for the category name
     * must return that product in the results.
     */
    #[Test]
    public function products_matching_category_are_found(): void
    {
        $this->forAll(
            Generators::elements(['Tools', 'Plumbing', 'Electrical', 'Hardware', 'Paint'])
        )
        ->withMaxSize(100)
        ->__invoke(function (string $categoryName): void {
            // Create a category with the specific name
            $category = Category::factory()->create([
                'name' => $categoryName,
            ]);

            // Create a product in that category
            $product = Product::factory()->create([
                'category_id' => $category->id,
            ]);

            // Search for the category name
            $searchTerm = $categoryName;

            // Perform the search
            $results = Product::query()
                ->with('category')
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                          $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                      });
                })
                ->get();

            // The created product must be in the results
            $this->assertTrue(
                $results->contains('id', $product->id),
                "Product in category '{$categoryName}' was not found when searching for '{$searchTerm}'"
            );

            // Clean up for next iteration
            $product->delete();
            $category->delete();
        });
    }

    /**
     * Property 13: Product Search Accuracy - Empty Search Returns All Products
     * 
     * When no search term is provided, the query should not filter any products.
     */
    #[Test]
    public function empty_search_does_not_filter_products(): void
    {
        $category = Category::factory()->create();
        
        // Create some products
        $products = Product::factory()->count(5)->create([
            'category_id' => $category->id,
        ]);

        // Empty search should return all products (no filtering applied)
        $searchTerm = '';
        
        $results = Product::query()
            ->with('category')
            ->when($searchTerm, function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('sku', 'like', '%' . $searchTerm . '%')
                      ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                          $categoryQuery->where('name', 'like', '%' . $searchTerm . '%');
                      });
                });
            })
            ->get();

        // All products should be returned
        $this->assertCount(5, $results);
        
        foreach ($products as $product) {
            $this->assertTrue(
                $results->contains('id', $product->id),
                "Product '{$product->name}' was not found in empty search results"
            );
        }
    }
}
