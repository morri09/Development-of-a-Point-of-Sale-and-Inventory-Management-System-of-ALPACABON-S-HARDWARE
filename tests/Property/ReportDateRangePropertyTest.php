<?php

namespace Tests\Property;

use App\Enums\PaymentMethod;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature: pos-hardwarezone, Property 14: Report Date Range Filtering
 * Validates: Requirements 6.2, 6.3
 * 
 * For any date range filter applied to reports, all returned transactions must have 
 * created_at timestamps within the specified range, and totals must equal the sum 
 * of those transactions.
 */
class ReportDateRangePropertyTest extends TestCase
{
    use TestTrait;
    use RefreshDatabase;

    protected ReportService $reportService;
    protected User $user;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->reportService = new ReportService();
        
        // Create a user for transactions
        $this->user = User::factory()->create([
            'role' => UserRole::USER,
            'menu_permissions' => ['pos', 'transactions', 'reports'],
        ]);
        
        // Create a category for products
        $this->category = Category::factory()->create();
    }


    /**
     * Helper method to clean up transactions between iterations.
     */
    protected function cleanupTransactions(): void
    {
        TransactionItem::query()->delete();
        Transaction::query()->delete();
        Product::query()->delete();
    }

    /**
     * Helper method to create a transaction with a specific date.
     */
    protected function createTransactionOnDate(Carbon $date, float $total): Transaction
    {
        $product = Product::factory()->create([
            'category_id' => $this->category->id,
            'price' => $total,
            'stock_quantity' => 1000,
            'is_active' => true,
        ]);

        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'transaction_number' => 'TXN-' . uniqid(),
            'subtotal' => $total,
            'tax' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_method' => PaymentMethod::CASH,
        ]);

        // Manually set created_at to the desired date
        $transaction->created_at = $date;
        $transaction->save();

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $total,
            'subtotal' => $total,
        ]);

        return $transaction;
    }

    /**
     * Property 14: Report Date Range Filtering - Transactions within range
     * 
     * For any date range filter applied to reports, all returned transactions must have 
     * created_at timestamps within the specified range.
     */
    #[Test]
    public function report_returns_only_transactions_within_date_range(): void
    {
        $this->forAll(
            Generators::choose(1, 30),   // days before start date for "before" transactions
            Generators::choose(1, 30),   // range length in days
            Generators::choose(1, 30),   // days after end date for "after" transactions
            Generators::choose(1, 5),    // number of transactions before range
            Generators::choose(1, 5),    // number of transactions in range
            Generators::choose(1, 5)     // number of transactions after range
        )
        ->withMaxSize(100)
        ->__invoke(function (
            int $daysBefore,
            int $rangeLength,
            int $daysAfter,
            int $countBefore,
            int $countInRange,
            int $countAfter
        ) {
            // Clean up from previous iteration
            $this->cleanupTransactions();

            // Define the date range
            $startDate = Carbon::now()->subDays($daysBefore + $rangeLength);
            $endDate = $startDate->copy()->addDays($rangeLength);

            // Create transactions BEFORE the range
            for ($i = 0; $i < $countBefore; $i++) {
                $date = $startDate->copy()->subDays($daysBefore + $i);
                $this->createTransactionOnDate($date, 100.00 + $i);
            }

            // Create transactions WITHIN the range
            $inRangeTransactions = [];
            for ($i = 0; $i < $countInRange; $i++) {
                $daysIntoRange = $rangeLength > 0 ? ($i % ($rangeLength + 1)) : 0;
                $date = $startDate->copy()->addDays($daysIntoRange);
                $inRangeTransactions[] = $this->createTransactionOnDate($date, 200.00 + $i);
            }

            // Create transactions AFTER the range
            for ($i = 0; $i < $countAfter; $i++) {
                $date = $endDate->copy()->addDays($daysAfter + $i);
                $this->createTransactionOnDate($date, 300.00 + $i);
            }

            // Get transactions in range using ReportService
            $transactions = $this->reportService->getTransactionsInRange($startDate, $endDate);

            // Verify count matches expected in-range transactions
            $this->assertCount(
                $countInRange,
                $transactions,
                "Should return exactly {$countInRange} transactions within the date range"
            );

            // Verify all returned transactions are within the date range
            foreach ($transactions as $transaction) {
                $transactionDate = Carbon::parse($transaction->created_at)->startOfDay();
                $rangeStart = $startDate->copy()->startOfDay();
                $rangeEnd = $endDate->copy()->startOfDay();

                $this->assertTrue(
                    $transactionDate->greaterThanOrEqualTo($rangeStart) &&
                    $transactionDate->lessThanOrEqualTo($rangeEnd),
                    "Transaction date {$transactionDate} should be within range {$rangeStart} to {$rangeEnd}"
                );
            }
        });
    }


    /**
     * Property 14: Report Date Range Filtering - Totals accuracy
     * 
     * For any date range filter applied to reports, totals must equal the sum 
     * of those transactions within the range.
     */
    #[Test]
    public function report_totals_equal_sum_of_transactions_in_range(): void
    {
        $this->forAll(
            Generators::choose(1, 30),   // range length in days
            Generators::seq(Generators::choose(100, 10000))  // transaction amounts in cents
        )
        ->withMaxSize(50)
        ->__invoke(function (int $rangeLength, array $amountsCents) {
            // Clean up from previous iteration
            $this->cleanupTransactions();

            // Limit to reasonable number of transactions
            $amountsCents = array_slice($amountsCents, 0, 10);
            
            // Skip if no amounts
            if (empty($amountsCents)) {
                $amountsCents = [1000]; // Default to one transaction of $10
            }

            // Define the date range
            $startDate = Carbon::now()->subDays($rangeLength);
            $endDate = Carbon::now();

            // Create transactions within the range
            $expectedTotal = 0.0;
            foreach ($amountsCents as $index => $cents) {
                $amount = $cents / 100;
                $expectedTotal += $amount;
                
                // Spread transactions across the date range
                $daysIntoRange = $rangeLength > 0 ? ($index % ($rangeLength + 1)) : 0;
                $date = $startDate->copy()->addDays($daysIntoRange);
                $this->createTransactionOnDate($date, $amount);
            }

            // Get sales summary using ReportService
            $summary = $this->reportService->getSalesSummary($startDate, $endDate);

            // Verify total sales matches expected sum
            $this->assertEquals(
                round($expectedTotal, 2),
                round($summary['total_sales'], 2),
                "Total sales should equal sum of all transaction totals within range"
            );

            // Verify transaction count matches
            $this->assertEquals(
                count($amountsCents),
                $summary['total_transactions'],
                "Transaction count should match number of transactions created in range"
            );
        });
    }

    /**
     * Property 14: Report Date Range Filtering - Top products within range
     * 
     * For any date range filter, top-selling products should only include 
     * sales from transactions within that range.
     */
    #[Test]
    public function top_products_only_include_sales_within_date_range(): void
    {
        $this->forAll(
            Generators::choose(1, 15),   // range length in days
            Generators::choose(1, 5),    // quantity sold in range
            Generators::choose(1, 5)     // quantity sold outside range
        )
        ->withMaxSize(100)
        ->__invoke(function (int $rangeLength, int $qtyInRange, int $qtyOutRange) {
            // Clean up from previous iteration
            $this->cleanupTransactions();

            // Define the date range
            $startDate = Carbon::now()->subDays($rangeLength);
            $endDate = Carbon::now();

            // Create a product
            $product = Product::factory()->create([
                'category_id' => $this->category->id,
                'price' => 50.00,
                'stock_quantity' => 1000,
                'is_active' => true,
            ]);

            // Create transactions WITHIN the range
            for ($i = 0; $i < $qtyInRange; $i++) {
                $daysIntoRange = $rangeLength > 0 ? ($i % ($rangeLength + 1)) : 0;
                $date = $startDate->copy()->addDays($daysIntoRange);
                
                $transaction = Transaction::create([
                    'user_id' => $this->user->id,
                    'transaction_number' => 'TXN-IN-' . uniqid(),
                    'subtotal' => 50.00,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 50.00,
                    'payment_method' => PaymentMethod::CASH,
                ]);
                $transaction->created_at = $date;
                $transaction->save();

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 50.00,
                    'subtotal' => 50.00,
                ]);
            }

            // Create transactions OUTSIDE the range (before)
            for ($i = 0; $i < $qtyOutRange; $i++) {
                $date = $startDate->copy()->subDays($i + 1);
                
                $transaction = Transaction::create([
                    'user_id' => $this->user->id,
                    'transaction_number' => 'TXN-OUT-' . uniqid(),
                    'subtotal' => 50.00,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 50.00,
                    'payment_method' => PaymentMethod::CASH,
                ]);
                $transaction->created_at = $date;
                $transaction->save();

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 50.00,
                    'subtotal' => 50.00,
                ]);
            }

            // Get top products within the date range
            $topProducts = $this->reportService->getTopSellingProducts(10, $startDate, $endDate);

            // Find our product in the results
            $productResult = $topProducts->firstWhere('product_id', $product->id);

            // Verify the quantity only includes in-range sales
            $this->assertNotNull($productResult, "Product should appear in top products");
            $this->assertEquals(
                $qtyInRange,
                $productResult['total_quantity'],
                "Top products should only count quantity from transactions within the date range"
            );

            // Verify revenue only includes in-range sales
            $expectedRevenue = round($qtyInRange * 50.00, 2);
            $this->assertEquals(
                $expectedRevenue,
                $productResult['total_revenue'],
                "Top products revenue should only include sales within the date range"
            );
        });
    }
}
