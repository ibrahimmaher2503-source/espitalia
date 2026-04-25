<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyBrand extends BaseModel
{
    protected $table = 'pharmacy_brands';

    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(PharmacyProduct::class, 'brand_id');
    }
}
