<?php

use App\Enums\Currency;
use App\Enums\AccountType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('type', AccountType::values());
            $table->bigInteger('balance_cents')->default(0);
            $table->enum('currency', Currency::values())->default(Currency::EUR->value);
            $table->text('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type', 'is_active']);
            $table->index(['user_id', 'currency']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
