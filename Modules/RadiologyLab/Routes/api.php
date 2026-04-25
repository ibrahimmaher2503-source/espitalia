<?php

use Illuminate\Support\Facades\Route;
use Modules\RadiologyLab\Http\Controllers\Backend\API\LabTestsApiController;
use Modules\RadiologyLab\Http\Controllers\Backend\API\TestOrdersApiController;
use Modules\RadiologyLab\Http\Controllers\API\LabSearchApiController;
use Modules\RadiologyLab\Http\Controllers\API\RadiologySearchApiController;
use Modules\RadiologyLab\Http\Controllers\API\FacilityBookingApiController;
use Modules\RadiologyLab\Http\Controllers\API\AdminFacilityBookingApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Lab & Radiology search (public — no auth required, rate-limited — VL-15)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('labs/search', [LabSearchApiController::class, 'search']);
    Route::get('radiology/search', [RadiologySearchApiController::class, 'search']);
});

// Lab Tests catalog (public — browse before login, rate-limited)
Route::middleware('throttle:120,1')->prefix('v1')->group(function () {
    Route::get('/lab-tests', [LabTestsApiController::class, 'index']);
    Route::get('/lab-tests/{id}', [LabTestsApiController::class, 'show']);
    Route::get('/lab-test-categories', [LabTestsApiController::class, 'categories']);
});

// Test Orders (authenticated)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/test-orders', [TestOrdersApiController::class, 'index']);
    Route::post('/test-orders', [TestOrdersApiController::class, 'store']);
    Route::get('/test-orders/{id}', [TestOrdersApiController::class, 'show']);
    Route::post('/test-orders/{id}/cancel', [TestOrdersApiController::class, 'cancel']);
    Route::get('/test-orders/{id}/report/download', [TestOrdersApiController::class, 'downloadReport']);
});

// Facility Bookings — public slot endpoints (rate-limited)
Route::middleware('throttle:120,1')->prefix('v1')->group(function () {
    Route::get('/facility-bookings/labs/{labId}/slots', [FacilityBookingApiController::class, 'labSlots']);
    Route::get('/facility-bookings/radiology-centers/{centerId}/slots', [FacilityBookingApiController::class, 'radiologyCenterSlots']);
});

// Facility Bookings — patient endpoints (authenticated)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::post('/facility-bookings', [FacilityBookingApiController::class, 'store']);
    Route::get('/facility-bookings', [FacilityBookingApiController::class, 'index']);
    Route::get('/facility-bookings/{id}', [FacilityBookingApiController::class, 'show']);
    Route::post('/facility-bookings/{id}/cancel', [FacilityBookingApiController::class, 'cancel']);
});

// Facility Bookings — admin endpoints (authenticated + permission)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/admin/facility-bookings', [AdminFacilityBookingApiController::class, 'index']);
    Route::post('/admin/facility-bookings/{id}/confirm', [AdminFacilityBookingApiController::class, 'confirm']);
    Route::post('/admin/facility-bookings/{id}/cancel', [AdminFacilityBookingApiController::class, 'cancel']);
    Route::post('/admin/facility-bookings/{id}/status', [AdminFacilityBookingApiController::class, 'updateStatus']);
});
