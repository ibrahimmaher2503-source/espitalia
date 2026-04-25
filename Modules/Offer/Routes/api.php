<?php

use Illuminate\Support\Facades\Route;
use Modules\Offer\Http\Controllers\Backend\API\OfferApiController;
use Modules\Offer\Http\Controllers\Backend\API\PublicOfferApiController;

// Public (no auth)
Route::prefix('v1')->group(function () {
    Route::get('offers/public', [PublicOfferApiController::class, 'publicIndex']);
    Route::get('offers/public/{slug}', [PublicOfferApiController::class, 'publicShow']);
    Route::get('services/{serviceId}/offers', [PublicOfferApiController::class, 'serviceOffers']);
});

// Authenticated
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {

    // Customer-facing (literal paths BEFORE parameterized)
    Route::post('offers/validate-coupon', [PublicOfferApiController::class, 'validateCoupon']);
    Route::post('offers/calculate-discount', [PublicOfferApiController::class, 'calculateDiscount']);
    Route::post('offers/apply', [PublicOfferApiController::class, 'apply']);
    Route::post('offers/remove', [PublicOfferApiController::class, 'remove']);

    // Admin API CRUD — literal routes first, then parameterized with regex
    Route::get('offers', [OfferApiController::class, 'index']);
    Route::post('offers', [OfferApiController::class, 'store']);
    Route::post('offers/bulk-action', [OfferApiController::class, 'bulkAction']);
    Route::get('offers/usage-logs/all', [OfferApiController::class, 'allUsageLogs']);

    // Parameterized routes with numeric constraint
    Route::get('offers/{id}', [OfferApiController::class, 'show'])->where('id', '[0-9]+');
    Route::put('offers/{id}', [OfferApiController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('offers/{id}', [OfferApiController::class, 'destroy'])->where('id', '[0-9]+');
    Route::post('offers/{id}/update-status', [OfferApiController::class, 'updateStatus'])->where('id', '[0-9]+');
    Route::get('offers/{id}/usage-logs', [OfferApiController::class, 'usageLogs'])->where('id', '[0-9]+');
    Route::post('offers/{id}/duplicate', [OfferApiController::class, 'duplicate'])->where('id', '[0-9]+');
});
