<?php

namespace Modules\HomeHealthcare\Services;

use Modules\HomeHealthcare\Models\HomeHealthcare;

class HomeHealthcareSearchService
{
    public function search(array $filters)
    {
        return HomeHealthcare::with(['governorate', 'city'])
            ->when(isset($filters['governorate_id']), function ($query) use ($filters) {
                $query->where('governorate_id', $filters['governorate_id']);
            })
            ->when(isset($filters['city_id']), function ($query) use ($filters) {
                $query->where('city_id', $filters['city_id']);
            })
            ->when(isset($filters['service_type']), function ($query) use ($filters) {
                $query->where('service_type', $filters['service_type']);
            })
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(min((int) ($filters['per_page'] ?? 15), 50));
    }
}
