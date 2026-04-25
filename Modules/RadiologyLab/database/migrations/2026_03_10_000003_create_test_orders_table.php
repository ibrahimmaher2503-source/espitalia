<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('lab_technician_id')->nullable();
            $table->text('clinical_notes')->nullable();
            $table->string('priority')->default('routine');
            $table->date('order_date');
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->double('total_amount')->default(0);
            $table->double('discount_amount')->default(0);
            $table->double('final_amount')->default(0);
            $table->text('admin_notes')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('lab_technician_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_orders');
    }
};
