<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'view-users' => 'Users',
            'create-users' => 'Users',
            'edit-users' => 'Users',
            'delete-users' => 'Users',

            // Setup - Offices
            'view-offices' => 'Offices',
            'create-offices' => 'Offices',
            'edit-offices' => 'Offices',
            'delete-offices' => 'Offices',

            // Setup - Economic Codes
            'view-economic-codes' => 'Economic Codes',
            'create-economic-codes' => 'Economic Codes',
            'edit-economic-codes' => 'Economic Codes',
            'delete-economic-codes' => 'Economic Codes',

            // Setup - Fiscal Years
            'view-fiscal-years' => 'Fiscal Years',
            'create-fiscal-years' => 'Fiscal Years',
            'edit-fiscal-years' => 'Fiscal Years',
            'delete-fiscal-years' => 'Fiscal Years',

            // Setup - Budget Types
            'view-budget-types' => 'Budget Types',
            'create-budget-types' => 'Budget Types',
            'edit-budget-types' => 'Budget Types',
            'delete-budget-types' => 'Budget Types',

            // Setup - Expenses
            'view-expenses' => 'Expenses',
            'create-expenses' => 'Expenses',
            'edit-expenses' => 'Expenses',
            'delete-expenses' => 'Expenses',
            'approve-expenses' => 'Expenses',

            // Budgeting
            'view-budget-estimations' => 'Budget Estimation',
            'create-budget-estimations' => 'Budget Estimation',
            'edit-budget-estimations' => 'Budget Estimation',
            'delete-budget-estimations' => 'Budget Estimation',
            'submit-budget-estimations' => 'Budget Estimation',

            // Approval Workflow
            'approve-budget' => 'Budget Approval',
            'reject-budget' => 'Budget Approval',
            'release-budget' => 'Budget Approval',

            // Security management
            'view-roles' => 'Roles',
            'create-roles' => 'Roles',
            'edit-roles' => 'Roles',
            'delete-roles' => 'Roles',
            'view-permissions' => 'Permissions',
            'create-permissions' => 'Permissions',
            'edit-permissions' => 'Permissions',
            'delete-permissions' => 'Permissions',

            // System Settings
            'view-system-settings' => 'System Settings',
            'edit-system-settings' => 'System Settings',

            // Reports
            'view-reports' => 'Reports',
            'view-transfer-history' => 'User Transfers',
            'view-all-offices-data' => 'Permissions',
        ];

        foreach ($permissions as $permission => $group) {
            Permission::firstOrCreate([
                'name' => $permission,
                'group_name' => $group,
                'guard_name' => 'web'
            ]);
        }
    }
}
