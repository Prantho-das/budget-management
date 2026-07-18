<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $hqManager = Role::firstOrCreate(['name' => 'HQ Manager', 'guard_name' => 'web']);
        $hqManager->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'view-budget-approvals',
            'approve-budget',
            'release-budget',
            'reject-budget',
            'edit-budget-approval-amount',
            'view-budget-distribution',
            'create-budget-distribution',
            'view-reports',
            'view-all-offices-data',
            'menu-budget-demand',
            'menu-budget-distribution',
            'menu-budget-preparation',
        ]);

        $regionalManager = Role::firstOrCreate(['name' => 'Regional Manager', 'guard_name' => 'web']);
        $regionalManager->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'view-budget-approvals',
            'approve-budget',
            'reject-budget',
            'edit-budget-approval-amount',
            'view-reports',
            'menu-budget-demand',
        ]);

        $districtManager = Role::firstOrCreate(['name' => 'District Manager', 'guard_name' => 'web']);
        $districtManager->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'view-budget-approvals',
            'approve-budget',
            'reject-budget',
            'edit-budget-approval-amount',
            'view-reports',
            'view-all-offices-data',
            'menu-budget-demand',
        ]);

        $userRole = Role::firstOrCreate(['name' => 'Office User', 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'create-budget-estimations',
            'edit-budget-estimations',
            'submit-budget-estimations',
            'view-expenses',
            'create-expenses',
            'view-budget-status',
            'view-budget-summary',
            'menu-budget-demand',
        ]);

        Role::firstOrCreate(['name' => 'Assistant Budget Officer (HQ)', 'guard_name' => 'web'])->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'view-budget-approvals',
            'approve-budget',
            'reject-budget',
            'edit-budget-approval-amount',
            'view-reports',
            'view-budget-status',
            'view-budget-summary',
            'menu-budget-demand',
        ]);

        Role::firstOrCreate(['name' => 'Budget Officer (HQ)', 'guard_name' => 'web'])->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'view-budget-approvals',
            'release-budget',
            'reject-budget',
            'edit-budget-approval-amount',
            'view-reports',
            'view-budget-status',
            'view-budget-summary',
            'menu-budget-demand',
        ]);

        Role::firstOrCreate(['name' => 'Budget Approval (Unit Office)', 'guard_name' => 'web'])->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'view-budget-approvals',
            'approve-budget',
            'reject-budget',
            'edit-budget-approval-amount',
            'view-reports',
            'view-budget-status',
            'view-budget-summary',
            'menu-budget-demand',
        ]);

        Role::firstOrCreate(['name' => 'Budget Entry (Unit Office)', 'guard_name' => 'web'])->syncPermissions([
            'view-dashboard',
            'view-budget-estimations',
            'create-budget-estimations',
            'edit-budget-estimations',
            'submit-budget-estimations',
            'view-budget-status',
            'view-budget-summary',
            'menu-budget-demand',
        ]);
    }
}
