<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('icu_admission_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();

            // Relationships
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('hospital_id');
            $table->unsignedBigInteger('icu_department_id')->nullable();

            // Patient info
            $table->string('patient_name');
            $table->integer('patient_age');
            $table->string('patient_gender');
            $table->string('national_id')->nullable();
            $table->string('insurance_number')->nullable();
            $table->text('medical_condition');
            $table->string('diagnosis')->nullable();

            // Case details
            $table->string('case_type');
            $table->string('urgency')->default('standard');
            $table->boolean('needs_ventilator')->default(false);
            $table->boolean('needs_oxygen')->default(false);
            $table->string('current_location')->nullable();
            $table->boolean('needs_ambulance')->default(false);

            // Contact
            $table->string('contact_name');
            $table->string('contact_phone');
            $table->string('relationship_to_patient')->nullable();
            $table->string('payment_method')->default('cash');
            $table->string('insurance_provider')->nullable();

            // Workflow
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->datetime('response_date')->nullable();
            $table->unsignedBigInteger('responded_by')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Audit
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('restrict');
            $table->foreign('icu_department_id')->references('id')->on('icu_departments')->onDelete('set null');
            $table->foreign('responded_by')->references('id')->on('users')->onDelete('set null');
            $table->index('status');
            $table->index('patient_id');
            $table->index('hospital_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('icu_admission_requests');
    }
};
