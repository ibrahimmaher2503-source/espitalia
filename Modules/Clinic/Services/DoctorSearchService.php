<?php

namespace Modules\Clinic\Services;

use Modules\Clinic\Models\Doctor;

class DoctorSearchService
{
    /**
     * Search doctors with optional filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters)
    {
        return Doctor::query()
            ->with(['governorate', 'city', 'user'])
            ->where('status', 1)
            ->when($filters['governorate_id'] ?? null, function ($query, $value) {
                $query->where('governorate_id', $value);
            })
            ->when($filters['city_id'] ?? null, function ($query, $value) {
                $query->where('city_id', $value);
            })
            ->when($filters['name'] ?? null, function ($query, $value) {
                $query->whereHas('user', function ($q) use ($value) {
                    $q->where(function ($q2) use ($value) {
                        $q2->where('first_name_ar', 'LIKE', "%{$value}%")
                            ->orWhere('first_name_en', 'LIKE', "%{$value}%")
                            ->orWhere('last_name_ar', 'LIKE', "%{$value}%")
                            ->orWhere('last_name_en', 'LIKE', "%{$value}%");
                    });
                });
            })
            ->when($filters['gender'] ?? null, function ($query, $value) {
                $query->whereHas('user', function ($q) use ($value) {
                    $q->where('gender', $value);
                });
            })
            ->when($filters['specialty_id'] ?? null, function ($query, $value) {
                $query->whereHas('doctorService', function ($q) use ($value) {
                    $q->where('service_id', $value);
                });
            })
            ->when($filters['min_price'] ?? $filters['max_price'] ?? null, function ($query) use ($filters) {
                $query->whereHas('doctorService', function ($q) use ($filters) {
                    if (isset($filters['min_price'])) {
                        $q->where('charges', '>=', $filters['min_price']);
                    }
                    if (isset($filters['max_price'])) {
                        $q->where('charges', '<=', $filters['max_price']);
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 15), 50));
    }
}
