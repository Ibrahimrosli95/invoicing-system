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
        Schema::table('leads', function (Blueprint $table) {
            // Track all sales reps who contacted this customer
            // JSON format: [{"user_id": 1, "user_name": "Rep A", "contacted_at": "2025-01-05", "quoted": 15000}]
            $table->json('contacted_by')->nullable()->after('metadata');

            // Count of how many times customer was quoted
            $table->integer('quote_count')->default(0)->after('contacted_by');

            // Last quote amount for price comparison
            $table->decimal('last_quote_amount', 12, 2)->nullable()->after('quote_count');

            // Flag if this lead needs manager review due to issues
            $table->boolean('flagged_for_review')->default(false)->after('last_quote_amount');

            // JSON to store review flags: {"type": "price_drop", "details": {...}}
            $table->json('review_flags')->nullable()->after('flagged_for_review');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'contacted_by',
                'quote_count',
                'last_quote_amount',
                'flagged_for_review',
                'review_flags',
            ]);
        });
    }
};
