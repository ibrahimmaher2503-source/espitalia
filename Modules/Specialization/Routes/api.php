<?php

use Illuminate\Support\Facades\Route;
use Modules\Specialization\Http\Controllers\API\SpecializationController;

Route::prefix('v1')->name('api.')->group(function () {
    Route::get('specialties/home-config', [SpecializationController::class, 'homeConfig'])->name('specialties.home-config');
});

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('specializations', [SpecializationController::class, 'index'])->name('specializations.index');
    Route::get('specializations/{id}', [SpecializationController::class, 'show'])->name('specializations.show');
    Route::get('specializations/{id}/doctors', [SpecializationController::class, 'doctors'])->name('specializations.doctors');
    Route::get('specializations/{id}/nurses', [SpecializationController::class, 'nurses'])->name('specializations.nurses');
});
