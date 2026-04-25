<?php

use Illuminate\Support\Facades\Route;
use Modules\RadiologyLab\Http\Controllers\Backend\LabTestCategoriesController;
use Modules\RadiologyLab\Http\Controllers\Backend\LabTestsController;
use Modules\RadiologyLab\Http\Controllers\Backend\TestOrdersController;
use Modules\RadiologyLab\Http\Controllers\Backend\LabsController;
use Modules\RadiologyLab\Http\Controllers\Backend\RadiologyCentersController;
use Modules\RadiologyLab\Http\Controllers\Backend\FacilitySessionsController;
use Modules\RadiologyLab\Http\Controllers\Backend\FacilityBookingsController;

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
     * Lab Test Categories Management Routes
     */
    Route::group(['prefix' => 'lab-test-categories', 'as' => 'lab-test-categories.'], function () {
        Route::post('lab-test-categories-bulk-action', [LabTestCategoriesController::class, 'bulk_action'])->name('bulk_action');
        Route::get('lab-test-categories-index-data', [LabTestCategoriesController::class, 'index_data'])->name('index_data');
        Route::post('lab-test-categories-action', [LabTestCategoriesController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [LabTestCategoriesController::class, 'update_status'])->name('update_status');
    });
    Route::resource('lab-test-categories', LabTestCategoriesController::class)->except(['edit', 'update']);

    /*
     * Lab Tests Management Routes
     */
    Route::group(['prefix' => 'lab-tests', 'as' => 'lab-tests.'], function () {
        Route::post('lab-tests-bulk-action', [LabTestsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('lab-tests-index-data', [LabTestsController::class, 'index_data'])->name('index_data');
        Route::post('lab-tests-action', [LabTestsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [LabTestsController::class, 'update_status'])->name('update_status');
    });
    Route::resource('lab-tests', LabTestsController::class)->except(['edit', 'update']);

    /*
     * Test Orders Management Routes
     */
    Route::group(['prefix' => 'test-orders', 'as' => 'test-orders.'], function () {
        Route::post('test-orders-bulk-action', [TestOrdersController::class, 'bulk_action'])->name('bulk_action');
        Route::get('test-orders-index-data', [TestOrdersController::class, 'index_data'])->name('index_data');
        Route::post('test-orders-action', [TestOrdersController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [TestOrdersController::class, 'update_status'])->name('update_status');
        Route::post('assign-technician/{id}', [TestOrdersController::class, 'assign_technician'])->name('assign_technician');
        Route::post('enter-results/{id}', [TestOrdersController::class, 'enter_results'])->name('enter_results');
        Route::post('upload-report/{id}', [TestOrdersController::class, 'upload_report'])->name('upload_report');
        Route::post('generate-report/{id}', [TestOrdersController::class, 'generate_report'])->name('generate_report');
        Route::get('download-report/{reportId}', [TestOrdersController::class, 'download_report'])->name('download_report');
        Route::post('update-admin-notes/{id}', [TestOrdersController::class, 'update_admin_notes'])->name('update_admin_notes');
        Route::get('users-search', [TestOrdersController::class, 'users_search'])->name('users_search');
        Route::get('lab-tests-search', [TestOrdersController::class, 'lab_tests_search'])->name('lab_tests_search');
        Route::post('validate-coupon', [TestOrdersController::class, 'validate_coupon'])->middleware('throttle:30,1')->name('validate_coupon');
    });
    Route::resource('test-orders', TestOrdersController::class)->except(['edit', 'update']);

    /*
     * Labs Management Routes
     */
    Route::group(['prefix' => 'labs', 'as' => 'labs.'], function () {
        Route::post('labs-bulk-action', [LabsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('labs-index-data', [LabsController::class, 'index_data'])->name('index_data');
        Route::post('labs-action', [LabsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [LabsController::class, 'update_status'])->name('update_status');
    });
    Route::resource('labs', LabsController::class)->except(['edit', 'update']);

    /*
     * Radiology Centers Management Routes
     */
    Route::group(['prefix' => 'radiology-centers', 'as' => 'radiology-centers.'], function () {
        Route::post('radiology-centers-bulk-action', [RadiologyCentersController::class, 'bulk_action'])->name('bulk_action');
        Route::get('radiology-centers-index-data', [RadiologyCentersController::class, 'index_data'])->name('index_data');
        Route::post('radiology-centers-action', [RadiologyCentersController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [RadiologyCentersController::class, 'update_status'])->name('update_status');
    });
    Route::resource('radiology-centers', RadiologyCentersController::class)->except(['edit', 'update']);

    /*
     * Facility Sessions Management Routes
     */
    Route::group(['prefix' => 'facility-sessions', 'as' => 'facility-sessions.'], function () {
        Route::post('facility-sessions-bulk-action', [FacilitySessionsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('facility-sessions-index-data', [FacilitySessionsController::class, 'index_data'])->name('index_data');
        Route::post('facility-sessions-action', [FacilitySessionsController::class, 'action'])->name('action');
        Route::post('update-status/{id}', [FacilitySessionsController::class, 'update_status'])->name('update_status');
    });
    Route::resource('facility-sessions', FacilitySessionsController::class)->except(['edit', 'update']);

    /*
     * Facility Bookings Management Routes
     */
    Route::group(['prefix' => 'facility-bookings', 'as' => 'facility-bookings.'], function () {
        Route::post('facility-bookings-bulk-action', [FacilityBookingsController::class, 'bulk_action'])->name('bulk_action');
        Route::get('facility-bookings-index-data', [FacilityBookingsController::class, 'index_data'])->name('index_data');
        Route::post('facility-bookings-action', [FacilityBookingsController::class, 'action'])->name('action');
        Route::post('confirm/{id}', [FacilityBookingsController::class, 'confirm'])->name('confirm');
        Route::post('cancel/{id}', [FacilityBookingsController::class, 'cancel'])->name('cancel');
        Route::post('update-status/{id}', [FacilityBookingsController::class, 'update_status'])->name('update_status');
    });
    Route::resource('facility-bookings', FacilityBookingsController::class)->except(['edit', 'update', 'create', 'store']);
});
