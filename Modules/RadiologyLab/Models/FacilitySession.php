<?php

namespace Modules\RadiologyLab\Models;

use App\Models\BaseModel;

class FacilitySession extends BaseModel
{
    protected $table = 'facility_sessions';

    protected $fillable = [
        'facility_type',
        'facility_id',
        'day',
        'start_time',
        'end_time',
        'slot_duration',
        'breaks',
        'is_holiday',
        'status',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
        'breaks' => 'array',
        'status' => 'boolean',
        'slot_duration' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeForFacility($query, string $type, int $id)
    {
        return $query->where('facility_type', $type)->where('facility_id', $id);
    }

    public function scopeForDay($query, string $day)
    {
        return $query->where('day', $day);
    }

    public function getFacilityNameAttribute(): string
    {
        if ($this->facility_type === 'lab') {
            $facility = Lab::find($this->facility_id);
        } else {
            $facility = RadiologyCenter::find($this->facility_id);
        }

        return $facility ? $facility->name : '-';
    }
}
