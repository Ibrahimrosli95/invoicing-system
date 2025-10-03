<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Create a test company
        $company = \App\Models\Company::create([
            'name' => 'Bina Group',
            'slug' => 'bina-group',
            'email' => 'info@binagroup.com',
            'phone' => '+60123456789',
            'address' => 'Kuala Lumpur',
            'city' => 'Kuala Lumpur',
            'state' => 'Federal Territory',
            'postal_code' => '50000',
            'country' => 'Malaysia',
            'currency' => 'MYR',
            'timezone' => 'Asia/Kuala_Lumpur',
            'is_active' => true,
        ]);

        // Create superadmin user
        $superadmin = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Super Admin',
            'email' => 'admin@binagroup.com',
            'is_active' => true,
        ]);
        $superadmin->assignRole('superadmin');

        // Create test user
        $testUser = User::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'is_active' => true,
        ]);
        $testUser->assignRole('sales_executive');
    }
}
