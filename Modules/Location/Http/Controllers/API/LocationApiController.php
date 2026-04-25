<?php

namespace Modules\Location\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Modules\Location\Transformers\GovernorateCityResource;
use Modules\Location\Transformers\GovernorateResource;

class LocationApiController extends Controller
{
    /**
     * Return all governorates.
     */
    public function governorates()
    {
        $governorates = Governorate::all();

        return $this->sendResponse(
            GovernorateResource::collection($governorates),
            __('location.lbl_governorates_list')
        );
    }

    /**
     * Return cities filtered by governorate_id.
     */
    public function cities(Request $request)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
        ]);

        $cities = GovernorateCity::where('governorate_id', $request->governorate_id)->get();

        return $this->sendResponse(
            GovernorateCityResource::collection($cities),
            __('location.lbl_cities_list')
        );
    }
}
