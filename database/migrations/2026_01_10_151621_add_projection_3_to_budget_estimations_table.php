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
            if (!Schema::hasColumn('budget_estimations', 'projection_3')) {
                $table->decimal('projection_3', 20, 2)->nullable()->after('projection_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimations', function (Blueprint $table) {
            $table->dropColumn('projection_3');
        });
    }
};
