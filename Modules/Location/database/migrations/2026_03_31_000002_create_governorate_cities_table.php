<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('governorate_cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('governorate_id');
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('governorate_id')
                ->references('id')
                ->on('governorates')
                ->onDelete('cascade');

            $table->index('governorate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('governorate_cities');
    }
};
