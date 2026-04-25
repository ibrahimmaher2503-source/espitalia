<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class TestReport extends BaseModel
{
    use HasFactory;

    protected $table = 'test_reports';

    protected $fillable = [
        'test_order_id',
        'report_type',
        'title',
        'notes',
        'generated_by',
        'report_date',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    protected $appends = ['file_url'];

    // ── Type Constants ─────────────────────────────────────────

    const TYPE_LAB_REPORT = 'lab_report';
    const TYPE_RADIOLOGY_REPORT = 'radiology_report';
    const TYPE_CUSTOM = 'custom';

    // ── Status Constants ───────────────────────────────────────

    const STATUS_DRAFT = 'draft';
    const STATUS_FINAL = 'final';
    const STATUS_AMENDED = 'amended';

    // ── Relationships ──────────────────────────────────────────

    public function testOrder()
    {
        return $this->belongsTo(TestOrder::class, 'test_order_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ── Media ──────────────────────────────────────────────────

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('report_file')->singleFile();
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getFileUrlAttribute()
    {
        $media = $this->getFirstMediaUrl('report_file');

        return isset($media) && ! empty($media) ? $media : null;
    }

    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            self::STATUS_DRAFT => '<span class="badge bg-warning text-dark">' . __('radiology_lab.status_draft') . '</span>',
            self::STATUS_FINAL => '<span class="badge bg-success">' . __('radiology_lab.status_final') . '</span>',
            self::STATUS_AMENDED => '<span class="badge bg-info">' . __('radiology_lab.status_amended') . '</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    protected static function newFactory()
    {
        return \Modules\RadiologyLab\Database\Factories\TestReportFactory::new();
    }
}
