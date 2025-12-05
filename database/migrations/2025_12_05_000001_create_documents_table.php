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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // File storage info
            $table->string('original_filename');
            $table->string('stored_filename'); // UUID-based for security
            $table->string('disk')->default('local'); // local or s3
            $table->string('path'); // Full path within disk
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size'); // bytes

            // Document classification
            $table->enum('document_type', [
                'pension_statement',
                'insurance_policy',
                'investment_statement',
                'mortgage_statement',
                'savings_statement',
                'property_document',
                'unknown',
            ])->default('unknown');
            $table->string('detected_document_subtype')->nullable(); // e.g., 'dc_pension', 'life_insurance'
            $table->decimal('detection_confidence', 5, 4)->nullable(); // 0.0000 to 1.0000

            // Processing status
            $table->enum('status', [
                'uploaded',
                'processing',
                'extracted',
                'review_pending',
                'confirmed',
                'failed',
                'archived',
            ])->default('uploaded');
            $table->text('error_message')->nullable();

            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'document_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
