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
        Schema::create('risk_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('risk_tolerance', ['cautious', 'balanced', 'adventurous']);
            $table->decimal('capacity_for_loss_percent', 5, 2);
            $table->integer('time_horizon_years');
            $table->enum('knowledge_level', ['novice', 'intermediate', 'experienced']);
            $table->string('attitude_to_volatility')->nullable();
            $table->boolean('esg_preference')->default(false);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_profiles');
    }
};
