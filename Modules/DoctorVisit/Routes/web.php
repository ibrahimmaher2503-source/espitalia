<?php

use Illuminate\Support\Facades\Route;
use Modules\DoctorVisit\Http\Controllers\Backend\DoctorVisitRequestController;

/*
|--------------------------------------------------------------------------
| Doctor Visit Web Routes (Admin Dashboard)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'auth_check'])->prefix('app/doctor-visit')->name('backend.doctor_visit.')->group(function () {
    Route::get('/', [DoctorVisitRequestController::class, 'index'])->name('index');
    Route::post('/index-data', [DoctorVisitRequestController::class, 'index_data'])->name('index_data');
    Route::get('/{id}', [DoctorVisitRequestController::class, 'show'])->name('show');
    Route::post('/update-status/{id}', [DoctorVisitRequestController::class, 'update_status'])->name('update_status');
    Route::post('/bulk-action', [DoctorVisitRequestController::class, 'bulk_action'])->name('bulk_action');
});
