<?php

namespace Modules\RequestNurse\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Transformers\NurseResource;

class NursesApiController extends Controller
{
    public function index(Request $request)
    {
        $query = Nurse::with('user')->active();

        if ($request->has('availability_status') && $request->availability_status !== '') {
            $query->where('availability_status', $request->availability_status);
        }

        if ($request->has('service_area') && $request->service_area !== '') {
            $search = $request->service_area;
            $query->where(function ($q) use ($search) {
                $q->where('service_area_ar', 'LIKE', '%'.$search.'%')
                    ->orWhere('service_area_en', 'LIKE', '%'.$search.'%');
            });
        }

        if ($request->has('specialization') && $request->specialization !== '') {
            $search = $request->specialization;
            $query->where(function ($q) use ($search) {
                $q->where('specialization_ar', 'LIKE', '%'.$search.'%')
                    ->orWhere('specialization_en', 'LIKE', '%'.$search.'%');
            });
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('specialization_ar', 'LIKE', "%$search%")
                    ->orWhere('specialization_en', 'LIKE', "%$search%")
                    ->orWhere('service_area_ar', 'LIKE', "%$search%")
                    ->orWhere('service_area_en', 'LIKE', "%$search%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                    });
            });
        }

        $perPage = min((int) ($request->per_page ?? 15), 50);
        $data = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return NurseResource::collection($data);
    }

    public function show($id)
    {
        $nurse = Nurse::with('user')->where('nurse_id', $id)->firstOrFail();

        return response()->json([
            'data' => new NurseResource($nurse),
            'status' => true,
        ], 200);
    }
}
