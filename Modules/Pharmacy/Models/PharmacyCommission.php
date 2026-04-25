<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PharmacyCommission extends BaseModel
{
    protected $table = 'pharmacy_commissions';

    protected $fillable = [
        'title',
        'commission_type',
        'commission_value',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'commission_value' => 'decimal:2',
    ];

    // Commission type constants
    const TYPE_PERCENTAGE = 'percentage';

    const TYPE_FIXED = 'fixed';

    // Relationships
    public function pharmacies(): HasMany
    {
        return $this->hasMany(Pharmacy::class, 'commission_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(PharmacyCommissionEarning::class, 'commission_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
