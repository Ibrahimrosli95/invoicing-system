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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            
            // Certification Information
            $table->string('title');
            $table->string('certification_body'); // ISO, OHSAS, etc.
            $table->string('certificate_number')->nullable();
            $table->text('description')->nullable();
            $table->string('certification_type'); // ISO 9001, OHSAS 18001, etc.
            $table->string('scope')->nullable(); // What areas/processes it covers
            
            // Validity & Expiration
            $table->date('issued_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('does_expire')->default(true);
            $table->integer('validity_years')->nullable();
            $table->boolean('auto_renewal')->default(false);
            
            // Status & Verification
            $table->enum('status', ['active', 'expired', 'revoked', 'suspended', 'pending_renewal'])->default('active');
            $table->enum('verification_status', ['verified', 'pending_verification', 'unverified'])->default('pending_verification');
            $table->text('verification_notes')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            
            // Certificate Files & Documentation
            $table->string('certificate_file')->nullable(); // PDF/Image of certificate
            $table->string('accreditation_logo')->nullable(); // Logo from certifying body
            $table->json('supporting_documents')->nullable(); // Array of document paths
            
            // Display & Presentation
            $table->boolean('show_on_documents')->default(true);
            $table->boolean('show_on_website')->default(true);
            $table->boolean('show_expiry_date')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('display_order')->default(0);
            
            // Certificate Details
            $table->string('issuing_authority')->nullable();
            $table->string('assessor_name')->nullable();
            $table->string('assessor_number')->nullable();
            $table->json('certificate_details')->nullable(); // Additional metadata
            
            // Renewal & Maintenance
            $table->date('next_assessment_date')->nullable();
            $table->date('next_surveillance_date')->nullable();
            $table->integer('renewal_reminder_days')->default(90);
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            
            // Business Impact
            $table->decimal('certification_cost', 10, 2)->nullable();
            $table->text('business_benefits')->nullable();
            $table->json('compliance_requirements')->nullable(); // What it helps comply with
            
            // Analytics & Usage
            $table->integer('view_count')->default(0);
            $table->integer('document_usage_count')->default(0);
            $table->json('usage_stats')->nullable(); // Track where it's displayed
            
            // Relationship to business entities
            $table->json('applicable_services')->nullable(); // Which services this cert applies to
            $table->json('applicable_projects')->nullable(); // Project types this cert covers
            
            // Audit & Tracking
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'verification_status']);
            $table->index(['company_id', 'is_featured']);
            $table->index(['company_id', 'show_on_documents']);
            $table->index('expiry_date');
            $table->index('certification_type');
            $table->index('certificate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
