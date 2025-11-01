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
        Schema::create('factor_exposures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('holding_id')->nullable();
            $table->date('analysis_date');
            $table->decimal('market_beta', 6, 4)->nullable(); // β (market sensitivity)
            $table->decimal('alpha', 6, 4)->nullable(); // α (excess return)
            $table->decimal('r_squared', 5, 4)->nullable(); // R² (explained variance)
            $table->decimal('value_factor', 6, 4)->nullable(); // HML (High Minus Low)
            $table->decimal('size_factor', 6, 4)->nullable(); // SMB (Small Minus Big)
            $table->decimal('momentum_factor', 6, 4)->nullable(); // UMD (Up Minus Down)
            $table->decimal('quality_factor', 6, 4)->nullable(); // Quality exposure
            $table->decimal('low_vol_factor', 6, 4)->nullable(); // Low volatility exposure
            $table->timestamps();

            $table->index(['user_id', 'analysis_date']);
            $table->foreign('holding_id')->references('id')->on('holdings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factor_exposures');
    }
};
