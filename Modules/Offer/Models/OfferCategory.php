<?php

namespace Modules\Offer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferCategory extends Model
{
    protected $table = 'offer_categories';

    public $timestamps = false;

    protected $fillable = ['offer_id', 'category_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(\Modules\Clinic\Models\ClinicsCategory::class, 'category_id');
    }
}
