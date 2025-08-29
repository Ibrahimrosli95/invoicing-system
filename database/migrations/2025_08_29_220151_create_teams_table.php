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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Team information
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            
            // Team management
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Team settings
            $table->string('color')->default('#2563EB'); // For UI identification
            $table->json('territories')->nullable(); // Geographical territories
            $table->json('settings')->nullable(); // Team-specific settings
            
            // Performance tracking
            $table->integer('target_leads_monthly')->default(0);
            $table->integer('target_quotations_monthly')->default(0);
            $table->decimal('target_revenue_monthly', 15, 2)->default(0);
            
            // Status
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'slug']);
            $table->index(['manager_id']);
            $table->index(['coordinator_id']);
            
            // Unique constraint
            $table->unique(['company_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
