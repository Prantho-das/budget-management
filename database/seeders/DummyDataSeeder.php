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
        // 1. Permissions List
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
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission,
                'group_name' => $group,
                'guard_name' => 'web'
            ]);
        }

        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(\Spatie\Permission\Models\Permission::all());

        $hqManager = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'HQ Manager', 'guard_name' => 'web']);
        $hqManager->syncPermissions([
            'view-budget-estimations',
            'approve-budget',
            'release-budget',
            'reject-budget',
            'view-reports',
            'view-all-offices-data'
        ]);

        $regionalManager = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Regional Manager', 'guard_name' => 'web']);
        $regionalManager->syncPermissions([
            'view-budget-estimations',
            'approve-budget',
            'reject-budget',
            'view-reports'
        ]);

        $districtManager = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'District Manager', 'guard_name' => 'web']);
        $districtManager->syncPermissions([
            'view-budget-estimations',
            'approve-budget',
            'reject-budget',
            'view-reports',
            'view-all-offices-data'
        ]);

        $userRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Office User', 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'view-budget-estimations',
            'create-budget-estimations',
            'edit-budget-estimations',
            'submit-budget-estimations',
            'view-expenses',
            'create-expenses',
        ]);

        // 2. Fiscal Years (Current + 4 Previous Years)
        $fiscalYears = [
            ['name' => '2020-21', 'start_date' => '2020-07-01', 'end_date' => '2021-06-30', 'status' => false],
            ['name' => '2021-22', 'start_date' => '2021-07-01', 'end_date' => '2022-06-30', 'status' => false],
            ['name' => '2022-23', 'start_date' => '2022-07-01', 'end_date' => '2023-06-30', 'status' => false],
            ['name' => '2023-24', 'start_date' => '2023-07-01', 'end_date' => '2024-06-30', 'status' => false],
            ['name' => '2024-25', 'start_date' => '2024-07-01', 'end_date' => '2025-06-30', 'status' => true],
        ];

        $createdFYs = [];
        foreach ($fiscalYears as $fyData) {
            $createdFYs[] = \App\Models\FiscalYear::firstOrCreate(
                ['name' => $fyData['name']],
                $fyData
            );
        }
        $fy = end($createdFYs); // Current fiscal year

        // 3. RPO Units (Hierarchy)
        $hq = \App\Models\RpoUnit::firstOrCreate(['code' => 'HQ001'], [
            'name' => 'Headquarters',
            'status' => true
        ]);

        $regional = \App\Models\RpoUnit::firstOrCreate(['code' => 'REG01'], [
            'name' => 'Dhaka Regional Office',
            'parent_id' => $hq->id,
            'status' => true
        ]);

        $district = \App\Models\RpoUnit::firstOrCreate(['code' => 'DIST01'], [
            'name' => 'Gazipur District Office',
            'parent_id' => $regional->id,
            'status' => true
        ]);

        $upazila = \App\Models\RpoUnit::firstOrCreate(['code' => 'UPZ01'], [
            'name' => 'Sreepur Upazila Office',
            'parent_id' => $district->id,
            'status' => true
        ]);

        // 4. Economic Codes
        $parentCode = \App\Models\EconomicCode::firstOrCreate(['code' => '32111'], [
            'name' => 'Rent and Utilities',
            'description' => 'Group for rent and utility payments'
        ]);

        $codes = [
            ['code' => '3211101', 'name' => 'Office Rent', 'description' => 'Rent for office buildings', 'parent_id' => $parentCode->id],
            ['code' => '3211102', 'name' => 'Electricity bill', 'description' => 'Payments for electricity', 'parent_id' => $parentCode->id],
            ['code' => '3211103', 'name' => 'Water bill', 'description' => 'Payments for water', 'parent_id' => $parentCode->id],
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
                'role' => 'Regional Manager'
            ],
            [
                'name' => 'District Manager',
                'email' => 'district@budget.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'rpo_unit_id' => $district->id,
                'role' => 'District Manager'
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

        // 6. Budget Types
        $originalType = \App\Models\BudgetType::firstOrCreate(['code' => 'ORIGINAL'], [
            'name' => 'Original Budget',
            'order_priority' => 1,
            'status' => true
        ]);

        $revisedType = \App\Models\BudgetType::firstOrCreate(['code' => 'REVISED'], [
            'name' => 'Revised Budget',
            'order_priority' => 2,
            'status' => true
        ]);

        $suppType = \App\Models\BudgetType::firstOrCreate(['code' => 'SUPPLEMENTARY'], [
            'name' => 'Supplementary Budget',
            'order_priority' => 3,
            'status' => true
        ]);

        // 7. Varied Budget Estimations & Sequential Logic
        $economicCodes = \App\Models\EconomicCode::all();
        $adminUser = \App\Models\User::where('email', 'admin@budget.com')->first();
        $regionalUser = \App\Models\User::where('email', 'regional@budget.com')->first();
        $districtUser = \App\Models\User::where('email', 'district@budget.com')->first();

        // Release some Original Budget for Upazila to unlock Revised Budget
        foreach ($economicCodes->take(2) as $code) {
            $approvalLog = [
                ['stage' => 'Draft', 'status' => 'submitted', 'user' => 'Upazila User 1', 'date' => now()->subDays(10)->toDateTimeString(), 'remarks' => 'Initial demand'],
                ['stage' => 'District', 'status' => 'approved', 'user' => 'District Manager', 'date' => now()->subDays(8)->toDateTimeString(), 'remarks' => 'Verified by district'],
                ['stage' => 'Regional', 'status' => 'approved', 'user' => 'Regional Manager', 'date' => now()->subDays(6)->toDateTimeString(), 'remarks' => 'Matches regional plan'],
                ['stage' => 'HQ', 'status' => 'approved', 'user' => 'HQ Admin', 'date' => now()->subDays(4)->toDateTimeString(), 'remarks' => 'Final approval for release'],
            ];

            $est = \App\Models\BudgetEstimation::create([
                'fiscal_year_id' => $fy->id,
                'rpo_unit_id' => $upazila->id,
                'economic_code_id' => $code->id,
                'budget_type_id' => $originalType->id,
                'amount_demand' => 100000,
                'current_stage' => 'Released',
                'status' => 'approved',
                'approval_log' => $approvalLog,
                'remarks' => 'Initial operational funds'
            ]);

            // Create Allocation for Released estimation
            \App\Models\BudgetAllocation::create([
                'fiscal_year_id' => $fy->id,
                'rpo_unit_id' => $upazila->id,
                'economic_code_id' => $code->id,
                'budget_type_id' => $originalType->id,
                'amount' => 100000,
            ]);

            // Create some Expenses
            \App\Models\Expense::create([
                'code' => 'EXP-' . strtoupper(uniqid()),
                'amount' => 5000,
                'description' => 'Office supplies',
                'date' => now()->subDays(5)->format('Y-m-d'),
                'rpo_unit_id' => $upazila->id,
                'fiscal_year_id' => $fy->id,
                'economic_code_id' => $code->id,
                'budget_type_id' => $originalType->id,
            ]);
        }

        // Add a Revised Budget demand for Upazila (now unlocked)
        \App\Models\BudgetEstimation::create([
            'fiscal_year_id' => $fy->id,
            'rpo_unit_id' => $upazila->id,
            'economic_code_id' => $economicCodes->first()->id,
            'budget_type_id' => $revisedType->id,
            'amount_demand' => 20000,
            'current_stage' => 'Draft',
            'status' => 'draft',
            'remarks' => 'Additional funds needed for maintenance'
        ]);

        // District Submissions (At different stages)
        // 1. One at Regional Review
        \App\Models\BudgetEstimation::create([
            'fiscal_year_id' => $fy->id,
            'rpo_unit_id' => $district->id,
            'economic_code_id' => $economicCodes[2]->id,
            'budget_type_id' => $originalType->id,
            'amount_demand' => 500000,
            'current_stage' => 'Regional',
            'status' => 'submitted',
            'approval_log' => [['stage' => 'District', 'status' => 'approved', 'user' => 'District Manager', 'date' => now()->subDays(2)->toDateTimeString()]],
            'remarks' => 'Pending regional approval'
        ]);

        // 2. One at HQ Review
        \App\Models\BudgetEstimation::create([
            'fiscal_year_id' => $fy->id,
            'rpo_unit_id' => $district->id,
            'economic_code_id' => $economicCodes[3]->id,
            'budget_type_id' => $originalType->id,
            'amount_demand' => 750000,
            'current_stage' => 'HQ',
            'status' => 'submitted',
            'approval_log' => [
                ['stage' => 'District', 'status' => 'approved', 'user' => 'District Manager', 'date' => now()->subDays(4)->toDateTimeString()],
                ['stage' => 'Regional', 'status' => 'approved', 'user' => 'Regional Manager', 'date' => now()->subDays(3)->toDateTimeString()]
            ],
            'remarks' => 'Final HQ review'
        ]);

        // 8. User Transfer History
        $testUser = \App\Models\User::where('email', 'upazila1@budget.com')->first();
        if ($testUser) {
            \App\Models\UserOfficeTransfer::create([
                'user_id' => $testUser->id,
                'from_office_id' => $district->id,
                'to_office_id' => $upazila->id,
                'transfer_date' => now()->subMonths(2),
                'remarks' => 'Initial placement in Upazila',
                'created_by' => $adminUser?->id
            ]);
        }

        // 9. Historical Data for Previous 4 Fiscal Years
        $economicCodes = \App\Models\EconomicCode::all();
        $offices = [$upazila, $district, $regional];

        // Seed data for the first 4 fiscal years (not current year)
        for ($i = 0; $i < 4; $i++) {
            $historicalFY = $createdFYs[$i];
            $baseAmount = 50000 + ($i * 10000); // Increasing amounts over years

            foreach ($offices as $office) {
                foreach ($economicCodes->take(5) as $code) {
                    $demandAmount = $baseAmount + rand(5000, 15000);
                    $releasedAmount = $demandAmount * (rand(85, 98) / 100); // 85-98% of demand
                    $expenseAmount = $releasedAmount * (rand(90, 99) / 100); // 90-99% of released

                    // Create Budget Estimation (Demand)
                    $estimation = \App\Models\BudgetEstimation::create([
                        'fiscal_year_id' => $historicalFY->id,
                        'budget_type_id' => $originalType->id,
                        'rpo_unit_id' => $office->id,
                        'economic_code_id' => $code->id,
                        'amount_demand' => $demandAmount,
                        'amount_approved' => $releasedAmount,
                        'status' => 'approved',
                        'current_stage' => 'Released',
                        'approval_log' => json_encode([
                            [
                                'from_stage' => 'Draft',
                                'to_stage' => 'Released',
                                'action_by' => $adminUser->id,
                                'action_name' => $adminUser->name,
                                'action_role' => 'Super Admin',
                                'action_at' => \Carbon\Carbon::parse($historicalFY->start_date)->format('Y-m-d H:i:s'),
                                'remarks' => 'Historical data - auto approved'
                            ]
                        ])
                    ]);

                    // Create Budget Allocation (Released)
                    \App\Models\BudgetAllocation::create([
                        'fiscal_year_id' => $historicalFY->id,
                        'budget_type_id' => $originalType->id,
                        'rpo_unit_id' => $office->id,
                        'economic_code_id' => $code->id,
                        'amount' => $releasedAmount,
                        'remarks' => "Historical allocation for FY {$historicalFY->name}"
                    ]);

                    // Create Expenses (Actual Spending)
                    $numExpenses = rand(3, 8);
                    $remainingAmount = $expenseAmount;

                    for ($e = 0; $e < $numExpenses; $e++) {
                        if ($remainingAmount <= 0) break;

                        $expAmount = ($e == $numExpenses - 1)
                            ? $remainingAmount
                            : $remainingAmount * (rand(10, 30) / 100);

                        \App\Models\Expense::create([
                            'code' => 'EXP-' . strtoupper(uniqid()),
                            'amount' => $expAmount,
                            'description' => "Historical expense for {$code->name}",
                            'date' => \Carbon\Carbon::parse($historicalFY->start_date)->addMonths($e),
                            'rpo_unit_id' => $office->id,
                            'fiscal_year_id' => $historicalFY->id,
                            'economic_code_id' => $code->id,
                            'budget_type_id' => $originalType->id,
                        ]);

                        $remainingAmount -= $expAmount;
                    }
                }

                // Add some rejected budgets for realism (10% chance)
                if (rand(1, 10) == 1) {
                    $rejectedCode = $economicCodes->random();
                    \App\Models\BudgetEstimation::create([
                        'fiscal_year_id' => $historicalFY->id,
                        'budget_type_id' => $originalType->id,
                        'rpo_unit_id' => $office->id,
                        'economic_code_id' => $rejectedCode->id,
                        'amount_demand' => rand(20000, 50000),
                        'status' => 'draft',
                        'current_stage' => 'Draft',
                        'approval_log' => json_encode([
                            [
                                'from_stage' => 'Waiting for Approval',
                                'to_stage' => 'Draft',
                                'action_by' => $adminUser->id,
                                'action_name' => $adminUser->name,
                                'action_role' => 'Super Admin',
                                'action_at' => \Carbon\Carbon::parse($historicalFY->start_date)->addMonths(2)->format('Y-m-d H:i:s'),
                                'remarks' => 'Rejected - Insufficient justification'
                            ]
                        ])
                    ]);
                }
            }
        }
    }
}
