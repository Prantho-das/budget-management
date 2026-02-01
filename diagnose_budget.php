<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Budget Types:\n";
$types = App\Models\BudgetType::all();
foreach ($types as $type) {
  echo "ID: {$type->id}, Name: {$type->name}, Status: {$type->status}, Priority: {$type->order_priority}\n";
}

echo "\nDefault Type Search:\n";
$defaultType = App\Models\BudgetType::where('status', true)->orderBy('order_priority')->first();
if ($defaultType) {
  echo "Default Type Found: ID {$defaultType->id} ({$defaultType->name})\n";
} else {
  echo "No default type found with status=true!\n";
}

echo "\nAllocations Count: " . App\Models\BudgetAllocation::count() . "\n";
