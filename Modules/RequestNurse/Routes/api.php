<?php

use Illuminate\Support\Facades\Route;
use Modules\RequestNurse\Http\Controllers\API\NurseSearchApiController;
use Modules\RequestNurse\Http\Controllers\Backend\API\NurseRequestApiController;
use Modules\RequestNurse\Http\Controllers\Backend\API\NursesApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public nurse search (no auth required)
Route::get('nurses/search', [NurseSearchApiController::class, 'search']);

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {

    // Nurse Requests
    Route::apiResource('nurse-requests', NurseRequestApiController::class);
    Route::post('nurse-requests/{id}/cancel', [NurseRequestApiController::class, 'cancel'])->name('nurse-requests.cancel');

    // Nurses (read-only for API consumers)
    Route::get('nurses', [NursesApiController::class, 'index'])->name('nurses.index');
    Route::get('nurses/{id}', [NursesApiController::class, 'show'])->name('nurses.show');
});
