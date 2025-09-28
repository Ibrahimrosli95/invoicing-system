<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete(); // For multi-tenancy
            $table->foreignId('recorded_by')->constrained('users'); // User who recorded the payment
            
            // Payment details
            $table->decimal('amount', 12, 2); // Payment amount
            $table->date('payment_date'); // Date payment was received
            $table->date('recorded_date')->default(DB::raw('CURDATE()')); // Date payment was recorded in system
            
            // Payment method and details
            $table->enum('payment_method', ['CASH', 'CHEQUE', 'BANK_TRANSFER', 'CREDIT_CARD', 'ONLINE_BANKING', 'OTHER']);
            $table->string('reference_number', 100)->nullable(); // Bank reference, cheque number, etc.
            $table->text('notes')->nullable(); // Additional payment notes
            
            // Bank details (for bank transfers)
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            
            // Cheque details (for cheque payments)
            $table->string('cheque_number', 50)->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('cheque_bank', 100)->nullable();
            
            // Receipt tracking
            $table->string('receipt_number', 50)->nullable();
            $table->boolean('receipt_issued')->default(false);
            
            // Status tracking
            $table->enum('status', ['PENDING', 'CLEARED', 'BOUNCED', 'CANCELLED'])->default('CLEARED');
            $table->date('clearance_date')->nullable(); // Date payment cleared (for cheques)
            
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'payment_date']);
            $table->index(['company_id', 'payment_date']);
            $table->index(['payment_method', 'status']);
            $table->index(['reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
