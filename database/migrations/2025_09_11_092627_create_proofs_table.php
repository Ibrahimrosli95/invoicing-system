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
        Schema::create('proofs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('company_id');
            
            // Polymorphic relationship (can attach to quotations, invoices, leads, etc.)
            $table->morphs('scope');
            
            // Proof Type
            $table->enum('type', [
                'visual_proof',      // Before/after photos, project images
                'social_proof',      // Testimonials, reviews, references
                'professional_proof', // Certifications, awards, memberships
                'performance_proof', // Statistics, metrics, case studies
                'trust_proof'       // Insurance, guarantees, compliance
            ]);
            
            // Content
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Flexible metadata storage
            
            // Display Settings
            $table->enum('visibility', ['public', 'private', 'restricted'])->default('public');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_in_pdf')->default(true);
            $table->boolean('show_in_quotation')->default(true);
            $table->boolean('show_in_invoice')->default(false);
            
            // Status & Publishing
            $table->enum('status', ['draft', 'active', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Analytics
            $table->integer('view_count')->default(0);
            $table->integer('click_count')->default(0);
            $table->decimal('conversion_impact', 5, 2)->default(0); // Percentage impact
            
            // User tracking
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'type']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_featured']);
            $table->index('sort_order');
            
            // Foreign Keys
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proofs');
    }
};
