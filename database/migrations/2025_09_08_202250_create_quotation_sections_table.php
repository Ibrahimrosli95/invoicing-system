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
        Schema::create('quotation_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            
            $table->string('name', 200); // Section name
            $table->text('description')->nullable(); // Section description/scope
            $table->integer('sort_order')->default(0); // For ordering sections
            
            // Financial totals for this section
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['quotation_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_sections');
    }
};