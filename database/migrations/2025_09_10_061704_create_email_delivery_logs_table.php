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
        Schema::create('email_delivery_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('notification_type');
            $table->morphs('related_model'); // lead, quotation, invoice, etc.
            $table->string('recipient_email');
            $table->string('recipient_name');
            $table->string('subject');
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'bounced']);
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional tracking data
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['notification_type', 'status']);
            $table->index('recipient_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_delivery_logs');
    }
};
