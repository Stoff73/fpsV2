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
        Schema::create('investment_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('investment_plan_id')->nullable()->constrained()->onDelete('set null');
            $table->string('category', 50); // rebalancing, tax, fees, risk, goal, contribution
            $table->integer('priority'); // 1 (highest) to N
            $table->string('title');
            $table->text('description');
            $table->text('action_required');
            $table->string('impact_level', 20)->nullable(); // low, medium, high
            $table->decimal('potential_saving', 10, 2)->nullable(); // Annual saving in £
            $table->string('estimated_effort', 20)->nullable(); // quick, moderate, significant
            $table->string('status', 20)->default('pending'); // pending, in_progress, completed, dismissed
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->text('dismissal_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_recommendations');
    }
};
