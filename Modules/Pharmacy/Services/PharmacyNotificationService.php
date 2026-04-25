<?php

namespace Modules\Pharmacy\Services;

use Modules\Pharmacy\Models\PharmacyExpiryAlert;
use Modules\Pharmacy\Models\PharmacyNotification;
use Modules\Pharmacy\Models\PharmacyOrder;
use Modules\Pharmacy\Models\PharmacyPrescription;
use Modules\Pharmacy\Models\PharmacyRefund;
use Modules\Pharmacy\Models\PharmacyStockAlert;

class PharmacyNotificationService
{
    public function notify(int $pharmacyId, ?int $userId, string $type, array $data): PharmacyNotification
    {
        return PharmacyNotification::create([
            'pharmacy_id' => $pharmacyId,
            'user_id' => $userId,
            'type' => $type,
            'title_ar' => $data['title_ar'] ?? '',
            'title_en' => $data['title_en'] ?? '',
            'body_ar' => $data['body_ar'] ?? null,
            'body_en' => $data['body_en'] ?? null,
            'data' => $data['extra'] ?? null,
            'created_at' => now(),
        ]);
    }

    public function notifyOrderStatus(PharmacyOrder $order, string $newStatus): void
    {
        $typeMap = [
            PharmacyOrder::STATUS_PENDING => PharmacyNotification::TYPE_ORDER_PLACED,
            PharmacyOrder::STATUS_CONFIRMED => PharmacyNotification::TYPE_ORDER_CONFIRMED,
            PharmacyOrder::STATUS_PREPARING => PharmacyNotification::TYPE_ORDER_PREPARING,
            PharmacyOrder::STATUS_ON_THE_WAY => PharmacyNotification::TYPE_ORDER_ON_THE_WAY,
            PharmacyOrder::STATUS_DELIVERED => PharmacyNotification::TYPE_ORDER_DELIVERED,
            PharmacyOrder::STATUS_CANCELLED => PharmacyNotification::TYPE_ORDER_CANCELLED,
        ];

        $type = $typeMap[$newStatus] ?? null;
        if (! $type) {
            return;
        }

        // Notify pharmacy staff for new orders
        if ($newStatus === PharmacyOrder::STATUS_PENDING) {
            $this->notify($order->pharmacy_id, null, $type, [
                'title_en' => 'New Order #' . $order->id,
                'title_ar' => 'طلب جديد #' . $order->id,
                'body_en' => 'A new order has been placed worth ' . $order->total,
                'body_ar' => 'تم تقديم طلب جديد بقيمة ' . $order->total,
                'extra' => ['order_id' => $order->id],
            ]);
        }

        // Notify patient for status changes
        if ($newStatus !== PharmacyOrder::STATUS_PENDING) {
            $this->notify($order->pharmacy_id, $order->user_id, $type, [
                'title_en' => 'Order #' . $order->id . ' ' . str_replace('_', ' ', $newStatus),
                'title_ar' => 'طلب #' . $order->id . ' تم تحديث الحالة',
                'extra' => ['order_id' => $order->id, 'status' => $newStatus],
            ]);
        }
    }

    public function notifyStockAlert(PharmacyStockAlert $alert): void
    {
        $type = $alert->alert_type === 'out_of_stock'
            ? PharmacyNotification::TYPE_OUT_OF_STOCK
            : PharmacyNotification::TYPE_LOW_STOCK;

        $productName = $alert->inventory->product->name ?? 'Unknown';

        $this->notify($alert->pharmacy_id, null, $type, [
            'title_en' => ucfirst(str_replace('_', ' ', $alert->alert_type)) . ': ' . $productName,
            'title_ar' => 'تنبيه مخزون: ' . $productName,
            'body_en' => "Current stock: {$alert->current_qty}, Threshold: {$alert->threshold}",
            'body_ar' => "المخزون الحالي: {$alert->current_qty}، الحد: {$alert->threshold}",
            'extra' => ['inventory_id' => $alert->inventory_id, 'alert_id' => $alert->id],
        ]);
    }

    public function notifyExpiryAlert(PharmacyExpiryAlert $alert): void
    {
        $type = $alert->alert_type === 'expired'
            ? PharmacyNotification::TYPE_BATCH_EXPIRED
            : PharmacyNotification::TYPE_EXPIRY_WARNING;

        $this->notify($alert->pharmacy_id, null, $type, [
            'title_en' => 'Expiry Alert: Batch expiring in ' . $alert->days_until_expiry . ' days',
            'title_ar' => 'تنبيه انتهاء صلاحية: ' . $alert->days_until_expiry . ' يوم',
            'extra' => ['batch_id' => $alert->batch_id, 'alert_id' => $alert->id],
        ]);
    }

    public function notifyPrescriptionStatus(PharmacyPrescription $prescription): void
    {
        $isApproved = $prescription->status === PharmacyPrescription::STATUS_APPROVED;

        if ($prescription->status === PharmacyPrescription::STATUS_PENDING) {
            // Notify pharmacy
            $this->notify($prescription->pharmacy_id ?? 0, null, PharmacyNotification::TYPE_PRESCRIPTION_UPLOADED, [
                'title_en' => 'New Prescription #' . $prescription->prescription_number,
                'title_ar' => 'وصفة جديدة #' . $prescription->prescription_number,
                'extra' => ['prescription_id' => $prescription->id],
            ]);
        } else {
            // Notify patient
            $this->notify($prescription->pharmacy_id ?? 0, $prescription->user_id, PharmacyNotification::TYPE_PRESCRIPTION_REVIEWED, [
                'title_en' => 'Prescription ' . ($isApproved ? 'Approved' : 'Rejected'),
                'title_ar' => 'الوصفة ' . ($isApproved ? 'تمت الموافقة' : 'تم الرفض'),
                'extra' => ['prescription_id' => $prescription->id, 'status' => $prescription->status],
            ]);
        }
    }

    public function notifyRefund(PharmacyRefund $refund): void
    {
        if ($refund->status === PharmacyRefund::STATUS_PENDING) {
            // Notify pharmacy + admin
            $this->notify($refund->order->pharmacy_id, null, PharmacyNotification::TYPE_REFUND_REQUESTED, [
                'title_en' => 'Refund Requested for Order #' . $refund->order_id,
                'title_ar' => 'طلب استرداد للطلب #' . $refund->order_id,
                'extra' => ['refund_id' => $refund->id, 'order_id' => $refund->order_id],
            ]);
        } else {
            // Notify patient
            $this->notify($refund->order->pharmacy_id, $refund->user_id, PharmacyNotification::TYPE_REFUND_PROCESSED, [
                'title_en' => 'Refund ' . ucfirst($refund->status) . ' - ' . $refund->refund_amount,
                'title_ar' => 'تم ' . ($refund->status === 'processed' ? 'معالجة' : 'رفض') . ' الاسترداد',
                'extra' => ['refund_id' => $refund->id, 'status' => $refund->status],
            ]);
        }
    }
}
