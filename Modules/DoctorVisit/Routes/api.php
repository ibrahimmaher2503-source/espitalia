<?php

use Illuminate\Support\Facades\Route;
use Modules\DoctorVisit\Http\Controllers\API\AdminDoctorVisitApiController;
use Modules\DoctorVisit\Http\Controllers\API\DoctorVisitApiController;

/*
|--------------------------------------------------------------------------
| Doctor Visit API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    // ── Patient-facing routes (US1 + US2) ─────────────────────────
    Route::post('doctor-visit/requests', [DoctorVisitApiController::class, 'store'])
        ->name('api.doctor_visit.store');

    Route::get('doctor-visit/requests', [DoctorVisitApiController::class, 'index'])
        ->name('api.doctor_visit.index');

    Route::get('doctor-visit/requests/{reference}', [DoctorVisitApiController::class, 'show'])
        ->name('api.doctor_visit.show');

    // ── Admin/Staff routes (US3) ───────────────────────────────────
    Route::prefix('admin/doctor-visit/requests')->group(function () {
        Route::get('/', [AdminDoctorVisitApiController::class, 'index'])
            ->name('api.admin.doctor_visit.index');

        Route::put('{reference}/status', [AdminDoctorVisitApiController::class, 'updateStatus'])
            ->name('api.admin.doctor_visit.update_status');

        Route::put('{reference}/assign-doctor', [AdminDoctorVisitApiController::class, 'assignDoctor'])
            ->name('api.admin.doctor_visit.assign_doctor');

        Route::post('{reference}/notes', [AdminDoctorVisitApiController::class, 'addNote'])
            ->name('api.admin.doctor_visit.add_note');
    });
});
