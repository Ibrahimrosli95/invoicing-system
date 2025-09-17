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
        Schema::create('service_assessment_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Template Configuration
            $table->enum('service_type', ['waterproofing', 'painting', 'sports_court', 'industrial']);
            $table->string('template_name', 100);
            $table->text('template_description')->nullable();
            $table->string('template_version', 10)->default('1.0');
            
            // Template Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Default template for service type
            $table->boolean('requires_approval')->default(false);
            
            // Template Configuration
            $table->json('sections_config')->nullable(); // Predefined sections and items structure
            $table->enum('scoring_method', ['weighted', 'simple', 'critical_points'])->default('weighted');
            $table->decimal('passing_score', 5, 2)->default(70.00);
            
            // Risk Calculation Rules
            $table->json('risk_thresholds')->nullable(); // Score ranges for risk levels
            $table->json('critical_items')->nullable(); // Items that are considered critical
            
            // Usage Tracking
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            
            // Approval Workflow
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Versioning
            $table->foreignId('parent_template_id')->nullable()->constrained('service_assessment_templates')->onDelete('set null');
            $table->text('change_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes and Constraints
            $table->index(['company_id', 'service_type']);
            $table->index(['service_type', 'is_active']);
            $table->index(['is_default']);
            $table->unique(['company_id', 'service_type', 'template_name'], 'unique_company_service_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_assessment_templates');
    }
};
