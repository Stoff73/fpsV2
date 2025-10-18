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
        Schema::create('chattels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('household_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('trust_id')->nullable()->constrained()->onDelete('set null');

            // Chattel details
            $table->enum('chattel_type', ['vehicle', 'art', 'antique', 'jewelry', 'collectible', 'other']);
            $table->string('name');
            $table->text('description')->nullable();

            // Ownership
            $table->enum('ownership_type', ['individual', 'joint'])->default('individual');
            $table->decimal('ownership_percentage', 5, 2)->default(100.00);

            // Valuation
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('current_value', 15, 2);
            $table->date('valuation_date');

            // Vehicle-specific fields
            $table->string('make')->nullable()->comment('Vehicle make');
            $table->string('model')->nullable()->comment('Vehicle model');
            $table->year('year')->nullable()->comment('Vehicle year');
            $table->string('registration_number')->nullable()->comment('Vehicle registration');

            // Additional info
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('household_id');
            $table->index('trust_id');
            $table->index('chattel_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chattels');
    }
};
