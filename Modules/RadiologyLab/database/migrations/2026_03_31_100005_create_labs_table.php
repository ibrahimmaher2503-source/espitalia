<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name_ar', 255);
            $table->string('name_en', 255);
            $table->unsignedBigInteger('governorate_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('governorate_id')
                  ->references('id')
                  ->on('governorates')
                  ->onDelete('set null');

            $table->foreign('city_id')
                  ->references('id')
                  ->on('governorate_cities')
                  ->onDelete('set null');

            $table->index('governorate_id');
            $table->index('city_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
