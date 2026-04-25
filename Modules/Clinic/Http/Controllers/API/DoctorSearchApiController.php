<?php

namespace Modules\Clinic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\Clinic\Http\Requests\DoctorSearchRequest;
use Modules\Clinic\Services\DoctorSearchService;
use Modules\Clinic\Transformers\DoctorSearchResource;

class DoctorSearchApiController extends Controller
{
    /**
     * Search doctors by location and other filters.
     */
    public function search(DoctorSearchRequest $request)
    {
        $service = new DoctorSearchService;
        $results = $service->search($request->validated());

        $data = [
            'items' => DoctorSearchResource::collection($results),
            'pagination' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ];

        return $this->sendResponse($data, '');
    }
}
