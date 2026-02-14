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
        // Clear existing data as we are changing the structure fundamentally
        \Illuminate\Support\Facades\DB::table('ministry_allocations')->truncate();

        Schema::table('ministry_allocations', function (Blueprint $table) {
            $table->foreignId('ministry_budget_master_id');
            
            // Drop redundant columns
            // $table->dropColumn('fiscal_year_id');
            // $table->dropColumn('rpo_unit_id');
            // $table->dropColumn('budget_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ministry_allocations', function (Blueprint $table) {
            $table->dropColumn('ministry_budget_master_id');
            
            $table->foreignId('fiscal_year_id')->nullable()->constrained('fiscal_years')->onDelete('cascade');
            $table->foreignId('rpo_unit_id')->nullable()->constrained('rpo_units')->onDelete('cascade');
            $table->string('budget_type')->default('original');
        });
    }
};
