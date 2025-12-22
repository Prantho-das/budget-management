<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $keys = [
            ['key' => 'footer_text', 'value' => 'Crafted with ❤️ by Themesbrand', 'type' => 'text', 'group' => 'general'],
            ['key' => 'site_logo_light', 'value' => null, 'type' => 'image', 'group' => 'appearance'],
        ];

        foreach ($keys as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
