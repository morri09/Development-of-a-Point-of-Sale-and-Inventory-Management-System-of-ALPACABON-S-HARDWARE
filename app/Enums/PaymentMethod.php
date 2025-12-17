<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case GCASH = 'gcash';

    /**
     * Get all payment method values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the display label for the payment method.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::CASH => 'Cash',
            self::GCASH => 'GCash',
        };
    }
}
