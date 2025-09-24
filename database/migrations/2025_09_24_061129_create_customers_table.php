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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Multi-tenancy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Lead relationship (for converted customers)
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();

            // Customer information
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            // Business classification
            $table->foreignId('customer_segment_id')->nullable()->constrained()->nullOnDelete();

            // Status tracking
            $table->boolean('is_new_customer')->default(true);
            $table->boolean('is_active')->default(true);

            // Additional metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // For custom fields

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'phone']);
            $table->index(['company_id', 'email']);
            $table->index(['lead_id']); // For lead conversion tracking
            $table->index(['customer_segment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
