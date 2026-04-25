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
        Schema::create('user_office_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('from_office_id')->nullable();
            $table->foreignId('to_office_id');
            $table->date('transfer_date');
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_office_transfers');
    }
};
