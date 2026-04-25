<?php

namespace Modules\Clinic\Services;

use Modules\Clinic\Models\Clinics;

class ClinicSearchService
{
    /**
     * Search clinics by the given filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters)
    {
        $query = Clinics::query()->with(['governorate', 'governorateCity'])
            ->where('status', 1);

        $query->when($filters['governorate_id'] ?? null, function ($q, $governorateId) {
            $q->where('governorate_id', $governorateId);
        });

        $query->when($filters['city_id'] ?? null, function ($q, $cityId) {
            $q->where('city_id', $cityId);
        });

        $query->when($filters['name'] ?? null, function ($q, $name) {
            $q->where('name', 'LIKE', "%{$name}%");
        });

        $query->when($filters['specialty_id'] ?? null, function ($q, $specialtyId) {
            $q->whereHas('clinicservices', function ($sub) use ($specialtyId) {
                $sub->where('service_id', $specialtyId);
            });
        });

        return $query->orderBy('created_at', 'desc')->paginate(min((int) ($filters['per_page'] ?? 15), 50));
    }
}
