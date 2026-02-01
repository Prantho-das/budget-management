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
            $table->foreignId('ministry_budget_master_id')
                ->nullable()
                ->after('id')
                ->constrained('ministry_budget_masters')
                ->onDelete('set null')
                ->comment('Links to the source Ministry Budget batch if applicable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_allocations', function (Blueprint $table) {
            $table->dropForeign(['ministry_budget_master_id']);
            $table->dropColumn('ministry_budget_master_id');
        });
    }
};
