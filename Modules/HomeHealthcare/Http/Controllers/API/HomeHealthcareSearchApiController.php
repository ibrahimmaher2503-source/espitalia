<?php

namespace Modules\HomeHealthcare\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\HomeHealthcare\Http\Requests\HomeHealthcareSearchRequest;
use Modules\HomeHealthcare\Services\HomeHealthcareSearchService;
use Modules\HomeHealthcare\Transformers\HomeHealthcareResource;

class HomeHealthcareSearchApiController extends Controller
{
    public function search(HomeHealthcareSearchRequest $request)
    {
        $service = new HomeHealthcareSearchService;
        $results = $service->search($request->validated());

        $data = [
            'items' => HomeHealthcareResource::collection($results),
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
