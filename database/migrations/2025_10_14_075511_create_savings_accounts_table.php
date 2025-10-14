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
        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_type'); // e.g., 'easy_access', 'notice', 'fixed_rate'
            $table->string('institution');
            $table->string('account_number')->nullable(); // Will be encrypted in model
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->decimal('interest_rate', 5, 4)->default(0.0000); // e.g., 0.0400 for 4%
            $table->enum('access_type', ['immediate', 'notice', 'fixed'])->default('immediate');
            $table->integer('notice_period_days')->nullable();
            $table->date('maturity_date')->nullable();
            $table->boolean('is_isa')->default(false);
            $table->string('isa_type')->nullable(); // 'cash', 'stocks_shares', 'LISA'
            $table->string('isa_subscription_year')->nullable(); // e.g., '2024/25'
            $table->decimal('isa_subscription_amount', 15, 2)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_accounts');
    }
};
