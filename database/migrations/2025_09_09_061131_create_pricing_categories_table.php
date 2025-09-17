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
        Schema::create('pricing_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('code', 20)->nullable(); // Optional category code
            $table->foreignId('parent_id')->nullable()->constrained('pricing_categories')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Category-specific settings
            $table->string('icon')->nullable(); // Icon class or image path
            $table->string('color', 7)->nullable(); // Hex color code
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'parent_id'], 'pc_company_parent_idx');
            $table->index(['company_id', 'is_active'], 'pc_company_active_idx');
            $table->index(['company_id', 'sort_order'], 'pc_company_sort_idx');
            $table->unique(['company_id', 'code'], 'pc_company_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_categories');
    }
};
