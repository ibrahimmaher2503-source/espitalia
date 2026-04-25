<?php

namespace Modules\Offer\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferAuditLog extends Model
{
    protected $table = 'offer_audit_logs';

    public $timestamps = false;

    protected $fillable = [
        'offer_id', 'user_id', 'action',
        'old_values', 'new_values',
        'ip_address', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
