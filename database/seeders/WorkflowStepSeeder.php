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
                'name' => 'Budget Approval (Unit Office)',
                'required_permission' => 'approve-budget',
                'order' => 1,
                'office_level' => 'origin',
                'is_active' => true,
            ],
            [
                'name' => 'Assistant Budget Officer (HQ)',
                'required_permission' => 'approve-budget',
                'order' => 2,
                'office_level' => 'hq',
                'is_active' => true,
            ],
            [
                'name' => 'Budget Officer (HQ)',
                'required_permission' => 'release-budget',
                'order' => 3,
                'office_level' => 'hq',
                'is_active' => true,
            ],
        ];

        // Deactivate older steps if they exist
        \App\Models\WorkflowStep::whereNotIn('name', array_column($steps, 'name'))->update(['is_active' => false]);

        foreach ($steps as $step) {
            \App\Models\WorkflowStep::updateOrCreate(['name' => $step['name']], $step);
        }
    }
}
