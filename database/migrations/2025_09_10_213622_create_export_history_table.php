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
        Schema::create('export_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Export details
            $table->string('export_type'); // report, bulk_data, etc.
            $table->string('data_type'); // leads, quotations, invoices, payments
            $table->string('format'); // csv, xlsx, pdf
            $table->json('configuration'); // filters, fields, etc.
            
            // File information
            $table->string('filename');
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable(); // in bytes
            $table->string('download_url')->nullable();
            
            // Processing information
            $table->string('status'); // pending, processing, completed, failed
            $table->integer('total_records')->nullable();
            $table->integer('processed_records')->default(0);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            
            // Timing
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_time')->nullable(); // seconds
            
            // Error handling
            $table->text('error_message')->nullable();
            $table->json('error_details')->nullable();
            $table->integer('retry_count')->default(0);
            
            // Cleanup
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_downloaded')->default(false);
            $table->timestamp('downloaded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'user_id']);
            $table->index(['status', 'created_at']);
            $table->index(['data_type', 'format']);
            $table->index(['expires_at', 'is_downloaded']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_history');
    }
};