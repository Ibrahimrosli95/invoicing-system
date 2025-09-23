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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('customer_segment_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'customer_segment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['customer_segment_id']);
            $table->dropIndex(['company_id', 'customer_segment_id']);
            $table->dropColumn('customer_segment_id');
        });
    }
};
