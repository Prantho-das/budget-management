<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetEstimation;
use App\Models\BudgetAllocation;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\EconomicCode;
use App\Models\BudgetType;
use App\Models\User;
use App\Models\RpoUnit;
use App\Models\UserOfficeTransfer;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fy = FiscalYear::where('status', true)->first();
        if (!$fy) return;

        $originalType = BudgetType::where('code', 'original')->first();
        $revisedType = BudgetType::where('code', 'revised')->first();

        $upazila = RpoUnit::where('id', 6)->first();
        $district = RpoUnit::where('id', 5)->first();

        if (!$upazila || !$district || !$originalType) return;

        $economicCodes = EconomicCode::all();
        $adminUser = User::where('email', 'admin@budget.com')->first();

        // 1. Release some Original Budget for Upazila to unlock Revised Budget
        foreach ($economicCodes->take(2) as $code) {
            $approvalLog = [
                ['stage' => 'Draft', 'status' => 'submitted', 'user' => 'Rony Ahmed', 'date' => now()->subDays(10)->toDateTimeString(), 'remarks' => 'Initial demand'],
                ['stage' => 'Unit Approval', 'status' => 'approved', 'user' => 'RPO APP', 'date' => now()->subDays(8)->toDateTimeString(), 'remarks' => 'Verified by unit'],
                ['stage' => 'HQ Review', 'status' => 'approved', 'user' => 'ABO', 'date' => now()->subDays(6)->toDateTimeString(), 'remarks' => 'Matches HQ plan'],
                ['stage' => 'HQ Final', 'status' => 'approved', 'user' => 'Md. Masud Rana', 'date' => now()->subDays(4)->toDateTimeString(), 'remarks' => 'Final approval for release'],
            ];

            BudgetEstimation::create([
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

            BudgetAllocation::create([
                'fiscal_year_id' => $fy->id,
                'rpo_unit_id' => $upazila->id,
                'economic_code_id' => $code->id,
                'budget_type_id' => $originalType->id,
                'amount' => 100000,
            ]);

            Expense::create([
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

        // 2. Add a Revised Budget demand for Upazila (now unlocked)
        if ($revisedType) {
            BudgetEstimation::create([
                'fiscal_year_id' => $fy->id,
                'rpo_unit_id' => $upazila->id,
                'economic_code_id' => $economicCodes->first()->id,
                'budget_type_id' => $revisedType->id,
                'amount_demand' => 20000,
                'current_stage' => 'Draft',
                'status' => 'draft',
                'remarks' => 'Additional funds needed for maintenance'
            ]);
        }

        // 3. User Transfer History
        $testUser = User::where('email', 'uttoraentry@budget.com')->first();
        if ($testUser) {
            UserOfficeTransfer::create([
                'user_id' => $testUser->id,
                'from_office_id' => $district->id,
                'to_office_id' => $upazila->id,
                'transfer_date' => now()->subMonths(2),
                'remarks' => 'Initial placement in Upazila',
                'created_by' => $adminUser?->id
            ]);
        }

        // 4. Historical Data for Previous Years
        $historicalFYs = FiscalYear::where('status', false)->orderBy('end_date', 'desc')->take(3)->get();
        foreach ($historicalFYs as $index => $historicalFY) {
            $baseAmount = 50000 + ($index * 10000);
            foreach ($economicCodes->take(3) as $code) {
                $demandAmount = $baseAmount + rand(5000, 15000);
                $releasedAmount = $demandAmount * 0.9;

                BudgetAllocation::create([
                    'fiscal_year_id' => $historicalFY->id,
                    'budget_type_id' => $originalType->id,
                    'rpo_unit_id' => $upazila->id,
                    'economic_code_id' => $code->id,
                    'amount' => $releasedAmount,
                    'remarks' => "Historical allocation for FY {$historicalFY->name}"
                ]);

                Expense::create([
                    'code' => 'EXP-' . strtoupper(uniqid()),
                    'amount' => $releasedAmount * 0.95,
                    'description' => "Historical expense for {$code->name}",
                    'date' => Carbon::parse($historicalFY->start_date)->addMonths(2),
                    'rpo_unit_id' => $upazila->id,
                    'fiscal_year_id' => $historicalFY->id,
                    'economic_code_id' => $code->id,
                    'budget_type_id' => $originalType->id,
                ]);
            }
        }
    }
}
