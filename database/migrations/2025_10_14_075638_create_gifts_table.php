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
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('gift_date');
            $table->string('recipient');
            $table->enum('gift_type', ['pet', 'clt', 'exempt', 'small_gift', 'annual_exemption']);
            $table->decimal('gift_value', 15, 2);
            $table->enum('status', ['within_7_years', 'survived_7_years'])->default('within_7_years');
            $table->boolean('taper_relief_applicable')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('gift_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gifts');
    }
};
