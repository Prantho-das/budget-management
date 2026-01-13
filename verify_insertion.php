<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BudgetEstimation;
use App\Models\Expense;
use App\Models\EconomicCode;

$ec = EconomicCode::where('code', '3221101')->first();
$results = [];

if ($ec) {
  $results['budget_estimations_count'] = BudgetEstimation::where('economic_code_id', $ec->id)->count();
  $results['expenses_count'] = Expense::where('economic_code_id', $ec->id)->count();
  $results['recent_estimations'] = BudgetEstimation::where('economic_code_id', $ec->id)
    ->latest()
    ->take(3)
    ->get(['id', 'amount_demand', 'projection_1', 'projection_2', 'projection_3'])
    ->toArray();
} else {
  $results['error'] = "Economic Code 3221101 not found";
}

file_put_contents('verification_results.json', json_encode($results, JSON_PRETTY_PRINT));
