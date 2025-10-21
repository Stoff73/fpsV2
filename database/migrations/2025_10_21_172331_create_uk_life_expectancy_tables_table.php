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
        Schema::create('uk_life_expectancy_tables', function (Blueprint $table) {
            $table->id();
            $table->integer('age');
            $table->enum('gender', ['male', 'female']);
            $table->decimal('life_expectancy_years', 5, 2);
            $table->string('table_version')->default('ONS_2020_2022'); // ONS National Life Tables
            $table->year('data_year')->default(2022);
            $table->timestamps();

            $table->unique(['age', 'gender', 'table_version']);
            $table->index(['age', 'gender']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uk_life_expectancy_tables');
    }
};
