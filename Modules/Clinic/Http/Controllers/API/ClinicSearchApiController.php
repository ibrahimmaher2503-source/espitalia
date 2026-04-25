<?php

namespace Modules\Clinic\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\Clinic\Http\Requests\ClinicSearchRequest;
use Modules\Clinic\Services\ClinicSearchService;
use Modules\Clinic\Transformers\ClinicSearchResource;

class ClinicSearchApiController extends Controller
{
    /**
     * Search clinics by location, name, or specialty.
     */
    public function search(ClinicSearchRequest $request)
    {
        $service = new ClinicSearchService;
        $results = $service->search($request->validated());

        $data = [
            'items' => ClinicSearchResource::collection($results),
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
