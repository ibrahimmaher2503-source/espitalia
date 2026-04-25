<?php

namespace Modules\Pharmacy\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyStaff extends BaseModel
{
    protected $table = 'pharmacy_staff';

    protected $fillable = [
        'pharmacy_id', 'user_id', 'role', 'license_number',
        'is_active', 'hired_date', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hired_date' => 'date',
    ];

    const ROLE_OWNER = 'owner';
    const ROLE_MANAGER = 'manager';
    const ROLE_PHARMACIST = 'pharmacist';
    const ROLE_CASHIER = 'cashier';
    const ROLE_DELIVERY = 'delivery';

    const ROLES = [
        self::ROLE_OWNER,
        self::ROLE_MANAGER,
        self::ROLE_PHARMACIST,
        self::ROLE_CASHIER,
        self::ROLE_DELIVERY,
    ];

    const ROLE_LABELS = [
        self::ROLE_OWNER => 'Owner',
        self::ROLE_MANAGER => 'Manager',
        self::ROLE_PHARMACIST => 'Pharmacist',
        self::ROLE_CASHIER => 'Cashier',
        self::ROLE_DELIVERY => 'Delivery',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABELS[$this->role] ?? $this->role;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPharmacy($query, $pharmacyId)
    {
        return $query->where('pharmacy_id', $pharmacyId);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopePharmacists($query)
    {
        return $query->where('role', self::ROLE_PHARMACIST);
    }
}
