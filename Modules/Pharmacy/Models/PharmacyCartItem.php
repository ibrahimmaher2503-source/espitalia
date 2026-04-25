<?php

namespace Modules\Pharmacy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyCartItem extends Model
{
    // NOTE: Does NOT extend BaseModel - no soft deletes, no audit columns, no timestamps
    protected $table = 'pharmacy_cart_items';

    protected $fillable = ['user_id', 'product_id', 'quantity'];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(PharmacyProduct::class, 'product_id');
    }
}
