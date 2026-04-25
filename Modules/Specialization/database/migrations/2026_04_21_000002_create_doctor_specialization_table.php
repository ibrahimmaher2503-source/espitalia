<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_specialization', function (Blueprint $table) {
            $table->unsignedBigInteger('specialization_id');
            $table->unsignedBigInteger('doctor_id');
            $table->timestamps();

            $table->primary(['specialization_id', 'doctor_id']);
            $table->unique(['doctor_id', 'specialization_id'], 'doctor_spec_unique');

            $table->foreign('specialization_id')->references('id')->on('specializations')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_specialization');
    }
};
