<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'HQ Admin',
                'email' => 'admin@budget.com',
                'password' => Hash::make('password'),
                'rpo_unit_id' => 1,
                'role' => 'Super Admin'
            ],
            [
                'name' => 'ABO',
                'email' => 'abo@budget.com',
                'password' => Hash::make('password'),
                'rpo_unit_id' => 1,
                'role' => 'Assistant Budget Officer (HQ)'
            ],
            [
                'name' => 'Md. Masud Rana',
                'email' => 'bo@budget.com',
                'password' => Hash::make('password'),
                'rpo_unit_id' => 1,
                'role' => 'Budget Officer (HQ)'
            ],
            [
                'name' => 'Rony Ahmed',
                'email' => 'uttoraentry@budget.com',
                'password' => Hash::make('password'),
                'rpo_unit_id' => 6,
                'role' => 'Budget Entry (Unit Office)'
            ],
            [
                'name' => 'RPO APP',
                'email' => 'upazila2@budget.com',
                'password' => Hash::make('password'),
                'rpo_unit_id' => 6,
                'role' => 'Budget Approval (Unit Office)'
            ],
        ];

        foreach ($users as $userData) {
            $roleName = $userData['role'];
            unset($userData['role']);
            $user = User::firstOrCreate(['email' => $userData['email']], $userData);
            $user->assignRole($roleName);
        }
    }
}
