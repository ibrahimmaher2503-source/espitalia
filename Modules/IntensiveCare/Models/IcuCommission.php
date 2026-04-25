<?php

namespace Modules\IntensiveCare\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IcuCommission extends BaseModel
{
    use HasFactory;

    protected $table = 'icu_commissions';

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

    const TYPE_PERCENTAGE = 'percentage';

    const TYPE_FIXED = 'fixed';

    // ── Relationships ────────────────────────────────────────

    public function hospitals(): HasMany
    {
        return $this->hasMany(Hospital::class, 'commission_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(IcuCommissionEarning::class, 'commission_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    protected static function newFactory()
    {
        return \Modules\IntensiveCare\Database\Factories\IcuCommissionFactory::new();
    }
}
