<?php

namespace Modules\Clinic\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class DoctorCallService extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctor_call_services';

    protected $fillable = [
        'doctor_id', 'name', 'description', 'call_type',
        'duration_min', 'time_slot', 'charges',
        'discount', 'discount_type', 'discount_value',
        'is_inclusive_tax', 'inclusive_tax', 'inclusive_tax_price',
        'status',
    ];

    protected $casts = [
        'charges' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'inclusive_tax_price' => 'decimal:2',
        'status' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
