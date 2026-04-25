<?php

namespace Modules\Offer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferCustomerSegment extends Model
{
    protected $table = 'offer_customer_segments';

    public $timestamps = false;

    protected $fillable = ['offer_id', 'segment_type', 'segment_value'];

    protected $casts = ['created_at' => 'datetime'];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
