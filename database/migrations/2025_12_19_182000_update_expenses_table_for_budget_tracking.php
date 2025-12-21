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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('economic_code_id')->nullable()->after('expense_category_id')->constrained('economic_codes')->onDelete('cascade');
            $table->foreignId('budget_type_id')->nullable()->after('economic_code_id')->constrained('budget_types')->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->nullable()->change(); // Already exists but ensuring nullable/constrained if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['economic_code_id']);
            $table->dropForeign(['budget_type_id']);
            $table->dropColumn(['economic_code_id', 'budget_type_id']);
        });
    }
};
