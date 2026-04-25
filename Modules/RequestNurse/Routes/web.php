<?php

use Illuminate\Support\Facades\Route;
use Modules\RequestNurse\Http\Controllers\Backend\NurseRequestsController;
use Modules\RequestNurse\Http\Controllers\Backend\NursesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {

    /*
     * Nurses Management Routes
     */
    Route::group(['prefix' => 'nurses', 'as' => 'nurses.'], function () {
        Route::post('nurses-bulk-action', [NursesController::class, 'bulk_action'])->name('bulk_action');
        Route::get('nurses-index-data', [NursesController::class, 'index_data'])->name('index_data');
        Route::post('nurses-action', [NursesController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [NursesController::class, 'update_status'])->name('update_status');
    });
    Route::resource('nurses', NursesController::class);

    /*
     * Nurse Requests Management Routes
     */
    Route::group(['prefix' => 'nurse-requests', 'as' => 'nurse-requests.'], function () {
        Route::post('nurse-requests-bulk-action', [NurseRequestsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('nurse-requests-index-data', [NurseRequestsController::class, 'index_data'])->name('index_data');
        Route::post('nurse-requests-action', [NurseRequestsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [NurseRequestsController::class, 'update_status'])->name('update_status');
        Route::post('assign-nurse/{id}', [NurseRequestsController::class, 'assign_nurse'])->name('assign_nurse');
    });
    Route::resource('nurse-requests', NurseRequestsController::class);
});
