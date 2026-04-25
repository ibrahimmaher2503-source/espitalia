<?php

namespace Modules\RadiologyLab\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TestResultHistory extends Model
{
    public $timestamps = false;

    protected $table = 'test_result_histories';

    protected $fillable = [
        'test_order_item_id',
        'test_order_id',
        'old_result_value',
        'old_result_unit',
        'old_reference_range',
        'old_result_status',
        'old_result_notes',
        'old_result_date',
        'old_result_by',
        'new_result_value',
        'new_result_unit',
        'new_reference_range',
        'new_result_status',
        'new_result_notes',
        'changed_by',
        'change_reason',
        'created_at',
    ];

    protected $casts = [
        'old_result_date' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function testOrderItem()
    {
        return $this->belongsTo(TestOrderItem::class);
    }

    public function testOrder()
    {
        return $this->belongsTo(TestOrder::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function oldResultBy()
    {
        return $this->belongsTo(User::class, 'old_result_by');
    }
}
