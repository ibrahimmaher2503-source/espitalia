<?php

use App\Http\Controllers\Auth\API\AuthController;
use App\Http\Controllers\Backend\API\AddressController;
use App\Http\Controllers\Backend\API\DashboardController;
use App\Http\Controllers\Backend\API\DBBackupController;
use App\Http\Controllers\Backend\API\IdempotencyController;
use App\Http\Controllers\Backend\API\NotificationsController;
use App\Http\Controllers\Backend\API\SettingController;
use App\Http\Controllers\Backend\SettingController as BackendSettingController;
use App\Http\Controllers\Backend\BackupController;
use App\Http\Controllers\IncidenceController;
use App\Http\Controllers\SearchController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('user-detail', [AuthController::class, 'userDetails']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('v1/idempotency/status', [IdempotencyController::class, 'status']);

Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('api.register');
    Route::post('login', 'login');
    Route::post('social-login', 'socialLogin');
    Route::post('forgot-password', 'forgotPassword');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});
Route::post('check-contact', [AuthController::class, 'checkContact']);
Route::post('check-email', [AuthController::class, 'checkEmail']);

Route::get('dashboard-detail', [DashboardController::class, 'DashboardDetail']);

Route::group(['middleware' => 'auth:sanctum'], function () {

    ### database backup 
    Route::get('backup', [DBBackupController::class, 'index']);
    Route::post('download-backup-files', [DBBackupController::class, 'downloadBkFiles']);

    Route::apiResource('setting', SettingController::class);
    Route::apiResource('notification', NotificationsController::class);
    Route::get('notification-list', [NotificationsController::class, 'notificationList']);
    Route::get('gallery-list', [DashboardController::class, 'globalGallery']);
    Route::get('search-list', [DashboardController::class, 'searchList']);
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
    Route::post('/store-access-token', [BackendSettingController::class, 'storeToken']);
    Route::post('/token-revoke', [BackendSettingController::class, 'revokeToken']);

    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('delete-account', [AuthController::class, 'deleteAccount']);

    Route::post('add-address', [AddressController::class, 'store']);
    Route::get('address-list', [AddressController::class, 'AddressList']);
    Route::get('remove-address', [AddressController::class, 'RemoveAddress']);
    Route::post('edit-address', [AddressController::class, 'EditAddress']);

    Route::get('vendor-dashboard-list', [DashboardController::class, 'VendorDashboardDetail']);
    Route::get('doctor-dashboard-list', [DashboardController::class, 'doctorDashboardDetail']);
    Route::get('receptionist-dashboard-list', [DashboardController::class, 'receptionistDashboardDetail']);
    Route::get('advance_filter', [SearchController::class, 'advanceFilter'])->name('advance_filter');

    // Keep report creation on the idempotent endpoint used by the patient app.
    // The resource route remains available for reads and updates, but must not
    // provide a second non-idempotent creation path.
    Route::apiResource('incidence-list', IncidenceController::class)->except(['store']);
    Route::post('incidence-save', [IncidenceController::class, 'store'])
        ->middleware('idempotency:incident');
    Route::post('incidence/change-status/{id}', [IncidenceController::class, 'changeStatus']);
    Route::post('update-incident-status/{id}', [IncidenceController::class, 'updateIncidentStatus']);
    // Route::post('incidence/{id}', [IncidenceController::class, 'update']);
});
Route::get('app-configuration', [SettingController::class, 'appConfiguraton']);
Route::get('translations', [SettingController::class, 'translations']);
Route::get('download-json', [SettingController::class, 'downloadJson']);
