<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facility_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number', 20)->unique();
            $table->enum('type', ['lab', 'radiology']);
            $table->unsignedBigInteger('lab_id')->nullable();
            $table->unsignedBigInteger('radiology_center_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->string('patient_name', 255);
            $table->string('patient_phone', 50);
            $table->string('scan_type', 255)->nullable();
            $table->date('booking_date');
            $table->time('booking_time');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->string('cancelled_by', 50)->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lab_id')->references('id')->on('labs')->onDelete('set null');
            $table->foreign('radiology_center_id')->references('id')->on('radiology_centers')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');

            $table->index('patient_id');
            $table->index('booking_date');
            $table->index('status');
            $table->index(['type', 'lab_id']);
            $table->index(['type', 'radiology_center_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_bookings');
    }
};
