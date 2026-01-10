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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username')->unique()->after('name')->nullable();
            } else {
                // Ensure unique if it exists but might not be unique
                // (Though usually it's added as unique or not at all)
            }
            $table->string('phone')->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
            // Removing unique from phone is tricky if other unique keys exist,
            // but let's try dropping by name if possible or just leaving it.
            // $table->dropUnique(['phone']);
        });
    }
};
