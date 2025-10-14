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
        Schema::create('holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investment_account_id')->constrained()->onDelete('cascade');
            $table->enum('asset_type', ['equity', 'bond', 'fund', 'etf', 'alternative']);
            $table->string('security_name');
            $table->string('ticker')->nullable();
            $table->string('isin')->nullable();
            $table->decimal('quantity', 15, 6);
            $table->decimal('purchase_price', 15, 4);
            $table->date('purchase_date');
            $table->decimal('current_price', 15, 4);
            $table->decimal('current_value', 15, 2);
            $table->decimal('cost_basis', 15, 2);
            $table->decimal('dividend_yield', 5, 4)->default(0);
            $table->decimal('ocf_percent', 5, 4)->default(0); // Ongoing charges figure
            $table->timestamps();

            $table->index('investment_account_id');
            $table->index('asset_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holdings');
    }
};
