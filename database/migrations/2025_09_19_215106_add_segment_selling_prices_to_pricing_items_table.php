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
            // Add JSON column to store selling prices per customer segment
            // Format: {"1": "150.00", "2": "140.00", "3": "125.00"}
            // Key = customer_segment_id, Value = selling_price
            $table->json('segment_selling_prices')->nullable()->after('unit_price');

            // Add target margin percentage per segment for recommendations
            $table->json('segment_target_margins')->nullable()->after('segment_selling_prices');

            // Add last price update tracking
            $table->timestamp('segment_prices_updated_at')->nullable()->after('last_price_update');

            // Add flag to enable/disable segment pricing for this item
            $table->boolean('use_segment_pricing')->default(false)->after('segment_prices_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_items', function (Blueprint $table) {
            $table->dropColumn([
                'segment_selling_prices',
                'segment_target_margins',
                'segment_prices_updated_at',
                'use_segment_pricing'
            ]);
        });
    }
};
