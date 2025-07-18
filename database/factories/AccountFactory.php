<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountType;
use App\Enums\Currency;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    /** Define the model's default state. */
    public function definition(): array
    {
        $type     = $this->faker->randomElement(AccountType::cases());
        $currency = $this->faker->randomElement(Currency::cases());

        return [
            'user_id'       => User::factory(),
            'name'          => $this->generateAccountName($type),
            'type'          => $type,
            'balance_cents' => $this->faker->numberBetween(-100000, 1000000), // -1000€ à 10000€ en centimes
            'currency'      => $currency,
            'bank_name'     => $this->faker->randomElement([
                'BNP Paribas', 'Crédit Agricole', 'Société Générale', 'LCL',
                'Banque Postale', 'Boursorama', 'ING', 'Revolut', 'N26',
            ]),
            'account_number' => $this->generateAccountNumber($type),
            'description'    => $this->faker->optional(0.7)->sentence(),
            'is_active'      => $this->faker->boolean(90), // 90% actifs
        ];
    }

    /** Create a checking account. */
    public function checking(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'          => AccountType::CHECKING,
            'name'          => $this->generateAccountName(AccountType::CHECKING),
            'balance_cents' => $this->faker->numberBetween(0, 500000), // 0 à 5000€
        ]);
    }

    /** Create a savings account. */
    public function savings(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'          => AccountType::SAVINGS,
            'name'          => $this->generateAccountName(AccountType::SAVINGS),
            'balance_cents' => $this->faker->numberBetween(10000, 2000000), // 100€ à 20000€
        ]);
    }

    /** Create a credit account. */
    public function credit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'          => AccountType::CREDIT,
            'name'          => $this->generateAccountName(AccountType::CREDIT),
            'balance_cents' => $this->faker->numberBetween(0, 200000), // 0 à 2000€
        ]);
    }

    /** Create an investment account. */
    public function investment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type'          => AccountType::INVESTMENT,
            'name'          => $this->generateAccountName(AccountType::INVESTMENT),
            'balance_cents' => $this->faker->numberBetween(50000, 5000000), // 500€ à 50000€
        ]);
    }

    /** Create an inactive account. */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active'     => false,
            'balance_cents' => $this->faker->numberBetween(0, 50000), // 0 à 500€
        ]);
    }

    /**
     * Create account with specific currency.
     */
    public function currency(Currency $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => $currency,
        ]);
    }

    /**
     * Create account with specific balance in cents.
     */
    public function withBalanceCents(int $balanceCents): static
    {
        return $this->state(fn (array $attributes) => [
            'balance_cents' => $balanceCents,
        ]);
    }

    /**
     * Create account with specific balance in euros (converted to cents).
     */
    public function withBalance(float $balance): static
    {
        return $this->withBalanceCents((int) round($balance * 100));
    }

    /**
     * Generate account name based on type.
     */
    private function generateAccountName(AccountType $type): string
    {
        return match ($type) {
            AccountType::CHECKING => $this->faker->randomElement([
                'Compte courant principal',
                'Compte chèques',
                'Compte courant BNP',
                'Compte principal',
                'Compte quotidien',
            ]),
            AccountType::SAVINGS => $this->faker->randomElement([
                'Livret A',
                'Livret développement durable',
                'Livret jeune',
                'Compte épargne',
                'Épargne de précaution',
            ]),
            AccountType::CREDIT => $this->faker->randomElement([
                'Carte Visa',
                'Carte MasterCard',
                'Carte American Express',
                'Carte de crédit',
                'Crédit renouvelable',
            ]),
            AccountType::INVESTMENT => $this->faker->randomElement([
                'PEA',
                'Assurance vie',
                'Compte-titres',
                'Portefeuille d\'investissement',
                'Compte trading',
            ]),
        };
    }

    /**
     * Generate account number based on type.
     */
    private function generateAccountNumber(AccountType $type): string
    {
        return match ($type) {
            AccountType::CHECKING   => $this->faker->numerify('################'), // 16 chiffres
            AccountType::SAVINGS    => $this->faker->numerify('################'),
            AccountType::CREDIT     => $this->faker->numerify('################'), // Format carte
            AccountType::INVESTMENT => $this->faker->numerify('##########'), // 10 chiffres
        };
    }
}
