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
        Schema::create('recommendation_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('recommendation_id')->index(); // Generated unique ID for this recommendation
            $table->string('module'); // protection, savings, investment, retirement, estate
            $table->text('recommendation_text');
            $table->decimal('priority_score', 5, 2)->default(50.00);
            $table->enum('timeline', ['immediate', 'short_term', 'medium_term', 'long_term'])->default('medium_term');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'dismissed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendation_tracking');
    }
};
