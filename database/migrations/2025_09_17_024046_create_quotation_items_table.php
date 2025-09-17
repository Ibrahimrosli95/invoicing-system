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
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quotation_section_id')->nullable()->constrained()->nullOnDelete();

            $table->string('description', 500);
            $table->string('unit', 50)->default('Nos');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('item_code', 100)->nullable();
            $table->text('specifications')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Indexes
            $table->index(['quotation_id', 'sort_order']);
            $table->index(['quotation_section_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
