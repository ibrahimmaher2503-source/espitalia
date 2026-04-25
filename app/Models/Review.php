<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends BaseModel
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'reviews';

    protected $fillable = [
        'reviewable_type',
        'reviewable_id',
        'user_id',
        'rating',
        'title',
        'review_msg_ar',
        'review_msg_en',
        'service_id',
        'appointment_id',
        'is_approved',
        'is_anonymous',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'user_id' => 'integer',
        'reviewable_id' => 'integer',
        'service_id' => 'integer',
        'appointment_id' => 'integer',
        'is_approved' => 'boolean',
        'is_anonymous' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function appointment()
    {
        return $this->belongsTo(\Modules\Appointment\Models\Appointment::class, 'appointment_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getReviewMsgAttribute()
    {
        $locale = app()->getLocale();
        $localized = $locale === 'ar' ? $this->review_msg_ar : $this->review_msg_en;

        return $localized ?: ($locale === 'ar' ? $this->review_msg_en : $this->review_msg_ar) ?: '';
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeForReviewable($query, $type, $id)
    {
        return $query->where('reviewable_type', $type)->where('reviewable_id', $id);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
