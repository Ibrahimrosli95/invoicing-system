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
            // Add customer relationship (nullable for backward compatibility)
            $table->foreignId('customer_id')->nullable()->after('lead_id')->constrained()->nullOnDelete();

            // Add optional sections configuration
            $table->json('optional_sections')->nullable()->after('notes');

            // Add index for customer lookups
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Drop foreign key constraint and column
            $table->dropConstrainedForeignId('customer_id');

            // Drop optional sections column
            $table->dropColumn('optional_sections');
        });
    }
};
