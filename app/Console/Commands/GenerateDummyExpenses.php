<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\EconomicCode;
use App\Models\BudgetType;
use App\Models\Expense;
use Carbon\Carbon;

class GenerateDummyExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'budget:generate-dummy-expenses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates dummy expense data for previous fiscal years.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting dummy expense generation...');

        $activeFyId = get_active_fiscal_year_id();
        $activeFy = FiscalYear::find($activeFyId);
        
        if (!$activeFy) {
            $this->error('No active fiscal year found.');
            return;
        }

        // Get previous fiscal years
        $prevFiscalYears = FiscalYear::where('end_date', '<', $activeFy->start_date)
            ->orderBy('end_date', 'desc')
            ->get();

        if ($prevFiscalYears->isEmpty()) {
            $this->error('No previous fiscal years found.');
            return;
        }

        $offices = RpoUnit::all();
        // Fetch only 3rd layer Economic Codes (those that have a grandparent)
        $economicCodes = EconomicCode::whereHas('parent.parent')->get();
        $budgetType = BudgetType::first();

        if (!$budgetType) {
            $this->error('No budget type found.');
            return;
        }

        $this->info('Found ' . $prevFiscalYears->count() . ' previous fiscal years.');
        $this->info('Found ' . $offices->count() . ' offices.');
        $this->info('Found ' . $economicCodes->count() . ' economic codes.');
        
        $totalSteps = $prevFiscalYears->count() * $offices->count() * $economicCodes->count();
        $bar = $this->output->createProgressBar($totalSteps);

        foreach ($prevFiscalYears as $fy) {
            foreach ($offices as $office) {
                foreach ($economicCodes as $code) {
                    // Generate a random expense
                    Expense::create([
                        'code' => 'EXP-' . $fy->name . '-' . $office->code . '-' . $code->code . '-' . rand(1000, 9999),
                        'amount' => rand(10000, 500000),
                        'description' => 'Dummy Expense Data',
                        'date' => Carbon::parse($fy->start_date)->addDays(rand(1, 300)),
                        'economic_code_id' => $code->id,
                        'budget_type_id' => $budgetType->id,
                        'rpo_unit_id' => $office->id,
                        'fiscal_year_id' => $fy->id,
                        'status' => Expense::STATUS_APPROVED,
                        'approved_by' => 1, // Assuming admin user ID 1 exists
                        'approved_at' => now(),
                        'created_by' => 1,
                    ]);
                    $bar->advance();
                }
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info('Dummy expenses generated successfully!');
    }
}
