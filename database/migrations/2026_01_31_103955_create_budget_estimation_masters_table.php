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
        Schema::create('budget_estimation_masters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fiscal_year_id');
            $table->unsignedBigInteger('rpo_unit_id');
            $table->unsignedBigInteger('budget_type_id');
            $table->unsignedBigInteger('workflow_step_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('batch_no')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_estimation_masters');
    }
};
