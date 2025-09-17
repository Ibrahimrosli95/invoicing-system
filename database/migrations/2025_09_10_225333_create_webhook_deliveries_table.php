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
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_endpoint_id')->constrained()->onDelete('cascade');
            $table->string('event_type');
            $table->json('payload');
            $table->string('status')->default('pending'); // pending, sent, failed, retrying
            $table->string('http_status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('next_retry_at')->nullable();
            $table->string('signature')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->timestamps();

            $table->index(['webhook_endpoint_id', 'status']);
            $table->index(['webhook_endpoint_id', 'event_type']);
            $table->index(['status', 'next_retry_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
    }
};
