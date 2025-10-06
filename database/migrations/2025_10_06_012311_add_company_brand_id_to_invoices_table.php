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
            $table->foreignId('company_brand_id')
                ->nullable()
                ->after('company_id')
                ->constrained('company_brands')
                ->nullOnDelete();

            $table->index('company_brand_id', 'idx_invoices_brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['company_brand_id']);
            $table->dropIndex('idx_invoices_brand');
            $table->dropColumn('company_brand_id');
        });
    }
};
