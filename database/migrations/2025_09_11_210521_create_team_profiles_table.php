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
        Schema::create('team_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('profile_type')->default('individual'); // individual, team, department
            $table->string('display_name');
            $table->string('job_title');
            $table->text('bio')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->json('expertise_areas'); // list of expertise areas
            $table->json('certifications')->nullable(); // professional certifications
            $table->json('qualifications')->nullable(); // education qualifications
            $table->json('skills')->nullable(); // technical and soft skills
            $table->integer('years_experience')->default(0);
            $table->json('specializations')->nullable(); // specialized areas
            $table->json('project_types')->nullable(); // types of projects handled
            $table->integer('projects_completed')->default(0);
            $table->decimal('client_satisfaction_rating', 3, 2)->default(5.00); // 1-10 scale
            $table->text('achievements')->nullable();
            $table->json('awards')->nullable(); // awards and recognitions
            $table->json('languages')->nullable(); // spoken languages with proficiency
            $table->string('availability_status')->default('available'); // available, busy, unavailable
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->string('currency', 3)->default('MYR');
            $table->json('working_hours')->nullable(); // availability schedule
            $table->json('time_zones')->nullable();
            $table->boolean('available_for_travel')->default(false);
            $table->json('travel_preferences')->nullable();
            $table->json('contact_preferences')->nullable(); // email, phone, video
            $table->string('linkedin_profile')->nullable();
            $table->json('social_profiles')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->json('portfolio_items')->nullable(); // portfolio pieces
            $table->json('client_testimonials')->nullable(); // specific to this profile
            $table->json('case_studies')->nullable(); // relevant case studies
            $table->integer('response_time_hours')->default(24);
            $table->boolean('featured_profile')->default(false);
            $table->integer('profile_views')->default(0);
            $table->timestamp('last_active_at')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->text('internal_notes')->nullable(); // private notes
            $table->string('status')->default('active'); // active, inactive, archived
            $table->boolean('public_profile')->default(false); // show on website
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign keys are defined inline above
            
            // Unique constraint
            $table->unique(['company_id', 'user_id']);
            
            // Indexes
            $table->index('profile_type');
            $table->index('status');
            $table->index('availability_status');
            $table->index('featured_profile');
            $table->index('public_profile');
            $table->index('client_satisfaction_rating');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_profiles');
    }
};
