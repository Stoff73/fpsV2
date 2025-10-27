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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('asset_type', ['property', 'pension', 'investment', 'business', 'other']);
            $table->string('asset_name');
            $table->decimal('current_value', 15, 2);
            $table->enum('ownership_type', ['individual', 'joint', 'trust']);
            $table->string('beneficiary_designation')->nullable();
            $table->boolean('is_iht_exempt')->default(false);
            $table->string('exemption_reason')->nullable();
            $table->date('valuation_date');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
