<?php

namespace Modules\RadiologyLab\Services;

use Modules\RadiologyLab\Models\Lab;

class LabSearchService
{
    /**
     * Search labs with optional filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function search(array $filters)
    {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        return Lab::query()
            ->with(['governorate', 'city'])
            ->where('status', 1)
            ->when($filters['governorate_id'] ?? null, function ($query, $value) {
                $query->where('governorate_id', $value);
            })
            ->when($filters['city_id'] ?? null, function ($query, $value) {
                $query->where('city_id', $value);
            })
            ->when($filters['test_name'] ?? null, function ($query, $value) {
                $escaped = str_replace(['%', '_'], ['\%', '\_'], $value);
                $query->where(function ($q) use ($escaped) {
                    $q->where('name_en', 'LIKE', "%{$escaped}%")
                        ->orWhere('name_ar', 'LIKE', "%{$escaped}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
