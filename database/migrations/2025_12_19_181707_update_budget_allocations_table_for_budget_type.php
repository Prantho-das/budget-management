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
        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->foreignId('budget_type_id')->nullable()->after('fiscal_year_id')->constrained('budget_types')->onDelete('cascade');
            $table->dropColumn('budget_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->string('budget_type')->default('Main Budget')->after('fiscal_year_id');
            $table->dropForeign(['budget_type_id']);
            $table->dropColumn('budget_type_id');
        });
    }
};
