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
        Schema::create('insurances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('policy_name');
            $table->text('description');
            $table->string('policy_type')->default('liability'); // liability, property, professional, workers_comp, cyber
            $table->string('policy_number')->unique();
            $table->string('insurer_name');
            $table->text('insurer_contact');
            $table->date('effective_date');
            $table->date('expiry_date');
            $table->decimal('coverage_amount', 15, 2);
            $table->decimal('premium_amount', 15, 2);
            $table->string('payment_frequency')->default('annual'); // annual, semi_annual, quarterly, monthly
            $table->string('currency', 3)->default('MYR');
            $table->text('coverage_details');
            $table->text('exclusions')->nullable();
            $table->text('deductible_info')->nullable();
            $table->string('broker_name')->nullable();
            $table->text('broker_contact')->nullable();
            $table->string('status')->default('active'); // active, expired, cancelled, pending_renewal
            $table->json('beneficiaries')->nullable();
            $table->decimal('claims_made', 15, 2)->default(0);
            $table->integer('claim_count')->default(0);
            $table->timestamp('last_claim_date')->nullable();
            $table->date('renewal_notice_date')->nullable();
            $table->boolean('auto_renewal')->default(false);
            $table->text('renewal_notes')->nullable();
            $table->json('policy_documents')->nullable(); // file paths
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign keys are defined inline above
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'policy_type']);
            $table->index(['effective_date', 'expiry_date']);
            $table->index('policy_number');
            $table->index('renewal_notice_date');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
