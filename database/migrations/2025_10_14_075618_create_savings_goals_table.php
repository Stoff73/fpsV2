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
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('goal_name');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_saved', 15, 2)->default(0.00);
            $table->date('target_date');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->foreignId('linked_account_id')->nullable()->constrained('savings_accounts')->onDelete('set null');
            $table->decimal('auto_transfer_amount', 10, 2)->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};
