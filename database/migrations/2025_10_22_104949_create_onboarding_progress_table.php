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
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('focus_area', ['estate', 'protection', 'retirement', 'investment', 'tax_optimisation']);
            $table->string('step_name');
            $table->json('step_data')->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('skipped')->default(false);
            $table->boolean('skip_reason_shown')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'focus_area']);
            $table->index(['user_id', 'step_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
