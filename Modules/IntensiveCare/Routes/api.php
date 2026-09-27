<?php

use Illuminate\Support\Facades\Route;
use Modules\IntensiveCare\Http\Controllers\Backend\API\HospitalsApiController;
use Modules\IntensiveCare\Http\Controllers\Backend\API\IcuAdmissionRequestsApiController;
use Modules\IntensiveCare\Http\Controllers\Backend\API\IcuCommissionsApiController;
use Modules\IntensiveCare\Http\Controllers\Backend\API\IcuEarningsApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public — hospital catalogue browseable without auth
Route::prefix('v1')->group(function () {
    Route::get('/hospitals', [HospitalsApiController::class, 'index']);
    Route::get('/hospitals/{id}', [HospitalsApiController::class, 'show']);
    Route::get('/icu-departments', [HospitalsApiController::class, 'departments']);
});

// Protected — require authentication
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // ICU Admission Requests
    Route::get('/icu-admission-requests', [IcuAdmissionRequestsApiController::class, 'index']);
    Route::post('/icu-admission-requests', [IcuAdmissionRequestsApiController::class, 'store'])
        ->middleware('idempotency:icu_admission');
    Route::get('/icu-admission-requests/{id}', [IcuAdmissionRequestsApiController::class, 'show']);
    Route::post('/icu-admission-requests/{id}/cancel', [IcuAdmissionRequestsApiController::class, 'cancel'])
        ->middleware('idempotency:icu_admission_cancellation');

    // ICU Commissions (admin only)
    Route::get('/icu-commissions', [IcuCommissionsApiController::class, 'index']);

    // ICU Earnings (admin + hospital coordinator)
    Route::get('/icu-earnings', [IcuEarningsApiController::class, 'index']);
});
