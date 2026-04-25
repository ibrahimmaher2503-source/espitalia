<?php

namespace Modules\Clinic\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class DoctorCallSession extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $table = 'doctor_call_sessions';

    protected $fillable = [
        'doctor_id', 'day', 'start_time', 'end_time',
        'is_holiday', 'breaks', 'status',
    ];

    protected $casts = [
        'breaks' => 'array',
        'is_holiday' => 'integer',
        'status' => 'integer',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}
