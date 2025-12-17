<?php

namespace App\Services;

use App\Models\Transaction;

class ReceiptService
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Generate receipt data from a transaction.
     *
     * @param Transaction $transaction
     * @return array<string, mixed>
     */
    public function generateReceiptData(Transaction $transaction): array
    {
        // Load relationships if not already loaded
        $transaction->loadMissing(['items.product', 'user']);

        // Get store settings
        $storeSettings = $this->settingsService->getStoreSettings();

        return [
            'store' => [
                'name' => $storeSettings['store_name'] ?? 'Store Name',
                'address' => $storeSettings['store_address'] ?? '',
                'contact' => $storeSettings['store_contact'] ?? '',
                'email' => $storeSettings['store_email'] ?? '',
            ],
            'transaction' => [
                'number' => $transaction->transaction_number,
                'date' => $transaction->created_at->format('M d, Y'),
                'time' => $transaction->created_at->format('h:i A'),
                'cashier' => $transaction->user->name ?? 'Unknown',
                'payment_method' => $transaction->payment_method->value ?? 'cash',
            ],
            'items' => $this->formatItems($transaction),
            'totals' => [
                'subtotal' => number_format((float) $transaction->subtotal, 2),
                'tax' => number_format((float) $transaction->tax, 2),
                'discount' => number_format((float) $transaction->discount, 2),
                'total' => number_format((float) $transaction->total, 2),
            ],
            'footer' => $storeSettings['receipt_footer'] ?? 'Thank you for shopping!',
        ];
    }

    /**
     * Format transaction items for receipt display.
     *
     * @param Transaction $transaction
     * @return array<int, array<string, mixed>>
     */
    protected function formatItems(Transaction $transaction): array
    {
        return $transaction->items->map(function ($item) {
            return [
                'name' => $item->product->name ?? 'Unknown Product',
                'quantity' => $item->quantity,
                'unit_price' => number_format((float) $item->unit_price, 2),
                'subtotal' => number_format((float) $item->subtotal, 2),
            ];
        })->toArray();
    }

    /**
     * Generate receipt data by transaction ID.
     *
     * @param int $transactionId
     * @return array<string, mixed>|null
     */
    public function generateReceiptDataById(int $transactionId): ?array
    {
        $transaction = Transaction::with(['items.product', 'user'])->find($transactionId);

        if (!$transaction) {
            return null;
        }

        return $this->generateReceiptData($transaction);
    }

    /**
     * Generate receipt data by transaction number.
     *
     * @param string $transactionNumber
     * @return array<string, mixed>|null
     */
    public function generateReceiptDataByNumber(string $transactionNumber): ?array
    {
        $transaction = Transaction::with(['items.product', 'user'])
            ->where('transaction_number', $transactionNumber)
            ->first();

        if (!$transaction) {
            return null;
        }

        return $this->generateReceiptData($transaction);
    }
}
