<?php

namespace Modules\Offer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferService extends Model
{
    protected $table = 'offer_services';

    public $timestamps = false;

    protected $fillable = ['offer_id', 'service_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(\Modules\Clinic\Models\ClinicsService::class, 'service_id');
    }
}
