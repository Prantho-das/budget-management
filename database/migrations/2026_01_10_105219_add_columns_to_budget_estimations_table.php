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
            $table->decimal('revised_amount', 15, 2)->nullable()->after('amount_approved');
            $table->decimal('projection_1', 15, 2)->nullable()->after('revised_amount');
            $table->decimal('projection_2', 15, 2)->nullable()->after('projection_1');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_estimations', function (Blueprint $table) {
            $table->dropColumn(['revised_amount', 'projection_1', 'projection_2']);
        });
    }
};
