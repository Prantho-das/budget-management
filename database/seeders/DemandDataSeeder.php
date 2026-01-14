<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EconomicCode;
use App\Models\RpoUnit;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\BudgetEstimation;
use App\Models\BudgetType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemandDataSeeder extends Seeder
{
  public function run()
  {
    $this->command->info("Starting Demand Data Seeding...");

    try {
      // 1. Ensure 2025-26 Fiscal Year exists
      $targetFyName = '2025-26';
      $targetFy = FiscalYear::where('name', $targetFyName)->first();

      if (!$targetFy) {
        $this->command->warn("Fiscal Year $targetFyName not found. Creating it...");
        // Assuming start date is July 1st
        $startDate = '2025-07-01';
        $endDate = '2026-06-30';

        $targetFy = FiscalYear::create([
          'name' => $targetFyName,
          'start_date' => $startDate,
          'end_date' => $endDate,
          'status' => 0, // Inactive initially? Or 1? Let's use 0 as default for future
        ]);
        $this->command->info("Created Fiscal Year: $targetFyName");
      } else {
        $this->command->info("Found Target Fiscal Year: " . $targetFy->name);
      }

      // 2. Identify Previous 3 Fiscal Years
      $previousFys = FiscalYear::where('end_date', '<', $targetFy->start_date)
        ->orderBy('end_date', 'desc')
        ->take(3)
        ->get();

      if ($previousFys->count() < 3) {
        $this->command->warn("Found only " . $previousFys->count() . " previous fiscal years. Calculations might be partial.");
      }

      $fyIds = $previousFys->pluck('id')->toArray();
      $this->command->info("Using expenses from FYs: " . $previousFys->pluck('name')->implode(', '));

      // 3. Get generic Budget Type
      $budgetType = BudgetType::first();
      if (!$budgetType) {
        $this->command->error("No Budget Type found! Creating default.");
        $budgetType = BudgetType::create(['name' => 'Revenue', 'code' => 'revenue', 'status' => 1]);
      }

      // 4. Calculate Expenses and Insert Demand
      $economicCodes = EconomicCode::whereNotNull('parent_id')->get(); // Only child codes usually carry expenses? Or all? 
      // In SQL dump, expenses are on codes like '3211101' which have parent_id=1.
      // Let's use all economic codes to be safe, or filter if needed.

      $offices = RpoUnit::all();

      $inserted = 0;
      $updated = 0;

      foreach ($offices as $office) {
        foreach ($economicCodes as $ec) {
          // Sum expenses
          $totalExpense = Expense::where('rpo_unit_id', $office->id)
            ->where('economic_code_id', $ec->id)
            ->whereIn('fiscal_year_id', $fyIds)
            ->sum('amount');

          if ($totalExpense > 0) {
            // Create or update BudgetEstimation
            $estimation = BudgetEstimation::updateOrCreate(
              [
                'fiscal_year_id' => $targetFy->id,
                'rpo_unit_id' => $office->id,
                'economic_code_id' => $ec->id,
                'budget_type_id' => $budgetType->id,
              ],
              [
                'amount_demand' => $totalExpense,
                'status' => 'submitted', // Or 'draft'
                'current_stage' => 'Draft',
                'remarks' => "Auto-generated based on avg/sum of last 3 years expenses ($totalExpense)",
              ]
            );

            if ($estimation->wasRecentlyCreated) {
              $inserted++;
            } else {
              $updated++;
            }
          }
        }
      }

      $this->command->info("Seeding Completed.");
      $this->command->info("Inserted: $inserted");
      $this->command->info("Updated: $updated");
    } catch (\Exception $e) {
      $this->command->error("Error during seeding: " . $e->getMessage());
      Log::error($e);
    }
  }
}
