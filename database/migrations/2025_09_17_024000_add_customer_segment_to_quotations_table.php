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
        Schema::table('quotations', function (Blueprint $table) {
            $table->foreignId('customer_segment_id')
                  ->nullable()
                  ->after('lead_id')
                  ->constrained()
                  ->nullOnDelete();
            
            $table->index(['customer_segment_id'], 'quotations_segment_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['customer_segment_id']);
            $table->dropIndex('quotations_segment_idx');
            $table->dropColumn('customer_segment_id');
        });
    }
};
