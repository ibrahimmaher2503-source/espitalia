<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharmacy\Http\Controllers\API\CartApiController;
use Modules\Pharmacy\Http\Controllers\API\CategoryApiController;
use Modules\Pharmacy\Http\Controllers\API\CouponApiController;
use Modules\Pharmacy\Http\Controllers\API\FilterApiController;
use Modules\Pharmacy\Http\Controllers\API\NotificationApiController;
use Modules\Pharmacy\Http\Controllers\API\OrderApiController;
use Modules\Pharmacy\Http\Controllers\API\PharmacyMatchingApiController;
use Modules\Pharmacy\Http\Controllers\API\PrescriptionApiController;
use Modules\Pharmacy\Http\Controllers\API\ProductApiController;
use Modules\Pharmacy\Http\Controllers\API\RefundApiController;

/*
|--------------------------------------------------------------------------
| API Routes (Customer API)
|--------------------------------------------------------------------------
|
| Routes registered here are wrapped by RouteServiceProvider with:
| - Prefix: v1/pharmacy
| - Middleware: auth:sanctum
| - Name prefix: api.
|
*/

Route::prefix('v1/pharmacy')->middleware('auth:sanctum')->name('api.')->group(function () {

    // Categories
    Route::get('categories', [CategoryApiController::class, 'index'])->name('categories.index');
    Route::get('categories/{id}/children', [CategoryApiController::class, 'children'])->name('categories.children');

    // Products
    Route::get('products', [ProductApiController::class, 'index'])->name('products.index');
    Route::get('products/{id}', [ProductApiController::class, 'show'])->name('products.show');

    // Filters
    Route::get('filters/brands', [FilterApiController::class, 'brands'])->name('filters.brands');
    Route::get('filters/product-types', [FilterApiController::class, 'productTypes'])->name('filters.product-types');

    // Cart
    Route::get('cart', [CartApiController::class, 'index'])->name('cart.index');
    Route::post('cart/items', [CartApiController::class, 'store'])->name('cart.items.store');
    Route::patch('cart/items/{id}', [CartApiController::class, 'update'])->name('cart.items.update');
    Route::delete('cart/items/{id}', [CartApiController::class, 'destroy'])->name('cart.items.destroy');

    // Pharmacy Matching
    Route::get('cart/available-pharmacies', [PharmacyMatchingApiController::class, 'index'])->name('cart.available-pharmacies');

    // Orders
    Route::post('orders', [OrderApiController::class, 'store'])->name('orders.store');
    Route::get('orders', [OrderApiController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [OrderApiController::class, 'show'])->name('orders.show');
    Route::post('orders/{id}/cancel', [OrderApiController::class, 'cancel'])->name('orders.cancel');

    // Prescriptions (patient)
    Route::post('prescriptions', [PrescriptionApiController::class, 'store'])->name('prescriptions.store');
    Route::get('prescriptions', [PrescriptionApiController::class, 'index'])->name('prescriptions.index');
    Route::get('prescriptions/{id}', [PrescriptionApiController::class, 'show'])->name('prescriptions.show');

    // Coupons (patient validation)
    Route::post('coupons/validate', [CouponApiController::class, 'validate'])->name('coupons.validate');

    // Refunds (patient)
    Route::post('orders/{id}/refund', [RefundApiController::class, 'store'])->name('refunds.store');
    Route::get('refunds', [RefundApiController::class, 'index'])->name('refunds.index');
    Route::get('refunds/{id}', [RefundApiController::class, 'show'])->name('refunds.show');

    // Notifications
    Route::get('notifications', [NotificationApiController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationApiController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('notifications/unread-count', [NotificationApiController::class, 'unreadCount'])->name('notifications.unread-count');
});
