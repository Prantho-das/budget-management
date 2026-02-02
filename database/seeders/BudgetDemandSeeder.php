<?php

namespace Database\Seeders;

use App\Models\BudgetEstimation;
use App\Models\BudgetEstimationMaster;
use App\Models\BudgetType;
use App\Models\EconomicCode;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\User;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetDemandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activeFiscalYear = FiscalYear::where('status', 1)->first();
        if (!$activeFiscalYear) {
            // Fallback to 2022-23 if none active
            $activeFiscalYear = FiscalYear::where('name', '2022-23')->first() ?? FiscalYear::first();
        }

        if (!$activeFiscalYear) {
            $this->command->error('No fiscal years found.');
            return;
        }

        // We'll seed 4 years: 2022-23, 2023-24, 2024-25, 2025-26
        // These are IDs 8, 9, 10, 11 in the system or just sequentially from 2022-23
        $fiscalYears = FiscalYear::whereIn('name', ['2022-23', '2023-24', '2024-25', '2025-26'])
            ->orderBy('start_date')
            ->get();

        $budgetType = BudgetType::where('code', 'original')->first();
        if (!$budgetType) {
            $budgetType = BudgetType::first();
        }

        $user = User::first();
        $workflowStep = WorkflowStep::first();
        $finalStep = WorkflowStep::orderBy('order', 'desc')->first();

        $offices = RpoUnit::all();
        $economicCodes = EconomicCode::whereNotNull('parent_id')->get();

        foreach ($fiscalYears as $fy) {
            $this->command->info("Seeding budget demands for Fiscal Year: {$fy->name}");
            $this->command->getOutput()->progressStart($offices->count());

            // Historical years (before 2025-26) should be approved to show history
            $isHistorical = $fy->start_date < '2025-07-01';
            $targetStatus = $isHistorical ? 'approved' : 'submitted';
            $targetStep = $isHistorical ? ($finalStep->id ?? 5) : ($workflowStep->id ?? 1);

            DB::beginTransaction();
            try {
                foreach ($offices as $office) {
                    // Check if master already exists to avoid duplicates
                    $master = BudgetEstimationMaster::firstOrCreate([
                        'fiscal_year_id' => $fy->id,
                        'rpo_unit_id' => $office->id,
                        'budget_type_id' => $budgetType->id,
                    ], [
                        'workflow_step_id' => $targetStep,
                        'created_by' => $user->id ?? 1,
                        'status' => $targetStatus,
                        'total_amount' => 0,
                        'batch_no' => 'SEED-' . $fy->name . '-' . now()->format('His') . '-' . $office->id,
                    ]);

                    $totalAmount = 0;
                    $batchData = [];

                    foreach ($economicCodes as $code) {
                        // Check if entry already exists
                        $exists = BudgetEstimation::where([
                            'budget_estimation_master_id' => $master->id,
                            'economic_code_id' => $code->id,
                        ])->exists();

                        if (!$exists) {
                            $amount = rand(50000, 500000);
                            $approvedAmount = $isHistorical ? $amount : null;
                            $totalAmount += $amount;

                            $batchData[] = [
                                'budget_estimation_master_id' => $master->id,
                                'fiscal_year_id' => $fy->id,
                                'budget_type_id' => $budgetType->id,
                                'rpo_unit_id' => $office->id,
                                'economic_code_id' => $code->id,
                                'amount_demand' => $amount,
                                'amount_approved' => $approvedAmount,
                                'status' => $targetStatus,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }

                    if (!empty($batchData)) {
                        BudgetEstimation::insert($batchData);
                        $master->increment('total_amount', $totalAmount);
                    }

                    $this->command->getOutput()->progressAdvance();
                }

                DB::commit();
                $this->command->getOutput()->progressFinish();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->command->error("Seeding failed for {$fy->name}: " . $e->getMessage());
            }
        }

        $this->command->info('Budget demand seeding completed successfully!');
    }
}
