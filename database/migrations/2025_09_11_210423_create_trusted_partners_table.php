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
        Schema::create('trusted_partners', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('partner_name');
            $table->text('description');
            $table->string('partner_type')->default('supplier'); // supplier, distributor, contractor, consultant, vendor, technology
            $table->string('partnership_level')->default('standard'); // preferred, strategic, standard, new
            $table->string('website')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Malaysia');
            $table->string('postal_code')->nullable();
            $table->date('partnership_start_date');
            $table->date('partnership_end_date')->nullable();
            $table->string('status')->default('active'); // active, inactive, suspended, terminated
            $table->text('services_provided');
            $table->decimal('annual_business_volume', 15, 2)->default(0);
            $table->string('currency', 3)->default('MYR');
            $table->integer('performance_rating')->default(5); // 1-10 scale
            $table->text('performance_notes')->nullable();
            $table->json('certifications')->nullable(); // relevant certifications
            $table->json('insurance_info')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null'); // user who verified
            $table->text('verification_notes')->nullable();
            $table->json('contract_terms')->nullable();
            $table->string('payment_terms')->nullable();
            $table->boolean('nda_signed')->default(false);
            $table->date('nda_expiry_date')->nullable();
            $table->json('project_history')->nullable(); // past projects
            $table->integer('projects_completed')->default(0);
            $table->timestamp('last_project_date')->nullable();
            $table->boolean('preferred_vendor')->default(false);
            $table->integer('response_time_hours')->nullable();
            $table->decimal('quality_score', 3, 2)->nullable(); // 0-10 scale
            $table->json('capabilities')->nullable(); // list of capabilities
            $table->json('references')->nullable(); // client references
            $table->json('social_media')->nullable();
            $table->text('internal_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign keys are defined inline above
            
            // Indexes
            $table->index(['company_id', 'partner_type']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'partnership_level']);
            $table->index('performance_rating');
            $table->index('is_verified');
            $table->index('preferred_vendor');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_partners');
    }
};
