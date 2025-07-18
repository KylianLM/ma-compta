<?php

declare(strict_types=1);

namespace App\Enums;

use App\Traits\EnumHelpers;

enum AccountType: string
{
    use EnumHelpers;
    case CHECKING   = 'checking';
    case SAVINGS    = 'savings';
    case CREDIT     = 'credit';
    case INVESTMENT = 'investment';

    /** Get human-readable labels. */
    public function label(): string
    {
        return match ($this) {
            self::CHECKING   => 'Compte courant',
            self::SAVINGS    => 'Livret d\'épargne',
            self::CREDIT     => 'Carte de crédit',
            self::INVESTMENT => 'Compte d\'investissement',
        };
    }

    /** Get icon for UI. */
    public function icon(): string
    {
        return match ($this) {
            self::CHECKING   => 'credit-card',
            self::SAVINGS    => 'piggy-bank',
            self::CREDIT     => 'credit-card',
            self::INVESTMENT => 'trending-up',
        };
    }

    /** Check if this is a debt account. */
    public function isDebtAccount(): bool
    {
        return $this === self::CREDIT;
    }
}
