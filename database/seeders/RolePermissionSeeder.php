<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Company management
            'manage companies',
            'view companies',

            // User management
            'manage users',
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Team management
            'manage teams',
            'view teams',
            'create teams',
            'edit teams',
            'delete teams',

            // Lead management
            'manage leads',
            'view leads',
            'create leads',
            'edit leads',
            'delete leads',
            'assign leads',

            // Assessment management
            'manage assessments',
            'view assessments',
            'create assessments',
            'edit assessments',
            'update assessments',
            'delete assessments',
            'assign assessments',
            'upload assessment photos',
            'generate assessment pdfs',

            // Quotation management
            'manage quotations',
            'view quotations',
            'create quotations',
            'edit quotations',
            'delete quotations',
            'send quotations',

            // Invoice management
            'manage invoices',
            'view invoices',
            'create invoices',
            'edit invoices',
            'update invoice status',

            // Pricing management
            'manage pricing',
            'view pricing',
            'edit pricing',

            // Settings
            'manage settings',
            'view settings',

            // Reports
            'view reports',
            'export reports',

            // Audit & Security
            'view audit logs',
            'view audit dashboard',
            'export audit logs',
            'manage audit logs',
            'view security monitoring',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createRoles();
    }

    private function createRoles(): void
    {
        // 1. Superadmin - Full system access
        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->givePermissionTo(Permission::all());

        // 2. Company Manager - Company-wide read access, invoice status updates
        $companyManager = Role::firstOrCreate(['name' => 'company_manager']);
        $companyManager->givePermissionTo([
            'view companies',
            'view users',
            'view teams',
            'view leads',
            'view assessments',
            'view quotations',
            'view invoices',
            'update invoice status',
            'view pricing',
            'view settings',
            'view reports',
            'export reports',
            'view audit logs',
            'view audit dashboard',
            'export audit logs',
            'manage audit logs',
            'view security monitoring',
        ]);

        // 3. Finance Manager - Pricing control + invoice management
        $financeManager = Role::firstOrCreate(['name' => 'finance_manager']);
        $financeManager->givePermissionTo([
            'view companies',
            'view users',
            'view teams',
            'view leads',
            'view assessments',
            'view quotations',
            'manage invoices',
            'view invoices',
            'create invoices',
            'edit invoices',
            'update invoice status',
            'manage pricing',
            'view pricing',
            'edit pricing',
            'view settings',
            'view reports',
            'export reports',
            'view audit logs',
            'view audit dashboard',
            'export audit logs',
        ]);

        // 4. Sales Manager - Team-level management
        $salesManager = Role::firstOrCreate(['name' => 'sales_manager']);
        $salesManager->givePermissionTo([
            'view users',
            'view teams',
            'manage teams',
            'manage leads',
            'view leads',
            'create leads',
            'edit leads',
            'assign leads',
            'manage assessments',
            'view assessments',
            'create assessments',
            'edit assessments',
            'update assessments',
            'assign assessments',
            'upload assessment photos',
            'generate assessment pdfs',
            'manage quotations',
            'view quotations',
            'create quotations',
            'edit quotations',
            'send quotations',
            'view invoices',
            'view pricing',
            'view reports',
            'export reports',
        ]);

        // 5. Sales Coordinator - Lead management + company-wide view
        $salesCoordinator = Role::firstOrCreate(['name' => 'sales_coordinator']);
        $salesCoordinator->givePermissionTo([
            'view users',
            'view teams',
            'create leads',
            'view leads',
            'edit leads',
            'assign leads',
            'view assessments',
            'create assessments',
            'edit assessments',
            'assign assessments',
            'upload assessment photos',
            'view quotations',
            'view invoices',
            'view pricing',
            'view reports',
        ]);

        // 6. Sales Executive - Own records only
        $salesExecutive = Role::firstOrCreate(['name' => 'sales_executive']);
        $salesExecutive->givePermissionTo([
            'view teams',
            'create leads',
            'view leads',
            'edit leads',
            'view assessments',
            'create assessments',
            'edit assessments',
            'upload assessment photos',
            'create quotations',
            'view quotations',
            'edit quotations',
            'view pricing',
        ]);
    }
}
