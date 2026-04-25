<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_independent_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('duration_min')->default(30);
            $table->integer('time_slot')->default(10);
            $table->double('charges', 8, 2)->default(0);
            $table->tinyInteger('discount')->default(0);
            $table->string('discount_type', 20)->nullable();
            $table->double('discount_value', 8, 2)->default(0);
            $table->tinyInteger('is_inclusive_tax')->default(0);
            $table->string('inclusive_tax', 255)->nullable();
            $table->double('inclusive_tax_price', 8, 2)->default(0);
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_independent_services');
    }
};
