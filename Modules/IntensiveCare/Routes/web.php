<?php

use Illuminate\Support\Facades\Route;
use Modules\IntensiveCare\Http\Controllers\Backend\HospitalsController;
use Modules\IntensiveCare\Http\Controllers\Backend\IcuAdmissionRequestsController;
use Modules\IntensiveCare\Http\Controllers\Backend\IcuCommissionController;
use Modules\IntensiveCare\Http\Controllers\Backend\IcuDepartmentsController;
use Modules\IntensiveCare\Http\Controllers\Backend\IcuEarningController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'app', 'as' => 'backend.', 'middleware' => ['auth', 'auth_check']], function () {

    /*
     * Hospitals Management Routes
     */
    Route::group(['prefix' => 'hospitals', 'as' => 'hospitals.'], function () {
        Route::post('hospitals-bulk-action', [HospitalsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('hospitals-index-data', [HospitalsController::class, 'index_data'])->name('index_data');
        Route::post('hospitals-action', [HospitalsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [HospitalsController::class, 'update_status'])->name('update_status');
    });
    Route::resource('hospitals', HospitalsController::class)->except(['edit', 'update']);

    /*
     * ICU Departments Management Routes
     */
    Route::group(['prefix' => 'icu-departments', 'as' => 'icu-departments.'], function () {
        Route::post('icu-departments-bulk-action', [IcuDepartmentsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('icu-departments-index-data', [IcuDepartmentsController::class, 'index_data'])->name('index_data');
        Route::post('icu-departments-action', [IcuDepartmentsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [IcuDepartmentsController::class, 'update_status'])->name('update_status');
        Route::post('update-beds/{id}', [IcuDepartmentsController::class, 'update_beds'])->name('update_beds');
    });
    Route::resource('icu-departments', IcuDepartmentsController::class)->except(['edit', 'update']);

    /*
     * ICU Admission Requests Management Routes
     */
    Route::group(['prefix' => 'icu-admission-requests', 'as' => 'icu-admission-requests.'], function () {
        Route::post('icu-admission-requests-bulk-action', [IcuAdmissionRequestsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('icu-admission-requests-index-data', [IcuAdmissionRequestsController::class, 'index_data'])->name('index_data');
        Route::post('icu-admission-requests-action', [IcuAdmissionRequestsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [IcuAdmissionRequestsController::class, 'update_status'])->name('update_status');
        Route::post('respond/{id}', [IcuAdmissionRequestsController::class, 'respond'])->name('respond');
    });
    Route::resource('icu-admission-requests', IcuAdmissionRequestsController::class)->except(['edit', 'update']);

    /*
     * ICU Commissions Management Routes
     */
    Route::group(['prefix' => 'icu-commissions', 'as' => 'icu-commissions.'], function () {
        Route::get('index_data', [IcuCommissionController::class, 'index_data'])->name('index_data');
        Route::post('bulk_action', [IcuCommissionController::class, 'bulk_action'])->name('bulk_action');
        Route::post('update-status/{id}', [IcuCommissionController::class, 'update_status'])->name('update_status');
    });
    Route::resource('icu-commissions', IcuCommissionController::class);

    /*
     * ICU Earnings Management Routes
     */
    Route::get('icu-earnings', [IcuEarningController::class, 'index'])->name('icu-earnings.index');
    Route::get('icu-earnings/index-data', [IcuEarningController::class, 'index_data'])->name('icu-earnings.index_data');
    Route::get('icu-earnings/{hospitalId}', [IcuEarningController::class, 'show'])->name('icu-earnings.show');
    Route::get('icu-earnings/{hospitalId}/data', [IcuEarningController::class, 'show_data'])->name('icu-earnings.show_data');
    Route::get('icu-earnings/{hospitalId}/payouts', [IcuEarningController::class, 'payouts_data'])->name('icu-earnings.payouts_data');
    Route::post('icu-earnings/{hospitalId}/payout', [IcuEarningController::class, 'payout'])->name('icu-earnings.payout');
});
