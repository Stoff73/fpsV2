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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('onboarding_completed')->default(false)->after('spouse_id');
            $table->enum('onboarding_focus_area', ['estate', 'protection', 'retirement', 'investment', 'tax_optimisation'])->nullable()->after('onboarding_completed');
            $table->string('onboarding_current_step')->nullable()->after('onboarding_focus_area');
            $table->json('onboarding_skipped_steps')->nullable()->after('onboarding_current_step');
            $table->timestamp('onboarding_started_at')->nullable()->after('onboarding_skipped_steps');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_completed',
                'onboarding_focus_area',
                'onboarding_current_step',
                'onboarding_skipped_steps',
                'onboarding_started_at',
                'onboarding_completed_at',
            ]);
        });
    }
};
