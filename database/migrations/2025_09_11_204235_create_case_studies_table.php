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
        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Project Information
            $table->string('title');
            $table->string('client_name');
            $table->string('client_company')->nullable();
            $table->string('client_industry')->nullable();
            $table->string('project_location')->nullable();
            $table->string('project_type'); // Renovation, Construction, etc.
            
            // Project Details
            $table->text('project_overview');
            $table->text('challenge_description'); // What problems were solved
            $table->text('solution_description'); // How it was solved
            $table->text('results_achieved'); // Outcomes and benefits
            $table->decimal('project_value', 15, 2)->nullable();
            
            // Timeline & Scope
            $table->date('project_start_date')->nullable();
            $table->date('project_completion_date')->nullable();
            $table->integer('project_duration_days')->nullable();
            $table->text('project_scope')->nullable();
            $table->json('services_provided')->nullable(); // Array of services
            
            // Before & After Documentation
            $table->json('before_images')->nullable(); // Array of image paths
            $table->json('after_images')->nullable(); // Array of image paths
            $table->json('process_images')->nullable(); // Work-in-progress images
            $table->string('hero_image')->nullable(); // Main featured image
            
            // Client Information & Consent
            $table->string('client_contact_person')->nullable();
            $table->string('client_position')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            $table->boolean('client_consent_given')->default(false);
            $table->timestamp('consent_date')->nullable();
            $table->string('consent_method')->nullable(); // email, written, verbal
            
            // Technical Details
            $table->json('technical_specifications')->nullable();
            $table->json('materials_used')->nullable(); // List of materials/products
            $table->json('equipment_used')->nullable(); // Tools and equipment
            $table->integer('team_size')->nullable();
            $table->json('team_roles')->nullable(); // Team members and their roles
            
            // Metrics & Results
            $table->json('key_metrics')->nullable(); // Quantifiable results
            $table->decimal('cost_savings_achieved', 15, 2)->nullable();
            $table->integer('completion_ahead_days')->nullable(); // If completed early
            $table->decimal('client_satisfaction_score', 3, 2)->nullable(); // Out of 5.00
            $table->boolean('repeat_client')->default(false);
            
            // Status & Publication
            $table->enum('status', ['draft', 'review', 'approved', 'published', 'archived'])->default('draft');
            $table->enum('approval_status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            // Display & Marketing
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_client_name')->default(true);
            $table->boolean('show_project_value')->default(false);
            $table->boolean('allow_public_display')->default(false);
            $table->integer('display_order')->default(0);
            
            // SEO & Marketing
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('keywords')->nullable(); // SEO keywords
            $table->string('case_study_slug')->nullable(); // URL-friendly identifier
            
            // Awards & Recognition
            $table->json('awards_received')->nullable(); // Any awards for this project
            $table->text('media_coverage')->nullable(); // Press mentions
            $table->json('certifications_demonstrated')->nullable(); // Which company certs this showcases
            
            // Analytics & Performance
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('share_count')->default(0);
            $table->json('usage_stats')->nullable(); // Where it's been used
            
            // Relationship to other entities
            $table->foreignId('related_quotation_id')->nullable()->constrained('quotations');
            $table->foreignId('related_invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('related_lead_id')->nullable()->constrained('leads');
            
            // Document Management
            $table->string('pdf_version')->nullable(); // Generated PDF case study
            $table->json('additional_documents')->nullable(); // Supporting docs
            
            // User tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'approval_status']);
            $table->index(['company_id', 'is_featured']);
            $table->index(['company_id', 'allow_public_display']);
            $table->index('client_industry');
            $table->index('project_type');
            $table->index('case_study_slug');
            $table->index('project_completion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_studies');
    }
};
