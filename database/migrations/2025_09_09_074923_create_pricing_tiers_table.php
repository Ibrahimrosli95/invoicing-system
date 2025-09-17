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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_segment_id')->constrained()->cascadeOnDelete();
            $table->integer('min_quantity')->default(1); // Minimum quantity for this tier
            $table->integer('max_quantity')->nullable(); // Maximum quantity (null = unlimited)
            $table->decimal('unit_price', 10, 2); // Price per unit for this tier
            $table->decimal('discount_percentage', 5, 2)->nullable(); // Alternative: discount from base price
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // Order within the same item/segment
            $table->json('settings')->nullable(); // Tier-specific settings
            $table->text('notes')->nullable(); // Internal notes for this tier
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['pricing_item_id', 'customer_segment_id'], 'pt_item_segment_idx');
            $table->index(['pricing_item_id', 'customer_segment_id', 'min_quantity'], 'pt_item_segment_qty_idx');
            $table->index(['pricing_item_id', 'is_active'], 'pt_item_active_idx');
            
            // Ensure no overlapping quantity ranges within same item/segment
            $table->unique(['pricing_item_id', 'customer_segment_id', 'min_quantity'], 'pt_item_segment_minqty_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
