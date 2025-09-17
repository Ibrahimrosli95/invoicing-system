<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CustomerSegment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies to create segments for each
        $companies = Company::all();

        foreach ($companies as $company) {
            // Get the first user from this company for created_by
            $user = User::where('company_id', $company->id)->first();
            
            if (!$user) {
                continue; // Skip if no users in this company
            }

            $defaultSegments = CustomerSegment::getDefaultSegments();

            foreach ($defaultSegments as $segmentData) {
                CustomerSegment::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'name' => $segmentData['name']
                    ],
                    array_merge($segmentData, [
                        'company_id' => $company->id,
                        'created_by' => $user->id,
                        'is_active' => true,
                    ])
                );
            }

            $this->command->info("Created customer segments for company: {$company->name}");
        }
    }
}
