<?php

use Illuminate\Support\Facades\Route;
use Modules\Appointment\Http\Controllers\Backend\API\AppointmentsController;
use Modules\Appointment\Http\Controllers\Backend\API\PatientEncounterController;
use Modules\Appointment\Http\Controllers\Backend\API\EncounterDashboardController;



Route::group(['middleware' => 'auth:sanctum'], function () {

    // Patients create their own booking; ownership and pricing are enforced
    // inside the controller, while idempotency protects retries.
    Route::post('save-booking', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'store'])
        ->middleware('idempotency:appointment');
    Route::post('update-booking/{id}', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'update'])->middleware('api_permission:edit_clinic_appointment_list');
    Route::get('appointment-list', [AppointmentsController::class, 'appointmentList']);
    Route::get('appointment-detail', [AppointmentsController::class, 'appointmentDetails']);
    Route::post('save-payment', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'savePayment'])
        ->middleware('idempotency:payment');
    Route::post('update-status/{id}', [Modules\Appointment\Http\Controllers\Backend\AppointmentsController::class, 'updateStatus'])->middleware('api_permission:edit_clinic_appointment_list');
    // Patients reschedule their own appointments; the controller enforces ownership and status.
    Route::post('reschedule-booking', [AppointmentsController::class, 'rescheduleBooking'])
        ->middleware('idempotency:appointment-reschedule');
    Route::post('cancel-appointment/{id}', [AppointmentsController::class, 'cancelAppointment'])->middleware('api_permission:edit_clinic_appointment_list');

    Route::get('encounter-list', [PatientEncounterController::class, 'encounterList']);
    Route::post('save-encounter', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'store'])
        ->middleware(['api_permission:add_encounter', 'idempotency:encounter']);
    Route::post('update-encounter/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'update'])->middleware('api_permission:edit_encounter');
    Route::post('delete-encounter/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'destroy'])->middleware('api_permission:delete_encounter');
    Route::get('encounter-details', [PatientEncounterController::class, 'encounterList']);
    Route::get('download-encounter-invoice', [PatientEncounterController::class, 'encounterInvoice'])->middleware('api_permission:view_encounter');
    Route::get('download-prescription', [PatientEncounterController::class, 'downloadPrescription'])->middleware('api_permission:view_encounter');

    Route::get('encounter-dropdown-list', [EncounterDashboardController::class, 'encounterDropdownList'])->middleware('api_permission:view_encounter');

    Route::get('get-medical-report', [EncounterDashboardController::class, 'GetMedicalReport'])->middleware('api_permission:view_encounter');
    Route::post('save-medical-report', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'saveMedicalReport'])
        ->middleware(['api_permission:add_encounter', 'idempotency:medical_report']);

    Route::post('update-medical-report/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'updateMedicalReport'])->middleware('api_permission:edit_encounter');
    Route::get('delete-medical-report/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'deleteMedicalReport'])->middleware('api_permission:delete_encounter');

    Route::get('get-prescription', [EncounterDashboardController::class, 'GetPrescription'])->middleware('api_permission:view_encounter');
    Route::post('save-prescription', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'saveMedicalReport'])
        ->middleware(['api_permission:add_encounter', 'idempotency:prescription']);

    Route::post('update-prescription/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'updateMedicalReport'])->middleware('api_permission:edit_encounter');
    Route::get('delete-prescription/{id}', [Modules\Appointment\Http\Controllers\Backend\PatientEncounterController::class, 'deleteMedicalReport'])->middleware('api_permission:delete_encounter');

    Route::post('save-encounter-dashboard', [EncounterDashboardController::class, 'saveEncounterDashboard'])
        ->middleware(['api_permission:edit_encounter', 'idempotency:encounter_dashboard']);
    Route::get('encounter-dashboard-detail', [EncounterDashboardController::class, 'encounterDashboardDetail'])->middleware('api_permission:view_encounter');
    Route::get('encounter-service-detail', [EncounterDashboardController::class, 'encounterServiceDetails'])->middleware('api_permission:view_encounter');

    Route::get('download_invoice', [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'downloadPDf'])->name('download_invoice');

    Route::post("save-bodychart", [EncounterDashboardController::class, 'saveBodychart']);
    Route::post("update-bodychart/{id}", [EncounterDashboardController::class, 'updateBodychart']);
    Route::post('delete-bodychart/{id}', [EncounterDashboardController::class, 'deleteBodychart']);
    Route::get('bodychart-list', [EncounterDashboardController::class, 'bodyChartList']);

    Route::post('save-billing-details', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'saveBillingDetails']);
    Route::get('billing-list', [EncounterDashboardController::class, 'billingList']);
    Route::get('billing-record-detail', [EncounterDashboardController::class, 'billingList']);

    Route::get('get-revenue-chart-data', [AppointmentsController::class, 'getRevenuechartData']);
    Route::post("save-soap/{id}", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient'])->name("save_soap");
    Route::get("get-soap/{id}", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient_data'])->name("get_soap");
    Route::put("save-appointment_patient/{id}", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient'])->name("appointment_patient");
    Route::get("{id}/get_appointment_patient_data", [Modules\Appointment\Http\Controllers\Backend\ClinicAppointmentController::class, 'appointment_patient_data'])->name("appointment_patient_data");

    Route::post('/save-billing-items', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'saveBillingItems']);
    Route::get('billing-item-list', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'billing_item_list'])->name('billing_item_list');
    Route::get('billing-item-details', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'billing_item_detail'])->name('billing_item_detail');
    Route::get('edit-billing-item/{id}', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'editBillingItem']);
    Route::post('delete-billing-item/{id}', [Modules\Appointment\Http\Controllers\Backend\BillingRecordController::class, 'deleteBillingItem']);
});
