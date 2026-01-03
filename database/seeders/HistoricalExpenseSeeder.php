<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\RpoUnit;
use App\Models\FiscalYear;
use App\Models\EconomicCode;
use App\Models\BudgetType;

class HistoricalExpenseSeeder extends Seeder
{
    public function run()
    {
        $office = RpoUnit::where('code', '১৩৩২৫৫')->first();
        if (!$office) {
            $this->command->error("Office 133255 not found.");
            return;
        }

        $activeFy = FiscalYear::orderBy('end_date', 'desc')->first();
        if (!$activeFy) {
            $this->command->error("No fiscal year found.");
            return;
        }

        $previousYears = FiscalYear::where('end_date', '<', $activeFy->start_date)
            ->orderBy('end_date', 'desc')
            ->get();

        $economicCodes = EconomicCode::whereNotNull('parent_id')->get();
        $budgetType = BudgetType::first();

        foreach ($previousYears as $index => $fy) {
          
            foreach ($economicCodes as $code) {
                // Generate some realistic dummy data
                // Base amount depends on the year and the code to make it look "historical"
                $baseAmount = rand(50000, 200000);
                $multiplier = 1 - ($index * 0.1); // Slightly less in older years
                
                Expense::create([
                    'code' => 'EXP-' . $fy->name . '-' . $code->code . '-' . rand(100, 999),
                    'amount' => round($baseAmount * $multiplier),
                    'description' => 'Historical expense for ' . $code->name,
                    'date' => $fy->start_date, // Setting it to the start of that year
                    'economic_code_id' => $code->id,
                    'budget_type_id' => $budgetType->id,
                    'rpo_unit_id' => $office->id,
                    'fiscal_year_id' => $fy->id,
                ]);
            }
        }

        $this->command->info("Historical expenses seeded for Office 133255.");
    }
}
