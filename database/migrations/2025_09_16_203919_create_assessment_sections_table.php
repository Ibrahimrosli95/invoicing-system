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
        Schema::create('assessment_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->onDelete('cascade');
            
            // Section Configuration
            $table->string('section_name', 100);
            $table->text('section_description')->nullable();
            $table->integer('section_order')->default(0);
            
            // Scoring Configuration
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->decimal('actual_score', 5, 2)->nullable();
            $table->decimal('weight', 3, 2)->default(1.00); // Weighting factor for overall score
            
            // Section Status and Notes
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            
            // Critical Flags
            $table->boolean('is_critical')->default(false); // Critical section that affects overall assessment
            $table->boolean('requires_photo')->default(false); // Section requires photo documentation
            
            $table->timestamps();
            
            // Indexes
            $table->index(['assessment_id', 'section_order']);
            $table->index(['assessment_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_sections');
    }
};
