<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds source tracking fields to quotation_items table to track where items originated from
     * (pricing_item, service_template_item, or manual entry).
     */
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // Source tracking fields
            if (!Schema::hasColumn('quotation_items', 'source_type')) {
                $table->string('source_type')->nullable()->after('quotation_section_id')
                    ->comment('Type of source: pricing_item, service_template_item, manual');
            }

            if (!Schema::hasColumn('quotation_items', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type')
                    ->comment('ID of the source record (pricing_items.id or service_template_items.id)');
            }

            // Add index for performance
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            // Drop index
            $table->dropIndex(['source_type', 'source_id']);

            // Drop columns
            if (Schema::hasColumn('quotation_items', 'source_id')) {
                $table->dropColumn('source_id');
            }

            if (Schema::hasColumn('quotation_items', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};
