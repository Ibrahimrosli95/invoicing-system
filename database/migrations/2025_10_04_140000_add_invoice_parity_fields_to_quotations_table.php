<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add fields to quotations table to match invoice functionality:
     * - company_logo_id: Logo selection for branded quotations
     * - payment_instructions: Payment terms and bank details
     * - shipping_info: Shipping address and preferences (JSON)
     * - amount_due: Amount due (initially equal to total)
     * - optional_sections: Optional sections visibility (JSON)
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Company logo reference (after company_id for logical grouping)
            if (!Schema::hasColumn('quotations', 'company_logo_id')) {
                $table->foreignId('company_logo_id')
                    ->nullable()
                    ->after('company_id')
                    ->comment('Optional company logo for branding');
            }

            // Payment instructions (after notes)
            if (!Schema::hasColumn('quotations', 'payment_instructions')) {
                $table->text('payment_instructions')
                    ->nullable()
                    ->after('notes')
                    ->comment('Payment terms and bank account details');
            }

            // Optional sections configuration (JSON field after payment_instructions)
            if (!Schema::hasColumn('quotations', 'optional_sections')) {
                $table->json('optional_sections')
                    ->nullable()
                    ->after('payment_instructions')
                    ->comment('JSON configuration for optional sections visibility');
            }

            // Shipping information (JSON field after optional_sections)
            if (!Schema::hasColumn('quotations', 'shipping_info')) {
                $table->json('shipping_info')
                    ->nullable()
                    ->after('optional_sections')
                    ->comment('Shipping address and delivery preferences (JSON)');
            }

            // Note: amount_due is NOT added to quotations
            // Quotations are proposals only, they don't track payments
            // Only invoices need amount_due for payment tracking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $columns = [
                'amount_due',
                'shipping_info',
                'optional_sections',
                'payment_instructions',
                'company_logo_id',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('quotations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
