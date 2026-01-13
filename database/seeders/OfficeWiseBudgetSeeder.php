<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetEstimation;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use App\Models\EconomicCode;
use App\Models\BudgetType;

class OfficeWiseBudgetSeeder extends Seeder
{
  public function run()
  {
    // Use an existing economic code: 3221101 (Stationery)
    $economicCode = EconomicCode::where('code', '3221101')->first();
    if (!$economicCode) {
      $economicCode = EconomicCode::create([
        'code' => '3221101',
        'name' => 'Stationery',
        'description' => 'Office stationery supplies'
      ]);
    }

    $budgetType = BudgetType::first(); // Revenue or Original
    $offices = RpoUnit::whereNotNull('parent_id')->get(); // Sub-offices

    // Get Fiscal Years
    $fy2022_23 = FiscalYear::where('name', '2022-23')->first();
    $fy2023_24 = FiscalYear::where('name', '2023-24')->first();
    $fy2024_25 = FiscalYear::where('name', '2024-25')->first(); // Current

    foreach ($offices as $office) {
      // 1. Insert Expenses for past 3 years (to show in report columns)
      // FY 2022-23 (history_full_1 if current is 24-25?)
      // Actually history_full_1 is the year before last, history_full_2 is last year.

      // FY 2022-23 Expenses
      if ($fy2022_23) {
        Expense::create([
          'code' => 'EXP-' . uniqid(),
          'date' => '2022-10-15',
          'amount' => rand(50000, 100000),
          'rpo_unit_id' => $office->id,
          'fiscal_year_id' => $fy2022_23->id,
          'economic_code_id' => $economicCode->id,
          'budget_type_id' => $budgetType->id,
        ]);
      }

      // FY 2023-24 Expenses (Full Year)
      if ($fy2023_24) {
        Expense::create([
          'code' => 'EXP-' . uniqid(),
          'date' => '2023-11-20',
          'amount' => rand(60000, 110000),
          'rpo_unit_id' => $office->id,
          'fiscal_year_id' => $fy2023_24->id,
          'economic_code_id' => $economicCode->id,
          'budget_type_id' => $budgetType->id,
        ]);

        // FY 2023-24 (First 6 months - for history_part_1)
        Expense::create([
          'code' => 'EXP-' . uniqid(),
          'date' => '2023-08-10',
          'amount' => rand(20000, 40000),
          'rpo_unit_id' => $office->id,
          'fiscal_year_id' => $fy2023_24->id,
          'economic_code_id' => $economicCode->id,
          'budget_type_id' => $budgetType->id,
        ]);
      }

      // FY 2024-25 Expenses (First 6 months - for history_part_2)
      if ($fy2024_25) {
        Expense::create([
          'code' => 'EXP-' . uniqid(),
          'date' => '2024-09-05',
          'amount' => rand(25000, 45000),
          'rpo_unit_id' => $office->id,
          'fiscal_year_id' => $fy2024_25->id,
          'economic_code_id' => $economicCode->id,
          'budget_type_id' => $budgetType->id,
        ]);

        // 2. Insert Current Year Demand and Projections
        BudgetEstimation::updateOrCreate(
          [
            'target_office_id' => $office->id, // Important for Ministry view
            'fiscal_year_id' => $fy2024_25->id,
            'budget_type_id' => $budgetType->id,
            'economic_code_id' => $economicCode->id,
            'rpo_unit_id' => $office->id, // Some systems use this for origin
          ],
          [
            'amount_demand' => rand(150000, 200000),
            'revised_amount' => rand(140000, 160000),
            'projection_1' => rand(170000, 220000), // Next year estimation
            'projection_2' => rand(190000, 240000), // Projection 1
            'projection_3' => rand(210000, 260000), // Projection 2
            'status' => 'approved',
            'current_stage' => 'Released'
          ]
        );
      }
    }
  }
}
