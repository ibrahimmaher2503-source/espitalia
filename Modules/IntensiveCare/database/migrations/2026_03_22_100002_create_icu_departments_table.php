<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hospital_id');
            $table->string('name');
            $table->string('specialty_type')->default('general');
            $table->integer('total_beds')->default(0);
            $table->integer('available_beds')->default(0);
            $table->boolean('has_ventilator')->default(false);
            $table->boolean('has_oxygen')->default(true);
            $table->string('equipment_level')->default('basic');
            $table->decimal('daily_price', 8, 2)->default(0);
            $table->text('description')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
            $table->index('status');
            $table->index('specialty_type');
            $table->index('available_beds');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_departments');
    }
};
