<?php
namespace Modules\Clinic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\User;

class DoctorIndependentService extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctor_independent_services';
    protected $fillable = [
        'doctor_id', 'name', 'description', 'duration_min', 'time_slot',
        'charges', 'discount', 'discount_type', 'discount_value',
        'is_inclusive_tax', 'inclusive_tax', 'inclusive_tax_price', 'status',
    ];

    protected $casts = [
        'charges' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'inclusive_tax_price' => 'decimal:2',
    ];

    public function setChargesAttribute($value)
    {
        $this->attributes['charges'] = round($value, 2);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
