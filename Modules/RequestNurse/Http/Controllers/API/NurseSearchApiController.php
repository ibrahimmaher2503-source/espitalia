<?php

namespace Modules\RequestNurse\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Modules\RequestNurse\Http\Requests\NurseSearchRequest;
use Modules\RequestNurse\Services\NurseSearchService;
use Modules\RequestNurse\Transformers\NurseSearchResource;

class NurseSearchApiController extends Controller
{
    public function search(NurseSearchRequest $request)
    {
        $service = new NurseSearchService;
        $results = $service->search($request->validated());

        $data = [
            'items' => NurseSearchResource::collection($results),
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
