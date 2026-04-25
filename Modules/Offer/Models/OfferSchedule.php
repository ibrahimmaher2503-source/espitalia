<?php

namespace Modules\Offer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferSchedule extends Model
{
    protected $table = 'offer_schedules';

    public $timestamps = false;

    protected $fillable = ['offer_id', 'day_of_week', 'start_time', 'end_time'];

    protected $casts = [
        'created_at' => 'datetime',
        'day_of_week' => 'integer',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }
}
