<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyProductBatch extends BaseModel
{
    protected $table = 'pharmacy_product_batches';

    protected $fillable = [
        'inventory_id', 'batch_number', 'quantity', 'manufacturing_date',
        'expiry_date', 'purchase_price', 'is_active', 'notes',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(PharmacyInventory::class, 'inventory_id');
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('is_active', true)
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    public function scopeByInventory($query, $inventoryId)
    {
        return $query->where('inventory_id', $inventoryId);
    }

    public function scopeFefo($query)
    {
        return $query->where('is_active', true)
            ->where('expiry_date', '>', now())
            ->where('quantity', '>', 0)
            ->orderBy('expiry_date', 'asc');
    }
}
