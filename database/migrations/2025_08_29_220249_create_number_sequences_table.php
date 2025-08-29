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
        Schema::create('number_sequences', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Sequence configuration
            $table->string('type'); // quotation, invoice, etc.
            $table->string('prefix'); // QTN, INV, etc.
            $table->integer('current_number')->default(0);
            $table->integer('year')->default(0); // For yearly reset
            $table->integer('padding')->default(6); // Number of digits (000001)
            
            // Format configuration
            $table->string('format')->default('{prefix}-{year}-{number}'); // Template format
            $table->boolean('yearly_reset')->default(true);
            
            // Metadata
            $table->timestamp('last_generated_at')->nullable();
            $table->string('last_generated_number')->nullable();
            
            $table->timestamps();
            
            // Indexes and constraints
            $table->unique(['company_id', 'type', 'year']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('number_sequences');
    }
};
