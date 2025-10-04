<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds fields required for the quotation builder interface to match invoice functionality.
     */
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Document type (product vs service quotations)
            if (!Schema::hasColumn('quotations', 'type')) {
                $table->string('type')->default('product')->after('number');
            }

            // Quotation dates
            if (!Schema::hasColumn('quotations', 'quotation_date')) {
                $table->date('quotation_date')->nullable()->after('type');
            }

            // Reference number for customer PO tracking
            if (!Schema::hasColumn('quotations', 'reference_number')) {
                $table->string('reference_number', 100)->nullable()->after('valid_until');
            }

            // Extended customer information
            if (!Schema::hasColumn('quotations', 'customer_address')) {
                $table->text('customer_address')->nullable()->after('customer_phone');
            }

            if (!Schema::hasColumn('quotations', 'customer_city')) {
                $table->string('customer_city', 100)->nullable()->after('customer_address');
            }

            if (!Schema::hasColumn('quotations', 'customer_state')) {
                $table->string('customer_state', 100)->nullable()->after('customer_city');
            }

            if (!Schema::hasColumn('quotations', 'customer_postal_code')) {
                $table->string('customer_postal_code', 20)->nullable()->after('customer_state');
            }

            if (!Schema::hasColumn('quotations', 'customer_company')) {
                $table->string('customer_company', 150)->nullable()->after('customer_postal_code');
            }

            // Quotation content
            if (!Schema::hasColumn('quotations', 'title')) {
                $table->string('title', 200)->nullable()->after('customer_company');
            }

            if (!Schema::hasColumn('quotations', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (!Schema::hasColumn('quotations', 'terms_conditions')) {
                $table->text('terms_conditions')->nullable()->after('description');
            }

            if (!Schema::hasColumn('quotations', 'notes')) {
                $table->text('notes')->nullable()->after('terms_conditions');
            }

            // Financial calculations (tax and discount details)
            if (!Schema::hasColumn('quotations', 'tax_percentage')) {
                $table->decimal('tax_percentage', 5, 2)->default(0)->after('total');
            }

            if (!Schema::hasColumn('quotations', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0)->after('tax_percentage');
            }

            if (!Schema::hasColumn('quotations', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('discount_percentage');
            }

            if (!Schema::hasColumn('quotations', 'tax_amount')) {
                $table->decimal('tax_amount', 10, 2)->default(0)->after('discount_amount');
            }

            // Add indexes for performance
            $table->index(['type', 'status']);
            $table->index(['quotation_date']);
            $table->index(['reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['quotation_date']);
            $table->dropIndex(['reference_number']);

            // Drop columns (in reverse order)
            $columns = [
                'tax_amount',
                'discount_amount',
                'discount_percentage',
                'tax_percentage',
                'notes',
                'terms_conditions',
                'description',
                'title',
                'customer_company',
                'customer_postal_code',
                'customer_state',
                'customer_city',
                'customer_address',
                'reference_number',
                'quotation_date',
                'type',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('quotations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
