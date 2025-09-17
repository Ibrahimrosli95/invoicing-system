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
        Schema::create('service_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_template_section_id')->constrained()->cascadeOnDelete();
            $table->string('description', 500);
            $table->string('unit', 20)->default('Nos');
            $table->decimal('default_quantity', 8, 2)->default(1);
            $table->decimal('default_unit_price', 10, 2)->default(0);
            $table->string('item_code', 50)->nullable();
            $table->text('specifications')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true); // Must be included in quotation
            $table->boolean('quantity_editable')->default(true); // Sales rep can change quantity
            $table->boolean('price_editable')->default(true); // Sales rep can change price
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable(); // Item-specific settings
            $table->decimal('cost_price', 10, 2)->nullable(); // Cost for margin calculations
            $table->decimal('minimum_price', 10, 2)->nullable(); // Minimum allowed selling price
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['service_template_section_id', 'sort_order'], 'st_items_section_sort_idx');
            $table->index(['service_template_section_id', 'is_active'], 'st_items_section_active_idx');
            $table->index('item_code', 'st_items_code_idx'); // For item code searches
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_template_items');
    }
};
