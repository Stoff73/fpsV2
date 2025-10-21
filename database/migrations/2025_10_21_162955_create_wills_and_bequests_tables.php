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
        // Wills table - tracks user's will preferences
        Schema::create('wills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('death_scenario', ['user_only', 'both_simultaneous'])->default('user_only');
            $table->boolean('spouse_primary_beneficiary')->default(true); // If married, default to spouse
            $table->decimal('spouse_bequest_percentage', 5, 2)->default(100.00); // % to spouse if married
            $table->text('executor_notes')->nullable();
            $table->date('last_reviewed_date')->nullable();
            $table->timestamps();
        });

        // Bequests table - specific gifts/allocations to beneficiaries
        Schema::create('bequests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('will_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('beneficiary_name');
            $table->foreignId('beneficiary_user_id')->nullable()->constrained('users')->onDelete('set null'); // If beneficiary is a user
            $table->enum('bequest_type', ['percentage', 'specific_amount', 'specific_asset', 'residuary'])->default('percentage');
            $table->decimal('percentage_of_estate', 5, 2)->nullable(); // e.g., 25.00 for 25%
            $table->decimal('specific_amount', 15, 2)->nullable(); // e.g., £50,000
            $table->string('specific_asset_description')->nullable(); // e.g., "Property at 123 Main St"
            $table->foreignId('asset_id')->nullable(); // Link to specific asset if applicable
            $table->integer('priority_order')->default(1); // Order of distribution (1 = first)
            $table->text('conditions')->nullable(); // Any conditions on the bequest
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bequests');
        Schema::dropIfExists('wills');
    }
};
