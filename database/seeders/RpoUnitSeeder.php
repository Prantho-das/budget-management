<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RpoUnit;

class RpoUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define Headquarters root
        $hq = RpoUnit::updateOrCreate(['id' => 1], [
            'name' => 'প্রধান কার্যালয়',
            'code' => '১৬১০৫০১',
            'status' => true,
            'parent_id' => null
        ]);

        // Define Regional level
        $regional = RpoUnit::updateOrCreate(['id' => 5], [
            'name' => 'বিভাগীয় পাসপোর্ট ও ভিসা অফিসসমূহ',
            'code' => 'REG001',
            'parent_id' => $hq->id,
            'status' => true
        ]);

        // Define specific office
        RpoUnit::updateOrCreate(['id' => 6], [
            'name' => 'বিভাগীয় পাসপোর্ট ও ভিসা অফিস, ঢাকা',
            'code' => '১৩৩২৫৫',
            'parent_id' => $regional->id,
            'status' => true
        ]);

        // Fallback for older seeder codes if any
        RpoUnit::firstOrCreate(['code' => 'HQ001'], [
            'name' => 'Headquarters (Alt)',
            'status' => true
        ]);
    }
}
