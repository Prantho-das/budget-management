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
        Schema::table('ministry_allocations', function (Blueprint $table) {
            $table->string('budget_type')->default('original')->after('remarks'); // original, revised
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ministry_allocations', function (Blueprint $table) {
            $table->dropColumn('budget_type');
        });
    }
};
