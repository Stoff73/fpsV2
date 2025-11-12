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
        Schema::create('actuarial_life_tables', function (Blueprint $table) {
            $table->id();

            // Demographics
            $table->unsignedTinyInteger('age');
            $table->enum('gender', ['male', 'female']);

            // Life expectancy data (UK ONS National Life Tables)
            $table->decimal('life_expectancy_years', 4, 2);
            $table->decimal('probability_of_death', 6, 5);

            // Data source
            $table->string('table_year', 10); // e.g., "2020-2022"
            $table->string('table_source', 100)->default('UK ONS National Life Tables');

            $table->timestamps();

            // Indexes
            $table->unique(['age', 'gender', 'table_year'], 'unique_age_gender_year');
            $table->index(['age', 'gender'], 'idx_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actuarial_life_tables');
    }
};
