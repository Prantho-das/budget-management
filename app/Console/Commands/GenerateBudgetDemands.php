<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateBudgetDemands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:generate-demands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate budget demands based on database records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Determine Fiscal Years
        // "All dynamic from database" - typically means active or all meaningful years
        // Let's grab all active ones, or just all if user wants a full seed
        // User said "no fixed data", so let's grab all Fiscal Years present in DB
        $fiscalYears = \App\Models\FiscalYear::orderBy('start_date')->get();

        // 2. Determine Budget Type
        $budgetType = \App\Models\BudgetType::where('code', 'original')->first()
                      ?? \App\Models\BudgetType::first();

        if (! $budgetType) {
            $this->error('No Budget Type found.');

            return;
        }

        // 3. User & Workflow (Dynamic)
        $user = \App\Models\User::first();
        $workflowStep = \App\Models\WorkflowStep::orderBy('order', 'asc')->first();
        $finalStep = \App\Models\WorkflowStep::orderBy('order', 'desc')->first();

        // 4. Units & Codes
        $offices = \App\Models\RpoUnit::all(); // Or filter if needed
        $economicCodes = \App\Models\EconomicCode::whereNotNull('parent_id')->get();

        $fyCount = $fiscalYears->count();
        $officeCount = $offices->count();
        $codeCount = $economicCodes->count();

        $this->info('Found Check Data:');
        $this->info("- Fiscal Years: {$fyCount}");
        $this->info("- RPO Units: {$officeCount}");
        $this->info("- Economic Codes (Leaf): {$codeCount}");
        $this->info('Total estimated entries: '.($fyCount * $officeCount * $codeCount));

        if ($offices->isEmpty() || $economicCodes->isEmpty()) {
            $this->error('Missing Offices or Economic Codes.');

            return;
        }

        if (! $this->confirm('Do you wish to proceed with generating budget demands?', true)) {
            $this->info('Operation cancelled.');

            return;
        }

        // 5. Process
        foreach ($fiscalYears as $fy) {
            $this->info("Processing Fiscal Year: {$fy->name}");

            // "Dynamic" logic for status/step based on date?
            // If the FY end date is in the past, assume it's approved/historical.
            $isHistorical = \Carbon\Carbon::parse($fy->end_date)->isPast();
            $targetStatus = $isHistorical ? 'approved' : 'submitted';
            // If historical, use final step. If current/future, use first step (draft/submitted) or specific?
            // Seeder used: $isHistorical ? ($finalStep->id ?? 5) : ($workflowStep->id ?? 1);
            $targetStepId = $isHistorical ? ($finalStep->id ?? null) : ($workflowStep->id ?? null);

            if (! $targetStepId) {
                $this->warn('Workflow steps missing. Using default IDs may fail.');
            }

            $bar = $this->output->createProgressBar($offices->count());
            $bar->start();

            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                foreach ($offices as $office) {
                    // Unique check or Reset?
                    // User didn't ask for clear, just generate.
                    // 'firstOrCreate' prevents duplication.

                    $master = \App\Models\BudgetEstimationMaster::firstOrCreate([
                        'fiscal_year_id' => $fy->id,
                        'rpo_unit_id' => $office->id,
                        'budget_type_id' => $budgetType->id,
                    ], [
                        'workflow_step_id' => $targetStepId,
                        'created_by' => $user->id ?? 1,
                        'status' => $targetStatus,
                        'total_amount' => 0,
                        'batch_no' => 'CMD-'.$fy->id.'-'.$office->id.'-'.time(),
                    ]);

                    $totalAmount = 0;
                    $batchData = [];

                    foreach ($economicCodes as $code) {
                        $exists = \App\Models\BudgetEstimation::where([
                            'budget_estimation_master_id' => $master->id,
                            'economic_code_id' => $code->id,
                        ])->exists();

                        if (! $exists) {
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

                    if (! empty($batchData)) {
                        \App\Models\BudgetEstimation::insert($batchData);
                        $master->increment('total_amount', $totalAmount);
                    }

                    $bar->advance();
                }

                \Illuminate\Support\Facades\DB::commit();
                $bar->finish();
                $this->newLine();

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                $this->error("Failed for {$fy->name}: ".$e->getMessage());
            }
        }

        $this->info('Done.');
    }
}
