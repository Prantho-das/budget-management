<?php

use App\Models\BudgetEstimation;
use App\Models\FiscalYear;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fy = FiscalYear::where('name', '2025-26')->first();

if (!$fy) {
  echo "Fiscal Year 2025-26 NOT found.\n";
  exit(1);
}

echo "Fiscal Year 2025-26 found (ID: {$fy->id}).\n";

$count = BudgetEstimation::where('fiscal_year_id', $fy->id)->count();
echo "Total Demand Records for 2025-26: $count\n";

if ($count > 0) {
  $sample = BudgetEstimation::where('fiscal_year_id', $fy->id)->first();
  echo "Sample Record:\n";
  echo "- Economic Code ID: {$sample->economic_code_id}\n";
  echo "- Office ID: {$sample->rpo_unit_id}\n";
  echo "- Amount Demand: {$sample->amount_demand}\n";
  echo "- Remarks: {$sample->remarks}\n";
} else {
  echo "No records found.\n";
}
