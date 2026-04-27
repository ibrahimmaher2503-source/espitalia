<?php

namespace Modules\IntensiveCare\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\IntensiveCare\Models\Hospital;
use Modules\IntensiveCare\Models\IcuDepartment;
use Modules\IntensiveCare\Transformers\HospitalResource;
use Modules\IntensiveCare\Transformers\IcuDepartmentResource;

class HospitalsApiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Hospital::with(['governorate', 'governorateCity', 'icuDepartments'])
                ->withCount('icuDepartments')
                ->active();

            // Filter by governorate
            if ($request->filled('governorate_id')) {
                $query->where('governorate_id', $request->governorate_id);
            }

            // Filter by city
            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            // Filter by specialty (hospitals that have a department with this specialty)
            if ($request->filled('specialty')) {
                $query->whereHas('icuDepartments', function ($q) use ($request) {
                    $q->where('specialty_type', $request->specialty)->active();
                });
            }

            // Filter by ventilator availability
            if ($request->filled('has_ventilator') && $request->boolean('has_ventilator')) {
                $query->whereHas('icuDepartments', function ($q) {
                    $q->where('has_ventilator', true)->active();
                });
            }

            // Filter by hospital type
            if ($request->filled('hospital_type')) {
                $query->where('hospital_type', $request->hospital_type);
            }

            // Search by name (both AR and EN)
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name_ar', 'LIKE', "%{$search}%")
                        ->orWhere('name_en', 'LIKE', "%{$search}%");
                });
            }

            // Filter by insurance provider
            if ($request->filled('insurance')) {
                $query->hasInsurance($request->insurance);
            }

            // Sorting
            $sortBy = $request->input('sort_by', 'created_at');
            switch ($sortBy) {
                case 'price':
                    $query->orderByRaw('(SELECT MIN(daily_price) FROM icu_departments WHERE icu_departments.hospital_id = hospitals.id AND icu_departments.status = 1) ASC');
                    break;
                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'availability':
                    $query->orderByRaw('(SELECT SUM(available_beds) FROM icu_departments WHERE icu_departments.hospital_id = hospitals.id AND icu_departments.status = 1) DESC');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }

            $perPage = min((int) ($request->per_page ?? 15), 50);
            $hospitals = $query->paginate($perPage);

            return HospitalResource::collection($hospitals)->additional([
                'status' => true,
                'message' => 'Hospital list',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }

    public function show($id)
    {
        try {
            $hospital = Hospital::with(['governorate', 'governorateCity', 'icuDepartments', 'commission', 'media'])
                ->withCount('icuDepartments')
                ->active()
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'Hospital detail',
                'data' => new HospitalResource($hospital),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => __('intensive_care.hospital_not_found')], 404);
        }
    }

    public function departments(Request $request)
    {
        try {
            $query = IcuDepartment::with('hospital')->active();

            if ($request->filled('hospital_id')) {
                $query->where('hospital_id', $request->hospital_id);
            }

            if ($request->filled('specialty')) {
                $query->bySpecialty($request->specialty);
            }

            if ($request->filled('available') && $request->boolean('available')) {
                $query->available();
            }

            if ($request->filled('has_ventilator') && $request->boolean('has_ventilator')) {
                $query->withVentilator();
            }

            $perPage = min((int) ($request->per_page ?? 15), 50);
            $departments = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return IcuDepartmentResource::collection($departments)->additional([
                'status' => true,
                'message' => 'ICU department list',
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['status' => false, 'message' => __('messages.something_wrong')], 500);
        }
    }
}
