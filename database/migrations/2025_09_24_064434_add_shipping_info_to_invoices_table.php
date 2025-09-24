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
            // Add shipping information for the enhanced invoice builder
            $table->json('shipping_info')->nullable()->after('optional_sections');

            // Add customer company field if not exists
            if (!Schema::hasColumn('invoices', 'customer_company')) {
                $table->string('customer_company')->nullable()->after('customer_postal_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['shipping_info']);

            // Only drop customer_company if we added it
            if (Schema::hasColumn('invoices', 'customer_company')) {
                $table->dropColumn('customer_company');
            }
        });
    }
};
