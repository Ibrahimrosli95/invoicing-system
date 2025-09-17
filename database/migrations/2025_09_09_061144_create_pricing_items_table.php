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
        Schema::create('pricing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pricing_category_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('item_code', 50)->nullable(); // SKU or item code
            $table->string('unit', 20)->default('Nos'); // Unit of measurement
            $table->decimal('unit_price', 10, 2); // Selling price
            $table->decimal('cost_price', 10, 2)->nullable(); // Cost for margin calculations
            $table->decimal('minimum_price', 10, 2)->nullable(); // Minimum allowed selling price
            $table->text('specifications')->nullable(); // Technical specifications
            $table->json('tags')->nullable(); // Searchable tags
            $table->string('image_path')->nullable(); // Product image
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Featured items
            $table->integer('sort_order')->default(0);
            $table->json('settings')->nullable(); // Item-specific settings
            $table->integer('stock_quantity')->nullable(); // Optional stock tracking
            $table->boolean('track_stock')->default(false);
            $table->decimal('markup_percentage', 5, 2)->nullable(); // Auto-calculated markup
            $table->timestamp('last_price_update')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['company_id', 'pricing_category_id'], 'pi_company_category_idx');
            $table->index(['company_id', 'is_active'], 'pi_company_active_idx');
            $table->index(['company_id', 'is_featured'], 'pi_company_featured_idx');
            $table->index('item_code', 'pi_item_code_idx');
            $table->index(['name', 'company_id'], 'pi_name_company_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_items');
    }
};
