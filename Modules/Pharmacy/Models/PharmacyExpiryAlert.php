<?php

namespace Modules\Pharmacy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyExpiryAlert extends Model
{
    protected $table = 'pharmacy_expiry_alerts';

    protected $fillable = [
        'pharmacy_id', 'batch_id', 'alert_type',
        'expiry_date', 'days_until_expiry', 'is_resolved', 'resolved_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'days_until_expiry' => 'integer',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    const TYPE_EXPIRING_SOON = 'expiring_soon';
    const TYPE_EXPIRED = 'expired';

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PharmacyProductBatch::class, 'batch_id');
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeByPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function resolve(): void
    {
        $this->update(['is_resolved' => true, 'resolved_at' => now()]);
    }
}
