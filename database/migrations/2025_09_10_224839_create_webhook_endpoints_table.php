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
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('url');
            $table->string('secret_key');
            $table->json('events')->comment('Array of event types this endpoint subscribes to');
            $table->boolean('is_active')->default(true);
            $table->string('description')->nullable();
            $table->integer('timeout')->default(30)->comment('Timeout in seconds');
            $table->integer('max_retries')->default(3);
            $table->json('headers')->nullable()->comment('Custom headers to include in requests');
            $table->timestamp('last_ping_at')->nullable();
            $table->string('last_ping_status')->nullable();
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_endpoints');
    }
};
