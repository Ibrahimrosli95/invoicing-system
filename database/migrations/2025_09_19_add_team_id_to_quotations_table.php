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
            $table->foreignId('team_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('team_id')->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();

            // Add indexes for performance
            $table->index(['company_id', 'team_id']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['created_by']);
            $table->dropColumn(['team_id', 'assigned_to', 'created_by']);
        });
    }
};