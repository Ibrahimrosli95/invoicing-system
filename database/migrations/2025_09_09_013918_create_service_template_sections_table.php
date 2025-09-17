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
        Schema::create('service_template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_template_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->decimal('default_discount_percentage', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true); // Must be included in quotation
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Section-specific settings
            $table->decimal('estimated_hours', 8, 2)->nullable(); // Estimated hours for this section
            $table->text('instructions')->nullable(); // Instructions for sales team
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['service_template_id', 'sort_order'], 'st_sections_template_sort_idx');
            $table->index(['service_template_id', 'is_active'], 'st_sections_template_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_template_sections');
    }
};
