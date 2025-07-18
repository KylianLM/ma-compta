<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumHelpers;

enum Currency: string
{
    use EnumHelpers;

    case EUR = 'EUR';
    case USD = 'USD';
    case GBP = 'GBP';
    case CHF = 'CHF';
    case CAD = 'CAD';

    /** Get currency symbol. */
    public function symbol(): string
    {
        return match ($this) {
            self::EUR => '€',
            self::USD => '$',
            self::GBP => '£',
            self::CHF => 'CHF',
            self::CAD => 'C$',
        };
    }

    /** Get human-readable name. */
    public function name(): string
    {
        return match ($this) {
            self::EUR => 'Euro',
            self::USD => 'Dollar américain',
            self::GBP => 'Livre sterling',
            self::CHF => 'Franc suisse',
            self::CAD => 'Dollar canadien',
        };
    }

    /**
     * Format amount with currency.
     */
    public function formatAmount(int $amountCents): string
    {
        $amount = $amountCents / 100;

        return match ($this) {
            self::EUR => number_format($amount, 2, ',', ' ') . ' €',
            self::USD => '$' . number_format($amount, 2, '.', ','),
            self::GBP => '£' . number_format($amount, 2, '.', ','),
            self::CHF => number_format($amount, 2, '.', '\'') . ' CHF',
            self::CAD => 'C$' . number_format($amount, 2, '.', ','),
        };
    }
}
