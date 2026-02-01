<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$allocations = App\Models\BudgetAllocation::limit(5)->get();
print_r($allocations->toArray());
