<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TestOrderItem extends BaseModel
{
    use HasFactory;

    protected $table = 'test_order_items';

    protected $fillable = [
        'test_order_id',
        'lab_test_id',
        'price',
        'status',
        'result_value',
        'result_unit',
        'reference_range',
        'result_status',
        'result_notes',
        'result_date',
        'result_by',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'result_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    // ── Status Constants ───────────────────────────────────────

    const STATUS_PENDING = 'pending';
    const STATUS_SAMPLE_COLLECTED = 'sample_collected';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    // ── Relationships ──────────────────────────────────────────

    public function testOrder()
    {
        return $this->belongsTo(TestOrder::class, 'test_order_id');
    }

    public function labTest()
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function resultEnteredBy()
    {
        return $this->belongsTo(User::class, 'result_by');
    }

    public function histories()
    {
        return $this->hasMany(TestResultHistory::class, 'test_order_item_id')->orderByDesc('created_at');
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeWithResults($query)
    {
        return $query->whereNotNull('result_value');
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getResultStatusBadgeAttribute()
    {
        return match ($this->result_status) {
            'normal' => '<span class="badge bg-success">' . __('radiology_lab.result_normal') . '</span>',
            'abnormal' => '<span class="badge bg-warning text-dark">' . __('radiology_lab.result_abnormal') . '</span>',
            'critical' => '<span class="badge bg-danger">' . __('radiology_lab.result_critical') . '</span>',
            default => '-',
        };
    }

    public function getHasResultAttribute()
    {
        return ! empty($this->result_value);
    }

    protected static function newFactory()
    {
        return \Modules\RadiologyLab\Database\Factories\TestOrderItemFactory::new();
    }
}
