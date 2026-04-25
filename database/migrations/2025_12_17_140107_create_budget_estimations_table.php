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
        Schema::create('budget_estimations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id');
            $table->foreignId('rpo_unit_id');
            $table->foreignId('economic_code_id');
            $table->decimal('amount_demand', 15, 2)->default(0);
            $table->decimal('amount_approved', 15, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_estimations');
    }
};
