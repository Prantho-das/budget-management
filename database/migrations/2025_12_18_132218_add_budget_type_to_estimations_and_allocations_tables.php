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
            $table->string('budget_type')->default('Main Budget')->after('fiscal_year_id');
        });

        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->string('budget_type')->default('Main Budget')->after('fiscal_year_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimations', function (Blueprint $table) {
            $table->dropColumn('budget_type');
        });

        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->dropColumn('budget_type');
        });
    }
};
