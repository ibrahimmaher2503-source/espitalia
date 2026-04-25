<?php

namespace Modules\RequestNurse\Services;

use Modules\RequestNurse\Models\Nurse;

class NurseSearchService
{
    public function search(array $filters)
    {
        return Nurse::query()
            ->with(['governorate', 'city', 'user'])
            ->where('status', 1)
            ->when($filters['governorate_id'] ?? null, fn ($q, $v) => $q->where('governorate_id', $v))
            ->when($filters['city_id'] ?? null, fn ($q, $v) => $q->where('city_id', $v))
            ->when($filters['specialty'] ?? null, fn ($q, $v) => $q->where(function ($q) use ($v) {
                $escaped = str_replace(['%', '_'], ['\%', '\_'], $v);
                $q->where('specialization_ar', 'LIKE', "%{$escaped}%")
                    ->orWhere('specialization_en', 'LIKE', "%{$escaped}%");
            }))
            ->when($filters['gender'] ?? null, fn ($q, $v) => $q->whereHas('user', fn ($uq) => $uq->where('gender', $v)))
            ->when($filters['availability'] ?? null, fn ($q, $v) => $q->where('availability_status', $v))
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }
}
