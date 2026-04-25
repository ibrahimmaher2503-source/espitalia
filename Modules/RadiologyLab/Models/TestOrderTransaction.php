<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;

class TestOrderTransaction extends BaseModel
{
    protected $table = 'test_order_transactions';

    protected $fillable = [
        'test_order_id',
        'external_transaction_id',
        'transaction_type',
        'payment_method',
        'payment_gateway',
        'payment_status',
        'total_amount',
        'discount_amount',
        'tax_percentage',
        'currency',
        'refund_amount',
        'refund_status',
        'paid_at',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'tax_percentage' => 'array',
        'paid_at' => 'datetime',
        'payment_status' => 'boolean',
    ];

    public function testOrder()
    {
        return $this->belongsTo(TestOrder::class);
    }
}
