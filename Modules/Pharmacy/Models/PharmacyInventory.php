<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyInventory extends BaseModel
{
    protected $table = 'pharmacy_inventory';

    protected $fillable = ['pharmacy_id', 'product_id', 'price', 'stock_qty', 'is_available', 'low_stock_threshold'];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_qty' => 'integer',
        'is_available' => 'boolean',
        'low_stock_threshold' => 'integer',
    ];

    // Relationships
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'product_id');
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('stock_qty', '>', 0)
            ->where('is_available', true);
    }

    public function batches()
    {
        return $this->hasMany(PharmacyProductBatch::class, 'inventory_id');
    }

    public function activeBatches()
    {
        return $this->hasMany(PharmacyProductBatch::class, 'inventory_id')
            ->where('is_active', true)
            ->where('expiry_date', '>', now())
            ->orderBy('expiry_date', 'asc');
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_qty', '<=', 'low_stock_threshold');
    }
}
