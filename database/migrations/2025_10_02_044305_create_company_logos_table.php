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
        Schema::create('company_logos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name'); // User-friendly name (e.g., "Primary Logo", "Event Logo")
            $table->string('file_path'); // Storage path
            $table->boolean('is_default')->default(false); // Mark as default logo
            $table->text('notes')->nullable(); // Optional notes about when to use this logo
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_logos');
    }
};
