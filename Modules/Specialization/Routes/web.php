<?php

use Illuminate\Support\Facades\Route;
use Modules\Specialization\Http\Controllers\SpecializationController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    Route::group(['prefix' => 'specializations', 'as' => 'specializations.'], function () {
        Route::get('index_data', [SpecializationController::class, 'index_data'])->name('index_data');
        Route::get('index_list', [SpecializationController::class, 'index_list'])->name('index_list');
        Route::post('bulk-action', [SpecializationController::class, 'bulk_action'])->name('bulk_action');
        Route::post('restore/{id}', [SpecializationController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{id}', [SpecializationController::class, 'forceDelete'])->name('force_delete');
        Route::post('update-status/{id}', [SpecializationController::class, 'update_status'])->name('update_status');
        Route::post('update-home-display/{id}', [SpecializationController::class, 'update_home_display'])->name('update_home_display');
    });
    Route::resource('specializations', SpecializationController::class);
});
