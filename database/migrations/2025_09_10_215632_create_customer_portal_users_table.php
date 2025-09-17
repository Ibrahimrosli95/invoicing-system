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
        Schema::create('customer_portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Basic customer information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            
            // Authentication
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            
            // Profile information
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('Malaysia');
            
            // Account status and settings
            $table->boolean('is_active')->default(true);
            $table->json('notification_preferences')->nullable();
            $table->string('preferred_language')->default('en');
            $table->string('timezone')->default('Asia/Kuala_Lumpur');
            
            // Access control
            $table->json('accessible_quotations')->nullable(); // Array of quotation IDs
            $table->json('accessible_invoices')->nullable(); // Array of invoice IDs
            $table->boolean('can_download_pdfs')->default(true);
            $table->boolean('can_view_payment_history')->default(true);
            
            // Tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('login_count')->default(0);
            
            // Password reset
            $table->string('password_reset_token')->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'email']);
            $table->index(['company_id', 'is_active']);
            $table->index(['email', 'is_active']);
            $table->index('password_reset_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_portal_users');
    }
};