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
        Schema::create('iht_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('marital_status', ['single', 'married', 'widowed', 'divorced']);
            $table->boolean('has_spouse')->default(false);
            $table->boolean('own_home')->default(false);
            $table->decimal('home_value', 15, 2)->nullable();
            $table->decimal('nrb_transferred_from_spouse', 15, 2)->default(0);
            $table->decimal('charitable_giving_percent', 5, 2)->default(0);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iht_profiles');
    }
};
