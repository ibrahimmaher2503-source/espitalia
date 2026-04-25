<?php

namespace Modules\Offer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferBranch extends Model
{
    protected $table = 'offer_branches';

    public $timestamps = false;

    protected $fillable = ['offer_id', 'branch_id'];

    protected $casts = ['created_at' => 'datetime'];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\Modules\Branch\Models\Branch::class, 'branch_id');
    }
}
