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
        Schema::create('company_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Brand Identity
            $table->string('name'); // Trading name: "Bina Waterproofing Services"
            $table->string('legal_name')->nullable(); // Legal entity name if different
            $table->string('registration_number', 100)->nullable(); // SSM/Business registration
            $table->string('logo_path')->nullable(); // Path to brand logo

            // Contact Details
            $table->text('address');
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20);
            $table->string('phone', 50);
            $table->string('email');
            $table->string('website')->nullable();

            // Bank Details (optional - can inherit from company or have own)
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number', 100)->nullable();

            // Settings
            $table->boolean('is_default')->default(false); // Default brand for new documents
            $table->boolean('is_active')->default(true); // Active/inactive toggle
            $table->text('tagline')->nullable(); // Brand tagline/slogan
            $table->string('color_primary', 7)->nullable(); // Primary brand color (hex)
            $table->string('color_secondary', 7)->nullable(); // Secondary brand color (hex)

            // Metadata
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'is_active'], 'idx_company_brands_company_active');
            $table->index('is_default', 'idx_company_brands_default');
            $table->index('name', 'idx_company_brands_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_brands');
    }
};
