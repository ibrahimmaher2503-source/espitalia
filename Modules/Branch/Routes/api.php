<?php

use Illuminate\Support\Facades\Route;
use Modules\Branch\Http\Controllers\Backend\API\BranchesApiController;

// Public — browseable without auth
Route::prefix('v1')->name('api.')->group(function () {
    Route::get('branches', [BranchesApiController::class, 'index'])->name('branches.index');
    Route::get('branches/{id}', [BranchesApiController::class, 'show'])->name('branches.show');
});

// Protected — require authentication
Route::middleware('auth:sanctum')->prefix('v1')->name('api.')->group(function () {
    Route::post('branches', [BranchesApiController::class, 'store'])->name('branches.store');
    Route::put('branches/{id}', [BranchesApiController::class, 'update'])->name('branches.update');
    Route::delete('branches/{id}', [BranchesApiController::class, 'destroy'])->name('branches.destroy');
    Route::post('branches/{id}/update-status', [BranchesApiController::class, 'updateStatus'])->name('branches.update-status');
});
