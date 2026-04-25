<?php

namespace Modules\RadiologyLab\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\RadiologyLab\Http\Requests\LabSearchRequest;
use Modules\RadiologyLab\Services\LabSearchService;
use Modules\RadiologyLab\Transformers\LabSearchResource;

class LabSearchApiController extends Controller
{
    /**
     * Search labs by location and test name.
     */
    public function search(LabSearchRequest $request)
    {
        $service = new LabSearchService;
        $results = $service->search($request->validated());

        $data = [
            'items' => LabSearchResource::collection($results->items()),
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
