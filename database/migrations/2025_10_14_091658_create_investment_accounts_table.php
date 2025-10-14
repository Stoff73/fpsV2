<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('account_type', ['isa', 'gia', 'onshore_bond', 'offshore_bond', 'vct', 'eis']);
            $table->string('provider');
            $table->string('account_number')->nullable(); // Encrypted in application layer
            $table->string('platform')->nullable();
            $table->decimal('current_value', 15, 2)->default(0);
            $table->decimal('contributions_ytd', 15, 2)->default(0);
            $table->string('tax_year', 10); // e.g., "2024/25"
            $table->decimal('platform_fee_percent', 5, 4)->default(0);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'account_type']);
            $table->index(['user_id', 'tax_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_accounts');
    }
};
