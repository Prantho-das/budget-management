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
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->onDelete('cascade');
            $table->foreignId('rpo_unit_id')->constrained('rpo_units')->onDelete('cascade');
            $table->foreignId('budget_type_id')->constrained('budget_types')->onDelete('cascade');
            $table->foreignId('workflow_step_id')->nullable()->constrained('workflow_steps');
            $table->foreignId('created_by')->nullable()->constrained('users');
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
