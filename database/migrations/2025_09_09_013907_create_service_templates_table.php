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
        Schema::create('service_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->json('applicable_teams')->nullable(); // Which teams can use this template
            $table->json('settings')->nullable(); // Template configuration settings
            $table->decimal('estimated_hours', 8, 2)->nullable(); // Estimated project hours
            $table->decimal('base_price', 12, 2)->nullable(); // Base template price
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approval')->default(false); // Manager approval required
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_used_at')->nullable();
            $table->unsignedInteger('usage_count')->default(0); // Track template usage
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_templates');
    }
};
