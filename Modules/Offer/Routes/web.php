<?php

use Illuminate\Support\Facades\Route;
use Modules\Offer\Http\Controllers\Backend\OfferController;
use Modules\Offer\Http\Controllers\Backend\OfferAnalyticsController;
use Modules\Offer\Http\Controllers\Backend\OfferUsageLogController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {

    // Offers CRUD
    Route::get('offers/index-data', [OfferController::class, 'index_data'])->name('offers.index_data');
    Route::post('offers/bulk-action', [OfferController::class, 'bulk_action'])->name('offers.bulk_action');
    Route::post('offers/{id}/update-status', [OfferController::class, 'update_status'])->name('offers.update_status');
    Route::post('offers/{id}/duplicate', [OfferController::class, 'duplicate'])->name('offers.duplicate');
    Route::resource('offers', OfferController::class);

    // Offer Analytics
    Route::get('offer-analytics', [OfferAnalyticsController::class, 'index'])->name('offer-analytics.index');
    Route::get('offer-analytics/data', [OfferAnalyticsController::class, 'data'])->name('offer-analytics.data');

    // Offer Usage Logs
    Route::get('offer-usage-logs', [OfferUsageLogController::class, 'index'])->name('offer-usage-logs.index');
    Route::get('offer-usage-logs/index-data', [OfferUsageLogController::class, 'index_data'])->name('offer-usage-logs.index_data');
});
