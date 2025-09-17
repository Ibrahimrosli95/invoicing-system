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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            
            // Link to original quotation item (if converted from quotation)
            $table->unsignedBigInteger('quotation_item_id')->nullable();
            
            $table->string('description', 500); // Item/service description
            $table->text('specifications')->nullable(); // Technical specifications or details
            $table->string('unit', 50)->default('Nos'); // Unit of measurement
            $table->decimal('quantity', 10, 2); // Quantity invoiced
            $table->decimal('unit_price', 10, 2); // Price per unit
            $table->decimal('total_price', 12, 2); // Calculated total (quantity × unit_price)
            
            $table->integer('sort_order')->default(0); // For ordering items in invoice
            
            // Lock items when invoice is paid to prevent changes
            $table->boolean('is_locked')->default(false);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'sort_order']);
            $table->index(['quotation_item_id']); // For tracing back to quotation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
