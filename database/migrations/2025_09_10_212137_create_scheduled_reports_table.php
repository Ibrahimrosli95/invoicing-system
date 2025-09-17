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
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('report_type'); // leads, quotations, invoices, etc.
            $table->json('configuration'); // report configuration (filters, fields, etc.)
            
            // Schedule configuration
            $table->string('frequency'); // daily, weekly, monthly, quarterly, yearly
            $table->json('schedule_config'); // day of week, day of month, etc.
            $table->time('send_time'); // what time to send
            $table->json('recipients'); // email addresses to send to
            
            // Status and tracking
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->json('last_run_result')->nullable(); // success/failure info
            $table->integer('run_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['next_run_at', 'is_active']);
            $table->index(['user_id', 'report_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};