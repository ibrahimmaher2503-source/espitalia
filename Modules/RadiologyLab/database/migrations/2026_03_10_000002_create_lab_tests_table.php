<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('sample_type')->nullable();
            $table->string('department')->default('laboratory');
            $table->text('preparation_instructions')->nullable();
            $table->double('default_price', 8, 2)->default(0);
            $table->string('turnaround_time')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('category_id')->references('id')->on('lab_test_categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
