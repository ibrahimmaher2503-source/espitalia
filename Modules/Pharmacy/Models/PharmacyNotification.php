<?php

namespace Modules\Pharmacy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyNotification extends Model
{
    public $timestamps = false;

    protected $table = 'pharmacy_notifications';

    protected $fillable = [
        'pharmacy_id', 'user_id', 'type',
        'title_ar', 'title_en', 'body_ar', 'body_en',
        'data', 'read_at', 'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Notification types
    const TYPE_ORDER_PLACED = 'order_placed';
    const TYPE_ORDER_CONFIRMED = 'order_confirmed';
    const TYPE_ORDER_PREPARING = 'order_preparing';
    const TYPE_ORDER_ON_THE_WAY = 'order_on_the_way';
    const TYPE_ORDER_DELIVERED = 'order_delivered';
    const TYPE_ORDER_CANCELLED = 'order_cancelled';
    const TYPE_PRESCRIPTION_UPLOADED = 'prescription_uploaded';
    const TYPE_PRESCRIPTION_REVIEWED = 'prescription_reviewed';
    const TYPE_LOW_STOCK = 'low_stock';
    const TYPE_OUT_OF_STOCK = 'out_of_stock';
    const TYPE_EXPIRY_WARNING = 'expiry_warning';
    const TYPE_BATCH_EXPIRED = 'batch_expired';
    const TYPE_REFUND_REQUESTED = 'refund_requested';
    const TYPE_REFUND_PROCESSED = 'refund_processed';
    const TYPE_PAYOUT_PROCESSED = 'payout_processed';

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getTitleAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->title_ar : $this->title_en;

        return $localized ?: ($locale === 'ar' ? $this->title_en : $this->title_ar) ?: '';
    }

    public function getBodyAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->body_ar : $this->body_en;

        return $localized ?: ($locale === 'ar' ? $this->body_en : $this->body_ar) ?: '';
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeByPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}
