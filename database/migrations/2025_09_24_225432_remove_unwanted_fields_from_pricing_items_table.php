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
        Schema::table('pricing_items', function (Blueprint $table) {
            // Remove unwanted fields to simplify pricing table
            $table->dropColumn([
                'unit',              // Remove unit field completely
                'specifications',    // Remove technical specifications
                'tags',             // Remove tags functionality
                'stock_quantity',   // Remove stock tracking
                'track_stock',      // Remove stock tracking flag
                'is_featured'       // Remove featured items functionality
            ]);

            // Remove index related to is_featured field
            $table->dropIndex('pi_company_featured_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_items', function (Blueprint $table) {
            // Restore removed fields
            $table->string('unit', 20)->default('Nos'); // Unit of measurement
            $table->text('specifications')->nullable(); // Technical specifications
            $table->json('tags')->nullable(); // Searchable tags
            $table->integer('stock_quantity')->nullable(); // Optional stock tracking
            $table->boolean('track_stock')->default(false);
            $table->boolean('is_featured')->default(false); // Featured items

            // Restore index
            $table->index(['company_id', 'is_featured'], 'pi_company_featured_idx');
        });
    }
};
