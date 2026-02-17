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
        Schema::create('ministry_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')/* /* /* ->constrained('fiscal_years') */ */ *//* /* /* ->onDelete('cascade') */ */ */;
            $table->foreignId('rpo_unit_id')/* /* /* ->constrained('rpo_units') */ */ *//* /* /* ->onDelete('cascade') */ */ */;
            $table->foreignId('economic_code_id')/* /* /* ->constrained('economic_codes') */ */ *//* /* /* ->onDelete('cascade') */ */ */;
            $table->decimal('amount', 15, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()/* /* /* ->constrained('users') */ */ *//* /* /* ->onDelete('set null') */ */ */;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ministry_allocations');
    }
};
