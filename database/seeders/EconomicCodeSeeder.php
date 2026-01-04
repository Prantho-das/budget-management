<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EconomicCode;

class EconomicCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentCode = EconomicCode::firstOrCreate(['code' => '32111'], [
            'name' => 'Rent and Utilities',
            'description' => 'Group for rent and utility payments'
        ]);

        $codes = [
            ['code' => '3211101', 'name' => 'Office Rent', 'description' => 'Rent for office buildings', 'parent_id' => $parentCode->id],
            ['code' => '3211102', 'name' => 'Electricity bill', 'description' => 'Payments for electricity', 'parent_id' => $parentCode->id],
            ['code' => '3211103', 'name' => 'Water bill', 'description' => 'Payments for water', 'parent_id' => $parentCode->id],
            ['code' => '3221101', 'name' => 'Stationery', 'description' => 'Pens, paper, etc.'],
            ['code' => '3821101', 'name' => 'Furniture', 'description' => 'Office desks and chairs'],
        ];

        foreach ($codes as $code) {
            EconomicCode::firstOrCreate(['code' => $code['code']], $code);
        }
    }
}
