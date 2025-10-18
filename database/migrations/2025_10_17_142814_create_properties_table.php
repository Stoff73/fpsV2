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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('household_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('trust_id')->nullable()->constrained()->onDelete('set null');

            // Property type and ownership
            $table->enum('property_type', ['main_residence', 'secondary_residence', 'buy_to_let']);
            $table->enum('ownership_type', ['individual', 'joint'])->default('individual');
            $table->decimal('ownership_percentage', 5, 2)->default(100.00);

            // Address
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('county')->nullable();
            $table->string('postcode', 10);

            // Financial details
            $table->date('purchase_date');
            $table->decimal('purchase_price', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->date('valuation_date');
            $table->decimal('sdlt_paid', 15, 2)->nullable()->comment('Stamp Duty Land Tax paid');

            // BTL specific fields
            $table->decimal('monthly_rental_income', 10, 2)->nullable();
            $table->decimal('annual_rental_income', 15, 2)->nullable();
            $table->integer('occupancy_rate_percent')->nullable()->comment('Percentage of time property is occupied');
            $table->string('tenant_name')->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();

            // Costs
            $table->decimal('annual_service_charge', 10, 2)->nullable();
            $table->decimal('annual_ground_rent', 10, 2)->nullable();
            $table->decimal('annual_insurance', 10, 2)->nullable();
            $table->decimal('annual_maintenance_reserve', 10, 2)->nullable();
            $table->decimal('other_annual_costs', 10, 2)->nullable();

            // Additional info
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('household_id');
            $table->index('trust_id');
            $table->index('property_type');
            $table->index('ownership_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
