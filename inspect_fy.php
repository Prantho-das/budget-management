<?php

use App\Models\FiscalYear;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$fy = FiscalYear::first();
echo "Attributes: " . implode(', ', array_keys($fy->getAttributes())) . "\n";
echo "Name: " . $fy->name . "\n";
echo "BN Name: " . ($fy->bn_name ?? 'NULL/UNDEFINED') . "\n";
