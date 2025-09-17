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
        Schema::create('assessment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('assessment_sections')->onDelete('cascade');
            
            // Item Configuration
            $table->string('item_name', 150);
            $table->text('item_description')->nullable();
            $table->integer('item_order')->default(0);
            
            // Response Type Configuration
            $table->enum('item_type', ['rating', 'yes_no', 'text', 'measurement', 'photo', 'multiple_choice']);
            $table->json('item_options')->nullable(); // For multiple choice options, rating scales, etc.
            
            // Scoring Configuration
            $table->decimal('max_points', 4, 2)->default(10.00);
            $table->decimal('actual_points', 4, 2)->nullable();
            
            // Response Data
            $table->text('response_value')->nullable(); // Flexible storage for different response types
            $table->text('response_notes')->nullable();
            $table->text('recommendations')->nullable();
            
            // Risk Assessment
            $table->enum('risk_factor', ['none', 'low', 'medium', 'high', 'critical'])->default('none');
            $table->boolean('is_critical')->default(false); // Critical item that affects overall assessment
            $table->boolean('requires_immediate_attention')->default(false);
            
            // Photo Documentation
            $table->boolean('photo_required')->default(false);
            $table->integer('photos_count')->default(0);
            $table->integer('min_photos')->default(0);
            $table->integer('max_photos')->default(5);
            
            // Measurements and Technical Data
            $table->string('measurement_unit', 20)->nullable(); // cm, mm, %, degrees, etc.
            $table->decimal('measurement_value', 10, 3)->nullable();
            $table->decimal('acceptable_min', 10, 3)->nullable();
            $table->decimal('acceptable_max', 10, 3)->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['section_id', 'item_order']);
            $table->index(['section_id', 'is_critical']);
            $table->index(['risk_factor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_items');
    }
};
