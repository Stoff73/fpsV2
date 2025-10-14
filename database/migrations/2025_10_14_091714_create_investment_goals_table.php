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
        Schema::create('investment_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('goal_name');
            $table->enum('goal_type', ['retirement', 'education', 'wealth', 'home']);
            $table->decimal('target_amount', 15, 2);
            $table->date('target_date');
            $table->enum('priority', ['high', 'medium', 'low'])->default('medium');
            $table->boolean('is_essential')->default(false);
            $table->json('linked_account_ids')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'goal_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_goals');
    }
};
