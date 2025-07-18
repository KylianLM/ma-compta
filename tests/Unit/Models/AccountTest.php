<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    /** Test balance handling in cents. */
    public function test_balance_in_cents(): void
    {
        $account = Account::factory()->make([
            'balance_cents' => 123456, // 1234.56€
        ]);

        $this->assertEquals(123456, $account->balance_cents);

        $decimalBalance = $account->balance_cents / 100;
        $this->assertEquals(1234.56, $decimalBalance);
    }

    /** Test currency formatting. */
    public function test_currency_formatting(): void
    {
        $account = Account::factory()->make([
            'currency'      => Currency::EUR,
            'balance_cents' => 123456,
        ]);

        $formatted = $account->currency->formatAmount($account->balance_cents);
        $this->assertEquals('1 234,56 €', $formatted);
    }

    /** Test effective balance calculation for different account types. */
    public function test_effective_balance_calculation(): void
    {
        // Compte normal (positif)
        $checkingAccount = Account::factory()->make([
            'type'          => AccountType::CHECKING,
            'balance_cents' => 100000, // 1000.00€ en centimes
        ]);
        $this->assertEquals(100000, $checkingAccount->getEffectiveBalanceCents());

        // Compte de crédit (négatif pour l'effective balance)
        $creditAccount = Account::factory()->make([
            'type'          => AccountType::CREDIT,
            'balance_cents' => 50000, // 500.00€ en centimes
        ]);
        $this->assertEquals(-50000, $creditAccount->getEffectiveBalanceCents());
    }

    /** Test formatted balance with different currencies. */
    public function test_formatted_balance_with_currencies(): void
    {
        $testCases = [
            ['currency' => Currency::EUR, 'amount_cents' => 123456, 'expected' => '1 234,56 €'],
            ['currency' => Currency::USD, 'amount_cents' => 123456, 'expected' => '$1,234.56'],
            ['currency' => Currency::GBP, 'amount_cents' => 123456, 'expected' => '£1,234.56'],
        ];

        foreach ($testCases as $case) {
            $account = Account::factory()->make([
                'currency'      => $case['currency'],
                'balance_cents' => $case['amount_cents'],
            ]);

            $formatted = $account->currency->formatAmount($account->balance_cents);
            $this->assertEquals(
                $case['expected'],
                $formatted,
                "Failed for {$case['currency']->value}"
            );
        }
    }

    /** Test account number masking. */
    public function test_account_number_masking(): void
    {
        $testCases = [
            ['number' => '1234567890123456', 'expected' => '****3456'],
            ['number' => '12345', 'expected' => '****5'],
            ['number' => '123', 'expected' => '123'], // Trop court pour masquer
            ['number' => '', 'expected' => null],
            ['number' => null, 'expected' => null],
        ];

        foreach ($testCases as $case) {
            $account = Account::factory()->make([
                'account_number' => $case['number'],
            ]);

            $this->assertEquals(
                $case['expected'],
                $account->masked_account_number,
                "Failed for number {$case['number']}"
            );
        }
    }

    /** Test account type detection methods. */
    public function test_account_type_detection(): void
    {
        // Test debt account detection
        $creditAccount = Account::factory()->make(['type' => AccountType::CREDIT]);
        $this->assertTrue($creditAccount->type->isDebtAccount());

        $checkingAccount = Account::factory()->make(['type' => AccountType::CHECKING]);
        $this->assertFalse($checkingAccount->type->isDebtAccount());
    }

    /** Test scopes functionality. */
    public function test_account_scopes(): void
    {
        $user = User::factory()->create();

        // Créer des comptes de test avec des devises explicites
        Account::factory()->create([
            'user_id'   => $user->id,
            'is_active' => true,
            'type'      => AccountType::CHECKING,
            'currency'  => Currency::EUR,
        ]);
        Account::factory()->create([
            'user_id'   => $user->id,
            'is_active' => false,
            'type'      => AccountType::SAVINGS,
            'currency'  => Currency::EUR,
        ]);
        Account::factory()->create([
            'user_id'   => $user->id,
            'is_active' => true,
            'currency'  => Currency::USD,
        ]);

        // Test scope active
        $activeAccounts = Account::active()->where('user_id', $user->id)->get();
        $this->assertCount(2, $activeAccounts);
        $this->assertTrue($activeAccounts->every(fn ($account) => $account->is_active));

        // Test scope forUser
        $userAccounts = Account::forUser($user->id)->get();
        $this->assertCount(3, $userAccounts);
        $this->assertTrue($userAccounts->every(fn ($account) => $account->user_id === $user->id));

        // Test scope ofType
        $checkingAccounts = Account::ofType(AccountType::CHECKING)->where('user_id', $user->id)->get();
        $this->assertCount(1, $checkingAccounts);
        $this->assertTrue($checkingAccounts->every(fn ($account) => $account->type === AccountType::CHECKING));

        // Test scope inCurrency
        $usdAccounts = Account::inCurrency(Currency::USD)->where('user_id', $user->id)->get();
        $this->assertCount(1, $usdAccounts);
        $this->assertTrue($usdAccounts->every(fn ($account) => $account->currency === Currency::USD));
    }
}
