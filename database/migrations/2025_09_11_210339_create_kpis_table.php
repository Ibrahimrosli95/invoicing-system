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
        Schema::create('kpis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('category')->default('performance'); // performance, financial, operational, customer, quality
            $table->string('metric_type')->default('percentage'); // percentage, number, currency, ratio, time
            $table->decimal('current_value', 15, 4);
            $table->decimal('target_value', 15, 4)->nullable();
            $table->decimal('baseline_value', 15, 4)->nullable();
            $table->string('unit')->nullable(); // %, units, hours, MYR, etc.
            $table->string('measurement_frequency')->default('monthly'); // daily, weekly, monthly, quarterly, yearly
            $table->string('calculation_method')->nullable(); // formula or method description
            $table->json('data_sources')->nullable(); // where data comes from
            $table->string('status')->default('active'); // active, inactive, archived
            $table->string('trend')->nullable(); // improving, declining, stable
            $table->decimal('previous_value', 15, 4)->nullable();
            $table->timestamp('last_measured_at')->nullable();
            $table->timestamp('next_measurement_at')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null'); // user responsible for this KPI
            $table->string('alert_threshold_type')->nullable(); // above, below, equal
            $table->decimal('alert_threshold_value', 15, 4)->nullable();
            $table->boolean('alerts_enabled')->default(false);
            $table->json('alert_recipients')->nullable(); // user IDs to notify
            $table->text('notes')->nullable();
            $table->json('historical_data')->nullable(); // store past values
            $table->string('visualization_type')->default('line'); // line, bar, gauge, pie
            $table->json('chart_config')->nullable();
            $table->integer('display_order')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign keys are defined inline above
            
            // Indexes
            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'status']);
            $table->index('measurement_frequency');
            $table->index('next_measurement_at');
            $table->index('display_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpis');
    }
};
