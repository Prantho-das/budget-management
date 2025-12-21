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
        Schema::table('budget_estimations', function (Blueprint $table) {
            $table->foreignId('target_office_id')->nullable()->after('rpo_unit_id')->constrained('rpo_units')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimations', function (Blueprint $table) {
            $table->dropForeign(['target_office_id']);
            $table->dropColumn('target_office_id');
        });
    }
};
