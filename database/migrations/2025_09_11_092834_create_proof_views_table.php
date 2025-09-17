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
        Schema::create('proof_views', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('proof_id');
            $table->unsignedBigInteger('company_id');
            
            // Viewer Information
            $table->unsignedBigInteger('user_id')->nullable(); // null for anonymous views
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            
            // View Context
            $table->enum('source', [
                'quotation_pdf',     // Viewed within quotation PDF
                'invoice_pdf',       // Viewed within invoice PDF
                'web_interface',     // Viewed in web UI
                'mobile_app',        // Viewed in mobile app
                'email_link',        // Clicked from email
                'direct_link',       // Direct link access
                'customer_portal'    // Customer portal view
            ])->default('web_interface');
            
            // Referenced document (if viewed via PDF)
            $table->morphs('document'); // quotation_id, invoice_id, etc.
            
            // Engagement Metrics
            $table->integer('duration_seconds')->default(0); // Time spent viewing
            $table->boolean('clicked_asset')->default(false); // Did they click on asset?
            $table->boolean('downloaded_asset')->default(false);
            $table->boolean('shared')->default(false);
            
            // Geographic Data (optional)
            $table->string('country', 2)->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            
            // Device Information
            $table->enum('device_type', ['desktop', 'tablet', 'mobile', 'unknown'])->default('unknown');
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            
            // Referrer Information
            $table->text('referrer_url')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            
            $table->timestamp('viewed_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['proof_id', 'viewed_at']);
            $table->index(['company_id', 'viewed_at']);
            $table->index(['user_id', 'viewed_at']);
            $table->index(['source', 'viewed_at']);
            $table->index('session_id');
            
            // Foreign Keys
            $table->foreign('proof_id')->references('id')->on('proofs')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proof_views');
    }
};
