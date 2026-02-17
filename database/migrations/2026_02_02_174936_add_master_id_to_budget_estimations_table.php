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
            $table->foreignId('budget_estimation_master_id')->after('id')->nullable()/* /* /* ->constrained('budget_estimation_masters') */ */ *//* /* /* ->onDelete('cascade') */ */ */;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimations', function (Blueprint $table) {
            // // // $table->dropForeign(['budget_estimation_master_id']);
            $table->dropColumn('budget_estimation_master_id');
        });
    }
};
