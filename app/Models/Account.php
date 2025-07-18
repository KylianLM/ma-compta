<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AccountType;
use App\Enums\Currency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    /** The attributes that are mass assignable. */
    protected $fillable = [
        'name',
        'type',
        'balance_cents',
        'currency',
        'account_number',
        'bank_name',
        'is_active',
        'description',
    ];

    /** The attributes that should be cast. */
    protected $casts = [
        'balance_cents' => 'integer',
        'is_active'     => 'boolean',
        'type'          => AccountType::class,
        'currency'      => Currency::class,
        // Chiffrement des données sensibles
        'description'    => 'encrypted',
        'account_number' => 'encrypted',
    ];

    /** The attributes that should be hidden for serialization. */
    protected $hidden = [
        'account_number', // Ne pas exposer dans les API par défaut
    ];

    /** Get the user that owns the account. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for active accounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific account type.
     */
    public function scopeOfType(Builder $query, AccountType $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for specific currency.
     */
    public function scopeInCurrency(Builder $query, Currency $currency): Builder
    {
        return $query->where('currency', $currency);
    }

    /**
     * Scope for user's accounts.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /** Get the account's effective balance (negative for debt accounts). */
    public function getEffectiveBalanceCents(): int
    {
        return $this->type->isDebtAccount() ? -$this->balance_cents : $this->balance_cents;
    }

    /** Get formatted balance with currency. */
    public function getFormattedBalanceAttribute(): string
    {
        return $this->currency->formatAmount($this->balance_cents);
    }

    /** Get masked account number for display. */
    public function getMaskedAccountNumberAttribute(): ?string
    {
        if ( ! $this->account_number) {
            return null;
        }

        $number = $this->account_number;
        $length = strlen($number);

        if ($length <= 4) {
            return $number;
        }

        // Masquer les 4 premiers caractères
        return '****' . substr($number, 4);
    }
}
