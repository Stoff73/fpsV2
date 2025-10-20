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
        Schema::table('properties', function (Blueprint $table) {
            // Make address fields nullable and optional for simple form
            $table->string('city')->nullable()->change();
            $table->date('purchase_date')->nullable()->change();
            $table->decimal('purchase_price', 15, 2)->nullable()->change();
            $table->date('valuation_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('city')->nullable(false)->change();
            $table->date('purchase_date')->nullable(false)->change();
            $table->decimal('purchase_price', 15, 2)->nullable(false)->change();
            $table->date('valuation_date')->nullable(false)->change();
        });
    }
};
