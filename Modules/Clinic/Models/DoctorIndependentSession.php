<?php
namespace Modules\Clinic\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Models\User;

class DoctorIndependentSession extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctor_independent_sessions';
    protected $fillable = ['doctor_id', 'day', 'start_time', 'end_time', 'is_holiday', 'breaks', 'status'];

    protected $casts = [
        'breaks' => 'array',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
