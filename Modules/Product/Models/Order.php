<?php

namespace Modules\Product\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Logistic\Models\LogisticZone;

class Order extends Model
{
    use HasFactory;

    protected $casts = [
        'order_group_id' => 'integer',
        'user_id' => 'integer',
        'location_id' => 'integer',
        'coupon_discount_amount' => 'decimal:2',
        'admin_earning_percentage' => 'decimal:4',
        'total_admin_earnings' => 'decimal:2',
        'total_vendor_earnings' => 'decimal:2',
        'logistic_id' => 'integer',
        'pickup_hub_id' => 'integer',
        'shipping_cost' => 'decimal:2',
        'tips_amount' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return \Modules\Product\Database\factories\OrderFactory::new();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logistic()
    {
        return $this->belongsTo(LogisticZone::class);
    }

    public function orderGroup()
    {
        return $this->belongsTo(OrderGroup::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class)->with('review');
    }

    public function orderUpdates()
    {
        return $this->hasMany(OrderUpdate::class)->latest();
    }

    // public function location()
    // {
    //     return $this->belongsTo(Location::class);
    // }
}
