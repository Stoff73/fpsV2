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
        Schema::create('iht_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Asset totals
            $table->decimal('user_gross_assets', 15, 2)->default(0);
            $table->decimal('spouse_gross_assets', 15, 2)->default(0);
            $table->decimal('total_gross_assets', 15, 2)->default(0);

            // Liability totals
            $table->decimal('user_total_liabilities', 15, 2)->default(0);
            $table->decimal('spouse_total_liabilities', 15, 2)->default(0);
            $table->decimal('total_liabilities', 15, 2)->default(0);

            // Net estate
            $table->decimal('user_net_estate', 15, 2)->default(0);
            $table->decimal('spouse_net_estate', 15, 2)->default(0);
            $table->decimal('total_net_estate', 15, 2)->default(0);

            // Allowances with messages
            $table->decimal('nrb_available', 15, 2)->default(0);
            $table->text('nrb_message')->nullable();

            $table->decimal('rnrb_available', 15, 2)->default(0);
            $table->enum('rnrb_status', ['full', 'tapered', 'none'])->default('none');
            $table->text('rnrb_message')->nullable();

            $table->decimal('total_allowances', 15, 2)->default(0);

            // IHT calculation
            $table->decimal('taxable_estate', 15, 2)->default(0);
            $table->decimal('iht_liability', 15, 2)->default(0);
            $table->decimal('effective_rate', 5, 2)->default(0);

            // Metadata
            $table->timestamp('calculation_date')->useCurrent();
            $table->boolean('is_married')->default(false);
            $table->boolean('data_sharing_enabled')->default(false);

            // Cache invalidation hashes
            $table->string('assets_hash', 64)->nullable();
            $table->string('liabilities_hash', 64)->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'calculation_date']);
            $table->index('assets_hash');
            $table->index('liabilities_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iht_calculations');
    }
};
