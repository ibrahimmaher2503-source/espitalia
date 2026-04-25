<?php

namespace Modules\RadiologyLab\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\RadiologyLab\Http\Requests\RadiologySearchRequest;
use Modules\RadiologyLab\Services\RadiologySearchService;
use Modules\RadiologyLab\Transformers\RadiologyCenterResource;

class RadiologySearchApiController extends Controller
{
    /**
     * Search radiology centers by location and scan type.
     */
    public function search(RadiologySearchRequest $request)
    {
        $service = new RadiologySearchService;
        $results = $service->search($request->validated());

        $data = [
            'items' => RadiologyCenterResource::collection($results->items()),
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
