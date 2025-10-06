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
        Schema::table('service_templates', function (Blueprint $table) {
            // Add the category_id column
            $table->foreignId('category_id')->nullable()->after('description')->constrained('service_categories')->nullOnDelete();
        });

        // Note: No data migration needed as this is a new feature
        // If there was existing data, we would migrate category strings to category_id here

        // Drop the old category column
        Schema::table('service_templates', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old category column
        Schema::table('service_templates', function (Blueprint $table) {
            $table->string('category', 50)->nullable()->after('description');
        });

        // Drop the category_id foreign key and column
        Schema::table('service_templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
