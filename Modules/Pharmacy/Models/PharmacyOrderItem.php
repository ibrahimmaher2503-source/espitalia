<?php

namespace Modules\Pharmacy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyOrderItem extends Model
{
    // NOTE: Does NOT use SoftDeletes - immutable snapshots
    protected $table = 'pharmacy_order_items';

    public $timestamps = true; // Keep timestamps (created_at, updated_at)

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(PharmacyOrder::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'product_id');
    }
}
