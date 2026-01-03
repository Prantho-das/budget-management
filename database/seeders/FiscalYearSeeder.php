<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FiscalYear;

class FiscalYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fiscalYears = [
            ['name' => '2020-21', 'start_date' => '2020-07-01', 'end_date' => '2021-06-30', 'status' => false],
            ['name' => '2021-22', 'start_date' => '2021-07-01', 'end_date' => '2022-06-30', 'status' => false],
            ['name' => '2022-23', 'start_date' => '2022-07-01', 'end_date' => '2023-06-30', 'status' => false],
            ['name' => '2023-24', 'start_date' => '2023-07-01', 'end_date' => '2024-06-30', 'status' => false],
            ['name' => '2024-25', 'start_date' => '2024-07-01', 'end_date' => '2025-06-30', 'status' => true],
        ];

        foreach ($fiscalYears as $fyData) {
            FiscalYear::firstOrCreate(
                ['name' => $fyData['name']],
                $fyData
            );
        }
    }
}
