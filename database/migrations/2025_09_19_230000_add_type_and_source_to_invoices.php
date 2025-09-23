<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'type')) {
                $table->string('type')->default('product')->after('status');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'source_type')) {
                $table->string('source_type')->nullable()->after('invoice_id');
            }

            if (!Schema::hasColumn('invoice_items', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            }

            if (!Schema::hasColumn('invoice_items', 'item_code')) {
                $table->string('item_code', 100)->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'item_code')) {
                $table->dropColumn('item_code');
            }

            if (Schema::hasColumn('invoice_items', 'source_id')) {
                $table->dropColumn('source_id');
            }

            if (Schema::hasColumn('invoice_items', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};
