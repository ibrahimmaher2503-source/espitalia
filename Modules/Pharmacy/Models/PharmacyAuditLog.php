<?php

namespace Modules\Pharmacy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PharmacyAuditLog extends Model
{
    public $timestamps = false;

    protected $table = 'pharmacy_audit_logs';

    protected $fillable = [
        'pharmacy_id', 'user_id', 'action',
        'auditable_type', 'auditable_id',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_STOCK_ADJUSTED = 'stock_adjusted';
    const ACTION_ORDER_PLACED = 'order_placed';
    const ACTION_REFUND_PROCESSED = 'refund_processed';
    const ACTION_PAYOUT_MADE = 'payout_made';

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeByPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
