<?php

use Illuminate\Support\Facades\Route;
use Modules\Pharmacy\Http\Controllers\PharmacyAuditController;
use Modules\Pharmacy\Http\Controllers\PharmacyBrandController;
use Modules\Pharmacy\Http\Controllers\PharmacyCategoryController;
use Modules\Pharmacy\Http\Controllers\PharmacyCommissionController;
use Modules\Pharmacy\Http\Controllers\PharmacyController;
use Modules\Pharmacy\Http\Controllers\PharmacyCouponController;
use Modules\Pharmacy\Http\Controllers\PharmacyEarningController;
use Modules\Pharmacy\Http\Controllers\PharmacyOrderController;
use Modules\Pharmacy\Http\Controllers\PharmacyPrescriptionController;
use Modules\Pharmacy\Http\Controllers\PharmacyRefundController;
use Modules\Pharmacy\Http\Controllers\PharmacyReportController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\AlertController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\BatchController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\CouponController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\DashboardController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\DeliveryZoneController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\InventoryController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\PharmacyPanelOrderController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\PrescriptionController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\RefundController;
use Modules\Pharmacy\Http\Controllers\PharmacyPanel\StaffController;
use Modules\Pharmacy\Http\Controllers\PharmacyProductController;
use Modules\Pharmacy\Http\Controllers\PharmacyProductTypeController;

/*
|--------------------------------------------------------------------------
| Web Routes (Admin & Pharmacy Panel)
|--------------------------------------------------------------------------
|
| Routes registered here are wrapped by RouteServiceProvider with:
| - Prefix: app
| - Middleware: auth, auth_check
| - Name prefix: backend.
|
*/

Route::prefix('app')->as('backend.')->middleware(['auth', 'auth_check'])->group(function () {
    // Admin routes: Categories, Brands, Product Types, Products, Pharmacies
    // IMPORTANT: Custom routes BEFORE resource() to avoid {id} parameter collision
    Route::get('pharmacy-categories/index-data', [PharmacyCategoryController::class, 'index_data'])->name('pharmacy-categories.index_data');
    Route::post('pharmacy-categories/bulk-action', [PharmacyCategoryController::class, 'bulk_action'])->name('pharmacy-categories.bulk_action');
    Route::post('pharmacy-categories/{id}/update-status', [PharmacyCategoryController::class, 'update_status'])->name('pharmacy-categories.update_status');
    Route::resource('pharmacy-categories', PharmacyCategoryController::class);

    Route::get('pharmacy-brands/index-data', [PharmacyBrandController::class, 'index_data'])->name('pharmacy-brands.index_data');
    Route::post('pharmacy-brands/bulk-action', [PharmacyBrandController::class, 'bulk_action'])->name('pharmacy-brands.bulk_action');
    Route::post('pharmacy-brands/{id}/update-status', [PharmacyBrandController::class, 'update_status'])->name('pharmacy-brands.update_status');
    Route::resource('pharmacy-brands', PharmacyBrandController::class);

    Route::get('pharmacy-product-types/index-data', [PharmacyProductTypeController::class, 'index_data'])->name('pharmacy-product-types.index_data');
    Route::post('pharmacy-product-types/bulk-action', [PharmacyProductTypeController::class, 'bulk_action'])->name('pharmacy-product-types.bulk_action');
    Route::post('pharmacy-product-types/{id}/update-status', [PharmacyProductTypeController::class, 'update_status'])->name('pharmacy-product-types.update_status');
    Route::resource('pharmacy-product-types', PharmacyProductTypeController::class);

    Route::get('pharmacy-commissions/index-data', [PharmacyCommissionController::class, 'index_data'])->name('pharmacy-commissions.index_data');
    Route::post('pharmacy-commissions/bulk-action', [PharmacyCommissionController::class, 'bulk_action'])->name('pharmacy-commissions.bulk_action');
    Route::post('pharmacy-commissions/{id}/update-status', [PharmacyCommissionController::class, 'update_status'])->name('pharmacy-commissions.update_status');
    Route::resource('pharmacy-commissions', PharmacyCommissionController::class);

    Route::get('pharmacy-earnings', [PharmacyEarningController::class, 'index'])->name('pharmacy-earnings.index');
    Route::get('pharmacy-earnings/index-data', [PharmacyEarningController::class, 'index_data'])->name('pharmacy-earnings.index_data');
    Route::get('pharmacy-earnings/{id}', [PharmacyEarningController::class, 'show'])->name('pharmacy-earnings.show');
    Route::get('pharmacy-earnings/{id}/data', [PharmacyEarningController::class, 'show_data'])->name('pharmacy-earnings.show_data');
    Route::get('pharmacy-earnings/{id}/payouts', [PharmacyEarningController::class, 'payouts_data'])->name('pharmacy-earnings.payouts_data');
    Route::post('pharmacy-earnings/{id}/payout', [PharmacyEarningController::class, 'payout'])->name('pharmacy-earnings.payout');

    Route::get('pharmacy-products/index-data', [PharmacyProductController::class, 'index_data'])->name('pharmacy-products.index_data');
    Route::post('pharmacy-products/bulk-action', [PharmacyProductController::class, 'bulk_action'])->name('pharmacy-products.bulk_action');
    Route::post('pharmacy-products/{id}/update-status', [PharmacyProductController::class, 'update_status'])->name('pharmacy-products.update_status');
    Route::resource('pharmacy-products', PharmacyProductController::class);

    Route::get('pharmacies/index-data', [PharmacyController::class, 'index_data'])->name('pharmacies.index_data');
    Route::post('pharmacies/bulk-action', [PharmacyController::class, 'bulk_action'])->name('pharmacies.bulk_action');
    Route::post('pharmacies/{id}/update-status', [PharmacyController::class, 'update_status'])->name('pharmacies.update_status');
    Route::resource('pharmacies', PharmacyController::class);

    // Admin order management
    Route::get('pharmacy-orders/index-data', [PharmacyOrderController::class, 'index_data'])->name('pharmacy-orders.index_data');
    Route::post('pharmacy-orders/{id}/update-status', [PharmacyOrderController::class, 'update_status'])->name('pharmacy-orders.update_status');
    Route::resource('pharmacy-orders', PharmacyOrderController::class, ['only' => ['index', 'show']]);

    // Admin: Prescriptions
    Route::get('pharmacy-prescriptions', [PharmacyPrescriptionController::class, 'index'])->name('pharmacy-prescriptions.index');
    Route::get('pharmacy-prescriptions/index-data', [PharmacyPrescriptionController::class, 'index_data'])->name('pharmacy-prescriptions.index_data');
    Route::get('pharmacy-prescriptions/{id}', [PharmacyPrescriptionController::class, 'show'])->name('pharmacy-prescriptions.show');

    // Admin: Coupons
    Route::get('pharmacy-coupons/index-data', [PharmacyCouponController::class, 'index_data'])->name('pharmacy-coupons.index_data');
    Route::post('pharmacy-coupons/bulk-action', [PharmacyCouponController::class, 'bulk_action'])->name('pharmacy-coupons.bulk_action');
    Route::post('pharmacy-coupons/{id}/update-status', [PharmacyCouponController::class, 'update_status'])->name('pharmacy-coupons.update_status');
    Route::resource('pharmacy-coupons', PharmacyCouponController::class);

    // Admin: Refunds
    Route::get('pharmacy-refunds', [PharmacyRefundController::class, 'index'])->name('pharmacy-refunds.index');
    Route::get('pharmacy-refunds/index-data', [PharmacyRefundController::class, 'index_data'])->name('pharmacy-refunds.index_data');
    Route::get('pharmacy-refunds/{id}', [PharmacyRefundController::class, 'show'])->name('pharmacy-refunds.show');
    Route::post('pharmacy-refunds/{id}/process', [PharmacyRefundController::class, 'process'])->name('pharmacy-refunds.process');

    // Admin: Reports
    Route::get('pharmacy-reports', [PharmacyReportController::class, 'index'])->name('pharmacy-reports.index');
    Route::get('pharmacy-reports/revenue', [PharmacyReportController::class, 'revenue'])->name('pharmacy-reports.revenue');

    // Admin: Audit Log
    Route::get('pharmacy-audit', [PharmacyAuditController::class, 'index'])->name('pharmacy-audit.index');
    Route::get('pharmacy-audit/index-data', [PharmacyAuditController::class, 'index_data'])->name('pharmacy-audit.index_data');

    // Pharmacy panel routes (gated by role middleware + pharmacy assignment check at group level)
    Route::prefix('pharmacy-panel')->as('pharmacy-panel-')
        ->middleware(['role:pharmacy', \Modules\Pharmacy\Http\Middleware\EnsurePharmacyAssigned::class])
        ->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('dashboard/data', [DashboardController::class, 'dashboard_data'])->name('dashboard.data');

        // Inventory (existing)
        Route::get('inventory/index-data', [InventoryController::class, 'index_data'])->name('inventory.index_data');
        Route::post('inventory/{id}/update-status', [InventoryController::class, 'update_status'])->name('inventory.update_status');
        Route::resource('inventory', InventoryController::class);

        // Orders (existing)
        Route::get('orders/index-data', [PharmacyPanelOrderController::class, 'index_data'])->name('orders.index_data');
        Route::post('orders/{id}/accept', [PharmacyPanelOrderController::class, 'accept'])->name('orders.accept');
        Route::post('orders/{id}/reject', [PharmacyPanelOrderController::class, 'reject'])->name('orders.reject');
        Route::post('orders/{id}/advance-status', [PharmacyPanelOrderController::class, 'advance_status'])->name('orders.advance_status');
        Route::resource('orders', PharmacyPanelOrderController::class, ['only' => ['index', 'show']]);

        // Staff
        Route::get('staff/index-data', [StaffController::class, 'index_data'])->name('staff.index_data');
        Route::resource('staff', StaffController::class);

        // Batches
        Route::get('batches/index-data', [BatchController::class, 'index_data'])->name('batches.index_data');
        Route::resource('batches', BatchController::class);

        // Prescriptions
        Route::get('prescriptions', [PrescriptionController::class, 'index'])->name('prescriptions.index');
        Route::get('prescriptions/index-data', [PrescriptionController::class, 'index_data'])->name('prescriptions.index_data');
        Route::get('prescriptions/{id}', [PrescriptionController::class, 'show'])->name('prescriptions.show');
        Route::post('prescriptions/{id}/review', [PrescriptionController::class, 'review'])->name('prescriptions.review');

        // Delivery Zones
        Route::get('delivery-zones/index-data', [DeliveryZoneController::class, 'index_data'])->name('delivery-zones.index_data');
        Route::post('delivery-zones/{id}/update-status', [DeliveryZoneController::class, 'update_status'])->name('delivery-zones.update_status');
        Route::resource('delivery-zones', DeliveryZoneController::class);

        // Coupons
        Route::get('coupons/index-data', [CouponController::class, 'index_data'])->name('coupons.index_data');
        Route::post('coupons/{id}/update-status', [CouponController::class, 'update_status'])->name('coupons.update_status');
        Route::resource('coupons', CouponController::class, ['except' => ['show']]);

        // Refunds
        Route::get('refunds', [RefundController::class, 'index'])->name('refunds.index');
        Route::get('refunds/index-data', [RefundController::class, 'index_data'])->name('refunds.index_data');
        Route::get('refunds/{id}', [RefundController::class, 'show'])->name('refunds.show');

        // Alerts
        Route::get('alerts', [AlertController::class, 'index'])->name('alerts.index');
        Route::get('alerts/index-data', [AlertController::class, 'index_data'])->name('alerts.index_data');
    });
});
