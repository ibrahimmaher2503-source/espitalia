<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('test_order_id');
            $table->string('report_type')->default('lab_report');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->date('report_date');
            $table->string('status')->default('draft');
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('test_order_id')->references('id')->on('test_orders')->onDelete('cascade');
            $table->foreign('generated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_reports');
    }
};
