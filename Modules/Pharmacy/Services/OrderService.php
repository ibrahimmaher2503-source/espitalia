<?php

namespace Modules\Pharmacy\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Pharmacy\Models\Pharmacy;
use Modules\Pharmacy\Models\PharmacyCartItem;
use Modules\Pharmacy\Models\PharmacyCoupon;
use Modules\Pharmacy\Models\PharmacyCouponUsage;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Models\PharmacyOrderItem;
use Modules\Pharmacy\Models\PharmacyPrescription;
use Modules\Pharmacy\Models\PharmacyProductBatch;
use Modules\Pharmacy\Models\PharmacyStockAlert;
use Modules\Pharmacy\Transformers\PharmacyOrderDetailResource;

class OrderService
{
    protected PharmacyNotificationService $notificationService;

    protected PharmacyAuditService $auditService;

    public function __construct()
    {
        $this->notificationService = new PharmacyNotificationService;
    }

    /**
     * Place an order with atomic transaction and pessimistic locking
     */
    public function placeOrder(
        int $userId,
        int $pharmacyId,
        ?int $addressId,
        string $paymentMethod = 'cash_on_delivery',
        string $deliveryType = 'delivery',
        ?string $deliveryNotes = null,
        ?string $couponCode = null,
        ?int $prescriptionId = null,
        ?string $deliveryAddress = null,
    ) {
        return DB::transaction(function () use ($userId, $pharmacyId, $addressId, $paymentMethod, $deliveryType, $deliveryNotes, $couponCode, $prescriptionId, $deliveryAddress) {
            if ($paymentMethod !== 'cash_on_delivery') {
                throw new \Exception(__('pharmacy.msg_payment_method_unavailable'));
            }

            // 1. Get cart items
            $cartItems = PharmacyCartItem::where('user_id', $userId)->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception(__('pharmacy.msg_cart_empty'));
            }

            // 2. Check prescription requirement
            $requiresPrescription = $cartItems->contains(fn ($item) => $item->product && $item->product->requires_prescription);

            if ($requiresPrescription) {
                if (! $prescriptionId) {
                    throw new \Exception(__('pharmacy.msg_prescription_required'));
                }

                $prescription = PharmacyPrescription::where('id', $prescriptionId)
                    ->where('user_id', $userId)
                    ->where('status', PharmacyPrescription::STATUS_APPROVED)
                    ->first();

                if (! $prescription) {
                    throw new \Exception(__('pharmacy.msg_prescription_not_found'));
                }
            }

            // 3. Get pharmacy
            $pharmacy = Pharmacy::where('id', $pharmacyId)
                ->where('is_active', true)
                ->firstOrFail();

            // 4. Determine delivery fee from zone or default
            $deliveryFee = 0;
            $estimatedDeliveryAt = null;

            if ($deliveryType === 'delivery') {
                $deliveryFee = $pharmacy->delivery_fee;

                if ($addressId) {
                    $address = \App\Models\Address::findOrFail($addressId);

                    // Try zone-based fee
                    $zone = $pharmacy->deliveryZones()
                        ->where('is_active', true)
                        ->where(function ($q) use ($address) {
                            $q->where('governorate_id', $address->governorate_id)
                                ->where(function ($q2) use ($address) {
                                    $q2->whereNull('city_id')
                                        ->orWhere('city_id', $address->city_id);
                                });
                        })
                        ->orderBy('sort_order')
                        ->first();

                    if ($zone) {
                        $deliveryFee = $zone->delivery_fee;
                        $estimatedDeliveryAt = now()->addMinutes($zone->estimated_delivery_minutes);
                    } else {
                        $estimatedDeliveryAt = now()->addMinutes($pharmacy->avg_preparation_time_min + 30);
                    }
                }
            }

            // 5. Lock inventory and validate stock
            $subtotal = 0;
            $inventoriesWithLock = [];

            foreach ($cartItems as $cartItem) {
                // Verify product is still active
                if (! $cartItem->product || ! $cartItem->product->is_active) {
                    throw new \Exception(__('pharmacy.msg_product_unavailable', ['product' => $cartItem->product?->name_en ?? '']));
                }

                $inventory = $pharmacy->inventory()
                    ->where('product_id', $cartItem->product_id)
                    ->where('stock_qty', '>=', $cartItem->quantity)
                    ->where('is_available', true)
                    ->lockForUpdate()
                    ->first();

                if (! $inventory) {
                    throw new \Exception(__('pharmacy.msg_insufficient_stock', ['product' => $cartItem->product->name_en ?? '']));
                }

                $subtotal += $inventory->price * $cartItem->quantity;
                $inventoriesWithLock[] = [
                    'inventory' => $inventory,
                    'cartItem' => $cartItem,
                ];
            }

            // 6. Validate and apply coupon
            $couponId = null;
            $couponDiscount = 0;

            if ($couponCode) {
                $coupon = PharmacyCoupon::where('code', $couponCode)
                    ->where(function ($q) use ($pharmacyId) {
                        $q->whereNull('pharmacy_id')
                            ->orWhere('pharmacy_id', $pharmacyId);
                    })
                    ->lockForUpdate()
                    ->first();

                if (! $coupon || ! $coupon->isValidForUser($userId)) {
                    throw new \Exception(__('pharmacy.msg_coupon_invalid'));
                }

                $couponDiscount = $coupon->calculateDiscount($subtotal);
                $couponId = $coupon->id;
            }

            // 7. Check minimum order amount
            if ($subtotal < $pharmacy->minimum_order_amount) {
                throw new \Exception(__('pharmacy.msg_below_minimum', ['amount' => $pharmacy->minimum_order_amount]));
            }

            // 8. Calculate total
            $total = $subtotal - $couponDiscount + $deliveryFee;

            // 9. Generate pickup code if pickup
            $pickupCode = $deliveryType === 'pickup' ? str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT) : null;

            // 10. Create order
            $order = PharmacyOrder::create([
                'user_id' => $userId,
                'pharmacy_id' => $pharmacyId,
                'address_id' => $addressId,
                'delivery_address' => $deliveryAddress ? trim($deliveryAddress) : null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'payment_method' => $paymentMethod,
                'status' => PharmacyOrder::STATUS_PENDING,
                'delivery_type' => $deliveryType,
                'delivery_notes' => $deliveryNotes,
                'estimated_delivery_at' => $estimatedDeliveryAt,
                'pickup_code' => $pickupCode,
                'coupon_id' => $couponId,
                'coupon_discount' => $couponDiscount,
            ]);

            // 11. Create order items and deduct stock using FEFO
            foreach ($inventoriesWithLock as $item) {
                $inventory = $item['inventory'];
                $cartItem = $item['cartItem'];

                PharmacyOrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->getNameAttribute(),
                    'quantity' => $cartItem->quantity,
                    'price' => $inventory->price,
                ]);

                // FEFO stock deduction
                $this->deductStockFefo($inventory, $cartItem->quantity);

                // Check stock alerts
                $this->checkStockAlerts($inventory);
            }

            // 12. Record coupon usage
            if ($couponId && $couponDiscount > 0) {
                PharmacyCouponUsage::create([
                    'coupon_id' => $couponId,
                    'user_id' => $userId,
                    'order_id' => $order->id,
                    'discount_amount' => $couponDiscount,
                    'created_at' => now(),
                ]);

                PharmacyCoupon::where('id', $couponId)->increment('used_count');
            }

            // 13. Link prescription
            if ($prescriptionId && isset($prescription)) {
                $prescription->update([
                    'order_id' => $order->id,
                    'status' => PharmacyPrescription::STATUS_LINKED,
                ]);
            }

            // 14. Clear cart
            PharmacyCartItem::where('user_id', $userId)->delete();

            // 15. Increment pharmacy total orders
            Pharmacy::where('id', $pharmacyId)->increment('total_orders');

            // 16. Send notification
            $this->notificationService->notifyOrderStatus($order, PharmacyOrder::STATUS_PENDING);

            // 17. Audit log
            PharmacyAuditService::log($pharmacyId, 'order_placed', $order, null, [
                'total' => $total,
                'items_count' => $cartItems->count(),
            ]);

            return new PharmacyOrderDetailResource($order->load('items', 'pharmacy', 'address'));
        });
    }

    /**
     * Deduct stock using FEFO (First Expiry, First Out) from batches
     */
    protected function deductStockFefo($inventory, int $quantity): void
    {
        // Decrement inventory total
        $inventory->decrement('stock_qty', $quantity);

        // Deduct from batches (FEFO)
        $remaining = $quantity;
        $batches = PharmacyProductBatch::where('inventory_id', $inventory->id)
            ->where('is_active', true)
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deduct = min($batch->quantity, $remaining);
            $batch->decrement('quantity', $deduct);
            $remaining -= $deduct;
        }

        // If batches didn't cover the full quantity, log a warning
        // (inventory total was already decremented, but batch-level tracking is short)
        if ($remaining > 0) {
            \Illuminate\Support\Facades\Log::warning("FEFO shortfall: {$remaining} units unaccounted in batches for inventory #{$inventory->id}");
        }
    }

    /**
     * Check and create stock alerts if thresholds are breached
     */
    protected function checkStockAlerts($inventory): void
    {
        if ($inventory->stock_qty <= 0) {
            PharmacyStockAlert::updateOrCreate(
                [
                    'inventory_id' => $inventory->id,
                    'alert_type' => PharmacyStockAlert::TYPE_OUT_OF_STOCK,
                    'is_resolved' => false,
                ],
                [
                    'pharmacy_id' => $inventory->pharmacy_id,
                    'threshold' => $inventory->low_stock_threshold,
                    'current_qty' => $inventory->stock_qty,
                ]
            );
        } elseif ($inventory->stock_qty <= $inventory->low_stock_threshold) {
            PharmacyStockAlert::updateOrCreate(
                [
                    'inventory_id' => $inventory->id,
                    'alert_type' => PharmacyStockAlert::TYPE_LOW_STOCK,
                    'is_resolved' => false,
                ],
                [
                    'pharmacy_id' => $inventory->pharmacy_id,
                    'threshold' => $inventory->low_stock_threshold,
                    'current_qty' => $inventory->stock_qty,
                ]
            );
        }
    }

    /**
     * Cancel an order and restore stock
     */
    public function cancelOrder($orderId, $cancelledBy = null, $reason = null, $isAdmin = false)
    {
        return DB::transaction(function () use ($orderId, $cancelledBy, $reason, $isAdmin) {
            $order = PharmacyOrder::with('items')->lockForUpdate()->findOrFail($orderId);

            if ($order->status === PharmacyOrder::STATUS_CANCELLED) {
                throw new \Exception('Order is already cancelled');
            }

            if (! $isAdmin && ! in_array($order->status, [PharmacyOrder::STATUS_PENDING, PharmacyOrder::STATUS_CONFIRMED])) {
                throw new \Exception('Only pending or confirmed orders can be cancelled');
            }

            $previousStatus = $order->status;

            // Restore stock with FEFO-aware re-addition
            foreach ($order->items as $orderItem) {
                $inventory = $order->pharmacy->inventory()
                    ->where('product_id', $orderItem->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->increment('stock_qty', $orderItem->quantity);

                    // Resolve any stock alerts
                    if ($inventory->stock_qty > $inventory->low_stock_threshold) {
                        PharmacyStockAlert::where('inventory_id', $inventory->id)
                            ->where('is_resolved', false)
                            ->update(['is_resolved' => true, 'resolved_at' => now()]);
                    }
                }
            }

            // Update order
            $order->update([
                'status' => PharmacyOrder::STATUS_CANCELLED,
                'cancelled_by' => $cancelledBy ?? auth()->id(),
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            // Reverse commission if previously delivered
            if ($previousStatus === PharmacyOrder::STATUS_DELIVERED) {
                $commissionService = new PharmacyCommissionService;
                $commissionService->reverseCommission($order);
            }

            // Notify
            $this->notificationService->notifyOrderStatus($order, PharmacyOrder::STATUS_CANCELLED);

            // Audit
            PharmacyAuditService::log($order->pharmacy_id, 'status_changed', $order, ['status' => $previousStatus], ['status' => PharmacyOrder::STATUS_CANCELLED]);

            return $order;
        });
    }
}
