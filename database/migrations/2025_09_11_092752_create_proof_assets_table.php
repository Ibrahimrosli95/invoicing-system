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
        Schema::create('proof_assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('proof_id');
            $table->unsignedBigInteger('company_id');
            
            // File Information
            $table->string('filename');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes
            
            // Asset Type & Purpose
            $table->enum('type', [
                'image',         // Photos, screenshots
                'video',         // Video testimonials, project videos
                'document',      // PDFs, certificates, contracts
                'audio',         // Audio testimonials
                'other'          // Other file types
            ]);
            
            // Image-specific fields
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('alt_text')->nullable();
            
            // Video-specific fields
            $table->integer('duration')->nullable(); // in seconds
            $table->string('thumbnail_path')->nullable();
            
            // Display & Organization
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_primary')->default(false); // Primary asset for the proof
            $table->boolean('is_public')->default(true);
            
            // Processing Status
            $table->enum('processing_status', [
                'pending',       // Uploaded, processing not started
                'processing',    // Currently being processed
                'completed',     // Processing completed successfully
                'failed',        // Processing failed
                'optimizing'     // Image optimization in progress
            ])->default('completed');
            
            // Analytics
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            
            // User tracking
            $table->unsignedBigInteger('uploaded_by');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['proof_id', 'sort_order']);
            $table->index(['company_id', 'type']);
            $table->index(['proof_id', 'is_primary']);
            $table->index('processing_status');
            
            // Foreign Keys
            $table->foreign('proof_id')->references('id')->on('proofs')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_assets');
    }
};
