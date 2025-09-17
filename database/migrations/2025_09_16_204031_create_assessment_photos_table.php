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
        Schema::create('assessment_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained('assessment_items')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained('assessment_sections')->onDelete('cascade');
            
            // File Details
            $table->string('file_path');
            $table->string('file_name', 100);
            $table->string('original_name', 100);
            $table->integer('file_size');
            $table->string('mime_type', 50);
            
            // Photo Metadata
            $table->string('title', 100)->nullable();
            $table->text('description')->nullable();
            $table->enum('photo_type', ['before', 'during', 'after', 'issue', 'general', 'reference'])->default('general');
            
            // Technical Metadata
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('camera_make', 50)->nullable();
            $table->string('camera_model', 50)->nullable();
            $table->dateTime('taken_at')->nullable();
            
            // Geolocation (if available)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_address')->nullable();
            
            // Processing Status
            $table->boolean('is_processed')->default(false);
            $table->string('thumbnail_path')->nullable();
            $table->json('processing_metadata')->nullable();
            
            // Organization
            $table->integer('display_order')->default(0);
            $table->boolean('is_featured')->default(false); // Featured photo for section/item
            $table->boolean('include_in_report')->default(true);
            
            // Annotations
            $table->json('annotations')->nullable(); // For marking up photos with text, arrows, etc.
            $table->text('technical_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['assessment_id']);
            $table->index(['item_id']);
            $table->index(['section_id']);
            $table->index(['photo_type']);
            $table->index(['display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_photos');
    }
};
