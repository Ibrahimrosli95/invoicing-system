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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            
            // Multi-tenancy and hierarchy
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            // Source relationships
            $table->unsignedBigInteger('quotation_id')->nullable(); // Converted from quotation
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete(); // Original lead (inherited from quotation)
            
            // Document details
            $table->string('number', 50)->unique(); // INV-2025-000001
            $table->enum('status', ['DRAFT', 'SENT', 'PARTIAL', 'PAID', 'OVERDUE', 'CANCELLED'])->default('DRAFT');
            $table->date('issued_date'); // Invoice issue date
            $table->date('due_date'); // Payment due date
            $table->integer('payment_terms')->default(30); // Payment terms in days
            
            // Customer information (copied from quotation or entered manually)
            $table->string('customer_name', 100);
            $table->string('customer_phone', 20);
            $table->string('customer_email', 100)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_city', 100)->nullable();
            $table->string('customer_state', 100)->nullable();
            $table->string('customer_postal_code', 20)->nullable();
            
            // Invoice details
            $table->string('title', 200); // Invoice description
            $table->text('description')->nullable(); // Work description
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            
            // Financial totals
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            
            // Payment tracking
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->timestamp('last_payment_date')->nullable();
            $table->integer('overdue_days')->default(0); // Auto-calculated
            
            // Status tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable(); // Full payment date
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            // PDF Generation
            $table->string('pdf_path')->nullable();
            $table->timestamp('pdf_generated_at')->nullable();
            
            // Bank and payment details
            $table->text('bank_details')->nullable(); // Company bank information
            $table->string('reference_number', 100)->nullable(); // Customer reference
            
            $table->timestamps();
            
            // Indexes for performance and queries
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'due_date']);
            $table->index(['company_id', 'issued_date']);
            $table->index(['team_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['quotation_id']); // For conversion tracking
            $table->index(['number']); // For searching
            $table->index(['customer_phone']); // For customer lookup
            $table->index(['due_date', 'status']); // For overdue queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
