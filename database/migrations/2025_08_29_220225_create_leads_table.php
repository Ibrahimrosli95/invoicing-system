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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Lead assignment
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            // Customer information
            $table->string('name');
            $table->string('phone')->index(); // For duplicate detection
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            
            // Lead details
            $table->string('source')->nullable(); // referral, website, social_media, etc.
            $table->string('status')->default('NEW'); // NEW, CONTACTED, QUOTED, WON, LOST
            $table->text('requirements')->nullable(); // What they need
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('urgency')->default('medium'); // low, medium, high
            
            // Lead qualification
            $table->boolean('is_qualified')->default(false);
            $table->integer('lead_score')->default(0); // 0-100
            $table->json('tags')->nullable(); // Flexible tagging system
            
            // Follow-up tracking
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up_at')->nullable();
            $table->integer('contact_attempts')->default(0);
            
            // Conversion tracking
            $table->timestamp('converted_at')->nullable();
            $table->string('lost_reason')->nullable();
            $table->text('lost_notes')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable(); // Additional flexible data
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'team_id']);
            $table->index(['company_id', 'assigned_to']);
            $table->index(['company_id', 'phone']); // Duplicate detection
            $table->index(['next_follow_up_at']);
            $table->index(['created_at']);
            $table->index(['lead_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
