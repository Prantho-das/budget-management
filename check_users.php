<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "--- ROLES ---\n";
foreach (Role::all() as $role) {
  echo "Role: {$role->name} (ID: {$role->id})\n";
  echo "  Permissions: " . $role->permissions->pluck('name')->implode(', ') . "\n";
}

echo "\n--- USERS ---\n";
foreach (User::with('roles', 'rpoUnit')->get() as $user) {
  echo "ID: {$user->id} | Name: {$user->name} | Email: {$user->email}\n";
  echo "  Office: " . ($user->rpoUnit ? $user->rpoUnit->name : 'N/A') . "\n";
  echo "  Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
  echo "--------------\n";
}
