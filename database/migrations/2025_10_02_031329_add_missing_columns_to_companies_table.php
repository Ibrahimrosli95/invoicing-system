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
        Schema::table('companies', function (Blueprint $table) {
            // Add tagline column
            $table->string('tagline')->nullable()->after('name');

            // Add separate color columns (migrate from brand_colors JSON)
            $table->string('primary_color')->default('#2563eb')->after('logo_path');
            $table->string('secondary_color')->default('#10b981')->after('primary_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['tagline', 'primary_color', 'secondary_color']);
        });
    }
};
