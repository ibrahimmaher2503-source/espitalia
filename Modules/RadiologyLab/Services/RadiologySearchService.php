<?php

namespace Modules\RadiologyLab\Services;

use Modules\RadiologyLab\Models\RadiologyCenter;

class RadiologySearchService
{
    /**
     * Search radiology centers with optional filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters)
    {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return RadiologyCenter::query()
            ->with(['governorate', 'city'])
            ->where('status', 1)
            ->when($filters['governorate_id'] ?? null, function ($query, $value) {
                $query->where('governorate_id', $value);
            })
            ->when($filters['city_id'] ?? null, function ($query, $value) {
                $query->where('city_id', $value);
            })
            ->when($filters['scan_type'] ?? null, function ($query, $value) {
                $query->where('scan_type', $value);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
