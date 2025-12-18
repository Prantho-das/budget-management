<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Roles & Permissions
        $permissions = [
            'manage-users' => 'User Management',
            'manage-setup' => 'System Setup',
            'create-budget' => 'Budget Estimation',
            'submit-budget' => 'Budget Estimation',
            'approve-budget' => 'Budget Approval',
            'view-reports' => 'Reports',
        ];

        foreach ($permissions as $permission => $group) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'group_name' => $group,
                'guard_name' => 'web'
            ]);
        }

        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::all());

        $managerRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'HQ Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions(['approve-budget', 'view-reports']);

        $userRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Office User', 'guard_name' => 'web']);
        $userRole->syncPermissions(['create-budget', 'submit-budget']);

        // 2. Fiscal Years
        $fy = \App\Models\FiscalYear::firstOrCreate([
            'name' => '2024-25',
        ], [
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'status' => true,
        ]);

        // 3. RPO Units (Hierarchy)
        $hq = \App\Models\RpoUnit::firstOrCreate(['code' => 'HQ001'], [
            'name' => 'Headquarters',
            'type' => 'headquarters',
            'status' => true
        ]);

        $regional = \App\Models\RpoUnit::firstOrCreate(['code' => 'REG01'], [
            'name' => 'Dhaka Regional Office',
            'type' => 'regional',
            'parent_id' => $hq->id,
            'status' => true
        ]);

        $district = \App\Models\RpoUnit::firstOrCreate(['code' => 'DIST01'], [
            'name' => 'Gazipur District Office',
            'type' => 'divisional',
            'parent_id' => $regional->id,
            'status' => true
        ]);

        $upazila = \App\Models\RpoUnit::firstOrCreate(['code' => 'UPZ01'], [
            'name' => 'Sreepur Upazila Office',
            'type' => 'divisional',
            'parent_id' => $district->id,
            'status' => true
        ]);

        // 4. Economic Codes
        $codes = [
            ['code' => '3211101', 'name' => 'Office Rent', 'description' => 'Rent for office buildings'],
            ['code' => '3211102', 'name' => 'Electricity bill', 'description' => 'Payments for electricity'],
            ['code' => '3211103', 'name' => 'Water bill', 'description' => 'Payments for water'],
            ['code' => '3221101', 'name' => 'Stationery', 'description' => 'Pens, paper, etc.'],
            ['code' => '3821101', 'name' => 'Furniture', 'description' => 'Office desks and chairs'],
        ];

        foreach ($codes as $code) {
            \App\Models\EconomicCode::firstOrCreate(['code' => $code['code']], $code);
        }

        // 5. Users
        // 5. Users
        $users = [
            [
                'name' => 'HQ Admin',
                'email' => 'admin@budget.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'rpo_unit_id' => $hq->id,
                'role' => 'Super Admin'
            ],
            [
                'name' => 'Regional Manager',
                'email' => 'regional@budget.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'rpo_unit_id' => $regional->id,
                'role' => 'HQ Manager'
            ],
            [
                'name' => 'District Manager',
                'email' => 'district@budget.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'rpo_unit_id' => $district->id,
                'role' => 'HQ Manager'
            ],
            [
                'name' => 'Upazila User 1',
                'email' => 'upazila1@budget.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'rpo_unit_id' => $upazila->id,
                'role' => 'Office User'
            ],
            [
                'name' => 'Upazila User 2',
                'email' => 'upazila2@budget.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'rpo_unit_id' => $upazila->id, // Same office for testing multiple users
                'role' => 'Office User'
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            $user = \App\Models\User::firstOrCreate(['email' => $userData['email']], $userData);
            $user->assignRole($roleName);
        }

        // 6. Varied Budget Estimations for Testing
        $economicCodes = \App\Models\EconomicCode::all();

        // Upazila 1 Submissions (Ready for District Review)
        foreach ($economicCodes->take(3) as $code) {
            \App\Models\BudgetEstimation::create([
                'fiscal_year_id' => $fy->id,
                'rpo_unit_id' => $upazila->id,
                'economic_code_id' => $code->id,
                'amount_demand' => rand(50000, 100000),
                'status' => 'submitted',
                'remarks' => 'Monthly operational cost'
            ]);
        }

        // District Submissions (Ready for Regional Review)
        foreach ($economicCodes->take(3) as $code) {
            \App\Models\BudgetEstimation::create([
                'fiscal_year_id' => $fy->id,
                'rpo_unit_id' => $district->id,
                'economic_code_id' => $code->id,
                'amount_demand' => rand(200000, 500000),
                'status' => 'submitted',
                'remarks' => 'District level equipment procurement'
            ]);
        }
    }
}
