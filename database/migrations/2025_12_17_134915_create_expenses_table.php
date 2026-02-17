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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->date('date');
            $table->foreignId('expense_category_id')/* /* /* ->constrained() */ */ *//* /* /* ->onDelete('cascade') */ */ */;
            // Assuming we need to link it to an office and fiscal year
            $table->foreignId('rpo_unit_id')/* /* /* ->constrained('rpo_units') */ */ *//* /* /* ->onDelete('cascade') */ */ */;
            $table->foreignId('fiscal_year_id')->nullable()/* /* /* ->constrained('fiscal_years') */ */ *//* /* /* ->onDelete('set null') */ */ */;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
