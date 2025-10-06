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
        Schema::table('service_template_items', function (Blueprint $table) {
            // Amount override - when set, overrides calculated amount (quantity × unit_price)
            $table->decimal('amount_override', 10, 2)->nullable()->after('minimum_price');

            // Track if amount was manually edited by user
            $table->boolean('amount_manually_edited')->default(false)->after('amount_override');

            // Index for queries filtering by manual overrides
            $table->index('amount_manually_edited', 'st_items_amount_edited_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_template_items', function (Blueprint $table) {
            // Drop index first
            $table->dropIndex('st_items_amount_edited_idx');

            // Drop columns in reverse order
            $table->dropColumn(['amount_manually_edited', 'amount_override']);
        });
    }
};
