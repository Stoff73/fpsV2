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
        Schema::create('investment_scenarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('scenario_name');
            $table->text('description')->nullable();

            // Scenario type: 'custom', 'template', 'comparison'
            $table->enum('scenario_type', ['custom', 'template', 'comparison'])->default('custom');

            // Template name if using a pre-built scenario
            $table->string('template_name')->nullable();

            // Scenario parameters (JSON)
            $table->json('parameters');

            // Results from Monte Carlo simulation (JSON)
            $table->json('results')->nullable();

            // Comparison data if comparing scenarios (JSON)
            $table->json('comparison_data')->nullable();

            // Status: 'draft', 'running', 'completed', 'failed'
            $table->enum('status', ['draft', 'running', 'completed', 'failed'])->default('draft');

            // Is this scenario saved/bookmarked?
            $table->boolean('is_saved')->default(false);

            // Monte Carlo job ID if running simulation
            $table->string('monte_carlo_job_id')->nullable();

            // Timestamps
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'is_saved']);
            $table->index('monte_carlo_job_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_scenarios');
    }
};
