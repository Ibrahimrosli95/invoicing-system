<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if companies table exists first
        if (!DB::getSchemaBuilder()->hasTable('companies')) {
            // Skip this migration if companies table doesn't exist yet
            // This handles fresh installations where no tables exist
            return;
        }

        // Get all companies with existing invoice_settings
        $companies = DB::table('companies')
            ->whereNotNull('invoice_settings')
            ->get();

        foreach ($companies as $company) {
            $settings = json_decode($company->invoice_settings, true);

            // Skip if settings is not an array or columns already exist
            if (!is_array($settings)) {
                continue;
            }

            // Add default columns array if not present
            if (!isset($settings['columns'])) {
                $settings['columns'] = [
                    ['key' => 'sl', 'label' => 'Sl.', 'visible' => true, 'order' => 1],
                    ['key' => 'description', 'label' => 'Description', 'visible' => true, 'order' => 2],
                    ['key' => 'quantity', 'label' => 'Qty', 'visible' => true, 'order' => 3],
                    ['key' => 'rate', 'label' => 'Rate', 'visible' => true, 'order' => 4],
                    ['key' => 'amount', 'label' => 'Amount', 'visible' => true, 'order' => 5]
                ];

                // Update the company's invoice_settings
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update([
                        'invoice_settings' => json_encode($settings),
                        'updated_at' => now()
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if companies table exists first
        if (!DB::getSchemaBuilder()->hasTable('companies')) {
            return;
        }

        // Get all companies with invoice_settings containing columns
        $companies = DB::table('companies')
            ->whereNotNull('invoice_settings')
            ->get();

        foreach ($companies as $company) {
            $settings = json_decode($company->invoice_settings, true);

            if (!is_array($settings)) {
                continue;
            }

            // Remove columns array if it exists
            if (isset($settings['columns'])) {
                unset($settings['columns']);

                // Update the company's invoice_settings
                DB::table('companies')
                    ->where('id', $company->id)
                    ->update([
                        'invoice_settings' => json_encode($settings),
                        'updated_at' => now()
                    ]);
            }
        }
    }
};