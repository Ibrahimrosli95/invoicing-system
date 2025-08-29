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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // User who performed the action
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name')->nullable(); // Keep user name even if user is deleted
            
            // Auditable resource (polymorphic)
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->index(['auditable_type', 'auditable_id']);
            
            // Action details
            $table->string('event'); // created, updated, deleted, restored
            $table->string('action')->nullable(); // More specific action like 'sent_quotation', 'approved_invoice'
            
            // Change tracking
            $table->json('old_values')->nullable(); // Previous state
            $table->json('new_values')->nullable(); // New state
            $table->json('metadata')->nullable(); // Additional context
            
            // Request context
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable(); // GET, POST, etc.
            
            // Session and context
            $table->string('session_id')->nullable();
            $table->string('batch_id')->nullable(); // For grouping related changes
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event']);
            $table->index(['batch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
