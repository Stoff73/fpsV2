<?php

declare(strict_types=1);

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
        Schema::create('document_extractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');

            // Extraction metadata
            $table->integer('extraction_version')->default(1); // For re-extraction
            $table->string('model_used')->default('claude-3-5-sonnet');
            $table->integer('input_tokens')->nullable();
            $table->integer('output_tokens')->nullable();

            // Raw and structured data
            $table->longText('raw_response'); // Full Claude API response
            $table->json('extracted_fields'); // Structured field data
            $table->json('field_confidence'); // Confidence per field
            $table->json('warnings')->nullable(); // Any extraction warnings

            // Target model info
            $table->string('target_model')->nullable(); // e.g., 'App\Models\DCPension'
            $table->unsignedBigInteger('target_model_id')->nullable(); // After confirmation

            // Validation
            $table->boolean('is_valid')->default(false);
            $table->json('validation_errors')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['document_id', 'extraction_version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_extractions');
    }
};
