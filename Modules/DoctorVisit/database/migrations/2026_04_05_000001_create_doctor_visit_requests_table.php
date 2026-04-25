<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_visit_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 20)->unique()->comment('Format: VR-YYYY-NNNN');
            $table->unsignedBigInteger('patient_id')->comment('FK to users.id');
            $table->unsignedBigInteger('preferred_doctor_id')->nullable()->comment('FK to doctors.id - patient preference at submission');
            $table->unsignedBigInteger('assigned_doctor_id')->nullable()->comment('FK to doctors.id - set by admin/receptionist');
            $table->string('visit_type')->default('home_visit')->comment('Fixed: home_visit');
            $table->text('visit_reason')->comment('Chief complaint / reason for visit');
            $table->date('preferred_date')->comment('Date-only; no time slot at submission');
            $table->string('contact_phone', 20);
            $table->text('additional_notes')->nullable();
            $table->string('status')->default('pending')->comment('pending, confirmed, cancelled, completed');
            $table->text('admin_notes')->nullable()->comment('Internal notes visible to admin/receptionist/doctor');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('patient_id');
            $table->index('status');
            $table->index('preferred_date');
            $table->index('assigned_doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_visit_requests');
    }
};
