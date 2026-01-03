<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkflowStepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $steps = [
            [
                'name' => 'Budget Entry (Unit Office)',
                'required_permission' => 'approve-budget',
                'order' => 1,
                'office_level' => 'origin',
                'is_active' => true,
            ],
            [
                'name' => 'Budget Approval (Unit Office)',
                'required_permission' => 'approve-budget',
                'order' => 2,
                'office_level' => 'parent',
                'is_active' => true,
            ],
            [
                'name' => 'Assistant Budget Officer (HQ)',
                'required_permission' => 'release-budget',
                'order' => 3,
                'office_level' => 'hq',
                'is_active' => true,
            ],
            [
                'name' => 'Budget Officer (HQ)',
                'required_permission' => 'release-budget',
                'order' => 4,
                'office_level' => 'hq',
                'is_active' => true,
            ],
        ];

        foreach ($steps as $step) {
            \App\Models\WorkflowStep::updateOrCreate(['name' => $step['name']], $step);
        }
    }
}
