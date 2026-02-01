<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$duplicates = App\Models\BudgetAllocation::select('economic_code_id', 'fiscal_year_id', 'rpo_unit_id')
  ->groupBy('economic_code_id', 'fiscal_year_id', 'rpo_unit_id')
  ->havingRaw('COUNT(DISTINCT budget_type_id) > 1')
  ->get();

if ($duplicates->count() > 0) {
  echo "Found " . $duplicates->count() . " cases where an economic code has multiple budget types.\n";
  print_r($duplicates->toArray());
} else {
  echo "No cases found where an economic code has multiple budget types in the same FY and office.\n";
}
