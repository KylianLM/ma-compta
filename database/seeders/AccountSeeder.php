<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Currency;
use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /** Run the database seeds. */
    public function run(): void
    {
        // Créer un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => 'test@macompta.local'],
            [
                'name'     => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Comptes spécifiques et réalistes
        $this->createSpecificAccounts($user);

        // Comptes générés aléatoirement pour plus de variété
        $this->createRandomAccounts($user);

        $this->command->info('✅ Accounts seeded successfully!');
        $this->command->info('📧 User email: test@macompta.local');
        $this->command->info('🔒 Password: password');
        $this->command->info('📊 Total accounts: ' . Account::count());
    }

    /**
     * Create specific realistic accounts.
     */
    private function createSpecificAccounts(User $user): void
    {
        // Compte courant principal
        Account::factory()
            ->checking()
            ->withBalance(1850.75)
            ->for($user)
            ->create([
                'name'        => 'Compte courant BNP',
                'bank_name'   => 'BNP Paribas',
                'description' => 'Compte principal pour les dépenses courantes',
            ]);

        // Livret A
        Account::factory()
            ->savings()
            ->withBalance(5200.00)
            ->for($user)
            ->create([
                'name'        => 'Livret A',
                'bank_name'   => 'Crédit Agricole',
                'description' => 'Épargne de précaution',
            ]);

        // Carte de crédit
        Account::factory()
            ->credit()
            ->withBalance(450.30)
            ->for($user)
            ->create([
                'name'        => 'Carte Visa',
                'bank_name'   => 'BNP Paribas',
                'description' => 'Carte de crédit pour les achats en ligne',
            ]);

        // PEA
        Account::factory()
            ->investment()
            ->withBalance(3750.90)
            ->for($user)
            ->create([
                'name'        => 'PEA Boursorama',
                'bank_name'   => 'Boursorama',
                'description' => 'Plan d\'épargne en actions',
            ]);

        // Compte USD
        Account::factory()
            ->checking()
            ->currency(Currency::USD)
            ->withBalance(850.25)
            ->for($user)
            ->create([
                'name'        => 'Compte USD',
                'bank_name'   => 'Chase Bank',
                'description' => 'Compte en dollars pour les voyages',
            ]);

        // Ancien compte inactif
        Account::factory()
            ->savings()
            ->inactive()
            ->withBalance(125.50)
            ->for($user)
            ->create([
                'name'        => 'Ancien Livret',
                'bank_name'   => 'Banque Postale',
                'description' => 'Ancien compte épargne peu utilisé',
            ]);
    }

    /**
     * Create random accounts for variety.
     */
    private function createRandomAccounts(User $user): void
    {
        // Quelques comptes supplémentaires aléatoires
        Account::factory()
            ->count(3)
            ->for($user)
            ->create();

        // Un compte inactif supplémentaire
        Account::factory()
            ->inactive()
            ->for($user)
            ->create();
    }
}
