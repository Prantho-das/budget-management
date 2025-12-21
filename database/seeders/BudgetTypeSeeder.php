<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetType;

class BudgetTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Original Budget', 'code' => 'original', 'order_priority' => 1],
            ['name' => 'Revised Budget', 'code' => 'revised', 'order_priority' => 2],
            ['name' => 'Supplementary Budget', 'code' => 'supplementary', 'order_priority' => 3],
        ];

        foreach ($types as $type) {
            BudgetType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
