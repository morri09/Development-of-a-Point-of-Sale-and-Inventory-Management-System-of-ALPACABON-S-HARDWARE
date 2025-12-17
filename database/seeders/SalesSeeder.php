<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user or first user
        $user = User::where('role', 'admin')->first() ?? User::first();
        
        if (!$user) {
            $this->command->warn('No user found. Please run UserSeeder first.');
            return;
        }

        // Get all products
        $products = Product::all();
        
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Please run ProductSeeder first.');
            return;
        }

        $paymentMethods = PaymentMethod::cases();
        $taxRate = 0.12; // 12% tax

        // Generate sales for the last 30 days
        for ($daysAgo = 30; $daysAgo >= 0; $daysAgo--) {
            $date = Carbon::today()->subDays($daysAgo);
            
            // Random number of transactions per day (3-10)
            $transactionsPerDay = rand(3, 10);
            
            for ($t = 0; $t < $transactionsPerDay; $t++) {
                // Random time during business hours (8am - 8pm)
                $hour = rand(8, 20);
                $minute = rand(0, 59);
                $transactionDate = $date->copy()->setTime($hour, $minute);

                // Generate transaction number
                $transactionNumber = 'TXN-' . $transactionDate->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // Random number of items (1-5)
                $itemCount = rand(1, 5);
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                $subtotal = 0;
                $items = [];

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 3);
                    $unitPrice = $product->price;
                    $itemSubtotal = $quantity * $unitPrice;
                    $subtotal += $itemSubtotal;

                    $items[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $itemSubtotal,
                    ];
                }

                $tax = $subtotal * $taxRate;
                $discount = rand(0, 10) > 8 ? rand(50, 200) : 0; // 20% chance of discount
                $total = $subtotal + $tax - $discount;

                // Create transaction
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'transaction_number' => $transactionNumber,
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'created_at' => $transactionDate,
                    'updated_at' => $transactionDate,
                ]);

                // Create transaction items
                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'subtotal' => $item['subtotal'],
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ]);
                }
            }
        }

        $this->command->info('Sales data seeded successfully for the last 30 days.');
    }
}
