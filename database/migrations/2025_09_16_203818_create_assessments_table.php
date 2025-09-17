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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('assessment_code', 20)->unique();
            
            // Multi-tenant and ownership
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            // Customer Information
            $table->string('client_name', 100);
            $table->string('company', 100)->nullable();
            $table->string('contact_email', 100);
            $table->string('contact_phone', 20)->nullable();
            
            // Property Details
            $table->text('property_address');
            $table->enum('property_type', ['office', 'industrial', 'retail', 'healthcare', 'educational', 'residential', 'mixed']);
            $table->string('property_size', 50)->nullable();
            $table->integer('property_age')->nullable();
            
            // Service Type and Assessment Configuration
            $table->enum('service_type', ['waterproofing', 'painting', 'sports_court', 'industrial']);
            $table->enum('assessment_type', ['initial', 'detailed', 'maintenance', 'warranty', 'compliance'])->default('initial');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Scheduling
            $table->date('requested_date')->nullable();
            $table->dateTime('scheduled_date')->nullable();
            $table->dateTime('completed_date')->nullable();
            
            // Status Management
            $table->enum('status', ['draft', 'scheduled', 'in_progress', 'completed', 'reported', 'quoted'])->default('draft');
            
            // Scoring and Risk Assessment
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            
            // Assessment Documentation
            $table->text('summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('internal_notes')->nullable();
            
            // Integration Points
            $table->foreignId('quotation_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('report_generated')->default(false);
            $table->string('report_path')->nullable();
            
            // Weather and Environmental Conditions
            $table->string('weather_conditions', 100)->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('humidity_percentage')->nullable();
            
            // Budget and Timeline
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->enum('budget_range', ['under_10k', '10k_25k', '25k_50k', '50k_100k', 'over_100k'])->nullable();
            $table->enum('timeline_urgency', ['immediate', 'within_month', 'within_quarter', 'flexible'])->default('flexible');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['service_type', 'status']);
            $table->index(['scheduled_date']);
            $table->index(['assessment_code']);
            $table->index(['risk_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
