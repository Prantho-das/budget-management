<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('text'); // text, textarea, image, file
            $table->string('group')->default('general'); // general, seo, social, appearance
            $table->timestamps();
        });

        // Insert default settings
        DB::table('system_settings')->insert([
            ['key' => 'site_name', 'value' => 'Budget Management System', 'type' => 'text', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_title', 'value' => 'Budget Management System', 'type' => 'text', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_logo', 'value' => null, 'type' => 'image', 'group' => 'appearance', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'site_favicon', 'value' => null, 'type' => 'image', 'group' => 'appearance', 'created_at' => now(), 'updated_at' => now()],
            
            // SEO Settings
            ['key' => 'meta_description', 'value' => 'Government Budget Management System', 'type' => 'textarea', 'group' => 'seo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'meta_keywords', 'value' => 'budget, management, government, finance', 'type' => 'textarea', 'group' => 'seo', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'meta_author', 'value' => '', 'type' => 'text', 'group' => 'seo', 'created_at' => now(), 'updated_at' => now()],
            
            // Social Media
            ['key' => 'facebook_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'twitter_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'linkedin_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'youtube_url', 'value' => '', 'type' => 'text', 'group' => 'social', 'created_at' => now(), 'updated_at' => now()],
            
            // Contact Info
            ['key' => 'contact_email', 'value' => '', 'type' => 'text', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_phone', 'value' => '', 'type' => 'text', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'contact_address', 'value' => '', 'type' => 'textarea', 'group' => 'general', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
