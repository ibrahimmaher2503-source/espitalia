<?php

use Illuminate\Support\Facades\Route;
use Modules\HomeHealthcare\Http\Controllers\HomeHealthcareController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    Route::group(['prefix' => 'home-healthcares', 'as' => 'home-healthcares.'], function () {
        Route::get('index_data', [HomeHealthcareController::class, 'index_data'])->name('index_data');
        Route::post('bulk_action', [HomeHealthcareController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update_status/{id}', [HomeHealthcareController::class, 'update_status'])->name('update_status');
    });
    Route::resource('home-healthcares', HomeHealthcareController::class);
});
