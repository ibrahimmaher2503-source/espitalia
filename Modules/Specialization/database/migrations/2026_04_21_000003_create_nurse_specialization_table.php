<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nurse_specialization', function (Blueprint $table) {
            $table->unsignedBigInteger('specialization_id');
            $table->unsignedBigInteger('nurse_id');
            $table->timestamps();

            $table->primary(['specialization_id', 'nurse_id']);
            $table->unique(['nurse_id', 'specialization_id'], 'nurse_spec_unique');

            $table->foreign('specialization_id')->references('id')->on('specializations')->onDelete('cascade');
            $table->foreign('nurse_id')->references('id')->on('nurses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurse_specialization');
    }
};
