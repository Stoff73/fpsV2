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
        Schema::create('isa_allowance_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tax_year'); // e.g., '2024/25'
            $table->decimal('cash_isa_used', 10, 2)->default(0.00);
            $table->decimal('stocks_shares_isa_used', 10, 2)->default(0.00);
            $table->decimal('lisa_used', 10, 2)->default(0.00);
            $table->decimal('total_used', 10, 2)->default(0.00);
            $table->decimal('total_allowance', 10, 2)->default(20000.00); // £20,000 for 2024/25
            $table->timestamps();

            $table->unique(['user_id', 'tax_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isa_allowance_tracking');
    }
};
