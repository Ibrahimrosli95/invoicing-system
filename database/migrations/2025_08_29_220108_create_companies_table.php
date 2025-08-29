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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            
            // Company basic information
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // Contact information
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            
            // Address information
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Malaysia');
            
            // Business information
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('business_type')->nullable();
            
            // Branding
            $table->string('logo_path')->nullable();
            $table->json('brand_colors')->nullable(); // Store primary, secondary colors
            
            // Settings
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            $table->string('currency')->default('MYR');
            $table->string('date_format')->default('d/m/Y');
            $table->json('settings')->nullable(); // Store additional company settings
            
            // Status and metadata
            $table->boolean('is_active')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('subscription_plan')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
