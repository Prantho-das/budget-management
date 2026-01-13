<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\FiscalYear;
use App\Models\EconomicCode;
use App\Models\RpoUnit;

echo "Fiscal Years:\n";
foreach (FiscalYear::all() as $fy) {
  echo "ID: {$fy->id}, Name: {$fy->name}\n";
}

echo "\nEconomic Codes (First 10):\n";
foreach (EconomicCode::take(10)->get() as $ec) {
  echo "ID: {$ec->id}, Code: {$ec->code}, Name: {$ec->name}\n";
}

echo "\nOffices (First 10):\n";
foreach (RpoUnit::take(10)->get() as $office) {
  echo "ID: {$office->id}, Name: {$office->name}\n";
}
