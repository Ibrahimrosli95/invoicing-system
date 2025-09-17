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
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); // Dealer, Contractor, End User, etc.
            $table->text('description')->nullable();
            $table->decimal('default_discount_percentage', 5, 2)->default(0); // Default discount for this segment
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Additional segment-specific settings
            $table->string('color', 7)->nullable(); // Hex color for UI display
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['company_id', 'is_active'], 'cs_company_active_idx');
            $table->index(['company_id', 'sort_order'], 'cs_company_sort_idx');
            $table->unique(['company_id', 'name'], 'cs_company_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segments');
    }
};
