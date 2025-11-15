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
            // Managing agent details for BTL properties
            $table->string('managing_agent_name')->nullable()->after('tenant_email');
            $table->string('managing_agent_company')->nullable()->after('managing_agent_name');
            $table->string('managing_agent_email')->nullable()->after('managing_agent_company');
            $table->string('managing_agent_phone')->nullable()->after('managing_agent_email');
            $table->decimal('managing_agent_fee', 10, 2)->nullable()->after('managing_agent_phone')
                ->comment('Management fee amount or percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'managing_agent_name',
                'managing_agent_company',
                'managing_agent_email',
                'managing_agent_phone',
                'managing_agent_fee',
            ]);
        });
    }
};
