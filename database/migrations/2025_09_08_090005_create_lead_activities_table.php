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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Lead and user
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Who performed the activity
            
            // Activity details
            $table->string('type'); // call, email, meeting, note, status_change, assignment, etc.
            $table->string('title'); // Brief title of the activity
            $table->text('description')->nullable(); // Detailed description
            $table->string('outcome')->nullable(); // successful, no_answer, callback_requested, etc.
            
            // Contact method (for calls/emails)
            $table->string('contact_method')->nullable(); // phone, email, in_person, etc.
            $table->string('duration')->nullable(); // Duration for calls/meetings
            
            // Follow-up scheduling
            $table->timestamp('follow_up_at')->nullable();
            $table->text('follow_up_notes')->nullable();
            
            // File attachments (for emails, documents, etc.)
            $table->json('attachments')->nullable(); // Array of file paths
            
            // Metadata for additional context
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'lead_id']);
            $table->index(['company_id', 'user_id']);
            $table->index(['type']);
            $table->index(['created_at']);
            $table->index(['follow_up_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
