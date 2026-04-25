<?php

namespace Modules\DoctorVisit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DoctorVisitStatusHistory extends Model
{
    protected $table = 'doctor_visit_status_histories';

    protected $fillable = [
        'visit_request_id',
        'old_status',
        'new_status',
        'changed_by',
        'note',
    ];

    // ── Relationships ─────────────────────────────────────────────

    public function visitRequest()
    {
        return $this->belongsTo(DoctorVisitRequest::class, 'visit_request_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
