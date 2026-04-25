<?php

namespace Modules\Pharmacy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStockAlert extends Model
{
    protected $table = 'pharmacy_stock_alerts';

    protected $fillable = [
        'pharmacy_id', 'inventory_id', 'alert_type',
        'threshold', 'current_qty', 'is_resolved', 'resolved_at',
    ];

    protected $casts = [
        'threshold' => 'integer',
        'current_qty' => 'integer',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    const TYPE_LOW_STOCK = 'low_stock';
    const TYPE_OUT_OF_STOCK = 'out_of_stock';

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(PharmacyInventory::class, 'inventory_id');
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
