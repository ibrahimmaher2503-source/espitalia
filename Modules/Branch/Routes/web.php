<?php

use Illuminate\Support\Facades\Route;
use Modules\Branch\Http\Controllers\Backend\BranchesController;

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {
    Route::get('branches', [BranchesController::class, 'index'])->name('branches.index');
    Route::get('branches/index_data', [BranchesController::class, 'index_data'])->name('branches.index_data');
    Route::get('branches/create', [BranchesController::class, 'create'])->name('branches.create');
    Route::post('branches', [BranchesController::class, 'store'])->name('branches.store');
    Route::get('branches/{id}/edit', [BranchesController::class, 'edit'])->name('branches.edit');
    Route::post('branches/{id}', [BranchesController::class, 'update'])->name('branches.update');
    Route::delete('branches/{id}', [BranchesController::class, 'destroy'])->name('branches.destroy');
    Route::post('branches/{id}/update-status', [BranchesController::class, 'update_status'])->name('branches.update_status');
    Route::post('branches/bulk-action', [BranchesController::class, 'bulk_action'])->name('branches.bulk_action');
});
