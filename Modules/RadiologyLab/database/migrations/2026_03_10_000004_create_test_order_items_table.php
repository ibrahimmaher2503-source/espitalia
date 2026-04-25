<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_order_id');
            $table->unsignedBigInteger('lab_test_id');
            $table->double('price', 8, 2)->default(0);
            $table->string('status')->default('pending');
            $table->text('result_value')->nullable();
            $table->string('result_unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->string('result_status')->nullable();
            $table->text('result_notes')->nullable();
            $table->dateTime('result_date')->nullable();
            $table->unsignedBigInteger('result_by')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('test_order_id')->references('id')->on('test_orders')->onDelete('cascade');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('cascade');
            $table->foreign('result_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_order_items');
    }
};
