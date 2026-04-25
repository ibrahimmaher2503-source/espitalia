<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number', 20)->unique();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pharmacy_id')->nullable();
            $table->string('doctor_name', 255)->nullable();
            $table->string('doctor_license_number', 50)->nullable();
            $table->string('patient_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')->references('id')->on('pharmacy_orders')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('set null');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_prescriptions');
    }
};
