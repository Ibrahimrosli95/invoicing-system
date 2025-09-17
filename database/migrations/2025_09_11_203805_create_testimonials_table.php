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
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Customer Information
            $table->string('customer_name');
            $table->string('customer_email')->nullable();
            $table->string('customer_company')->nullable();
            $table->string('customer_position')->nullable();
            $table->string('customer_phone')->nullable();
            
            // Testimonial Content
            $table->string('title');
            $table->text('content');
            $table->text('summary')->nullable(); // Short excerpt
            $table->integer('rating')->nullable(); // 1-5 star rating
            
            // Metadata
            $table->string('project_type')->nullable();
            $table->decimal('project_value', 15, 2)->nullable();
            $table->date('project_completion_date')->nullable();
            
            // Media & Assets
            $table->string('customer_photo')->nullable();
            $table->string('customer_signature')->nullable();
            $table->json('project_images')->nullable(); // Array of image paths
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'published', 'archived'])->default('pending');
            $table->enum('approval_status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            // Display & Usage Settings
            $table->boolean('allow_public_display')->default(false);
            $table->boolean('show_customer_name')->default(true);
            $table->boolean('show_customer_company')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);
            
            // Consent & Legal
            $table->boolean('consent_given')->default(false);
            $table->timestamp('consent_date')->nullable();
            $table->string('consent_method')->nullable(); // email, verbal, written
            $table->boolean('marketing_consent')->default(false);
            
            // Analytics
            $table->integer('view_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->json('usage_stats')->nullable(); // Track where it's used
            
            // Collection Method
            $table->enum('collection_method', ['manual', 'email_request', 'form_submission', 'imported'])->default('manual');
            $table->string('source_url')->nullable();
            $table->json('form_data')->nullable(); // Original form submission data
            
            // Relationship to other entities
            $table->foreignId('related_quotation_id')->nullable()->constrained('quotations');
            $table->foreignId('related_invoice_id')->nullable()->constrained('invoices');
            $table->foreignId('related_lead_id')->nullable()->constrained('leads');
            
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
            $table->index('customer_email');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
