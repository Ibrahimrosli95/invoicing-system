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
            // Add customer_id column after lead_id for logical grouping
            // Nullable because existing invoices won't have a customer record yet
            $table->foreignId('customer_id')
                ->nullable()
                ->after('lead_id')
                ->constrained()
                ->nullOnDelete();

            // Add index for customer lookup queries
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop the composite index first
            $table->dropIndex(['customer_id', 'status']);

            // Drop the foreign key constraint and column
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
    }
};
