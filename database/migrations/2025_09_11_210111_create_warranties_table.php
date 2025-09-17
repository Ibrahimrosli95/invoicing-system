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
        Schema::create('warranties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('type')->default('product'); // product, service, extended, manufacturer
            $table->integer('coverage_period_months');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('coverage_details');
            $table->text('exclusions')->nullable();
            $table->text('claim_process')->nullable();
            $table->string('contact_info')->nullable();
            $table->string('status')->default('active'); // active, expired, claimed, suspended
            $table->decimal('coverage_amount', 15, 2)->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->json('terms_conditions')->nullable();
            $table->string('certificate_number')->unique()->nullable();
            $table->string('provider_name')->nullable();
            $table->text('provider_contact')->nullable();
            $table->integer('claim_count')->default(0);
            $table->decimal('claimed_amount', 15, 2)->default(0);
            $table->timestamp('last_claimed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign keys are defined inline above
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
            $table->index(['start_date', 'end_date']);
            $table->index('certificate_number');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
