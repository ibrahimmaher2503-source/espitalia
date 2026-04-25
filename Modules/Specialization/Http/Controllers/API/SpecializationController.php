<?php

namespace Modules\Specialization\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Specialization\Models\Specialization;
use Modules\Specialization\Transformers\SpecializationResource;

class SpecializationController extends Controller
{
    public function homeConfig()
    {
        $specializations = Specialization::active()
            ->where('show_on_home', true)
            ->orderBy('display_order')
            ->orderBy('name_en')
            ->limit(8)
            ->get();

        $data = $specializations->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'name_ar' => $s->name_ar,
            'name_en' => $s->name_en,
            'icon' => $s->icon,
            'icon_url' => $s->icon_image ? url('storage/'.$s->icon_image) : null,
            'display_order' => $s->display_order,
        ]);

        return response()->json([
            'specialties' => $data,
        ]);
    }

    public function index(Request $request)
    {
        $query = Specialization::active()
            ->withCount(['doctors', 'nurses'])
            ->with(['parent', 'children' => fn ($q) => $q->active()->withCount(['doctors', 'nurses'])]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'LIKE', "%{$search}%")
                    ->orWhere('name_en', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('parent_id')) {
            $parentId = $request->parent_id;
            if ($parentId === 'null' || $parentId === '') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        $perPage = $request->input('per_page', 15);
        $specializations = $query->orderBy('name_en')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => SpecializationResource::collection($specializations),
            'meta' => [
                'current_page' => $specializations->currentPage(),
                'last_page' => $specializations->lastPage(),
                'per_page' => $specializations->perPage(),
                'total' => $specializations->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $specialization = Specialization::withCount(['doctors', 'nurses'])
            ->with(['parent', 'children' => fn ($q) => $q->active()->withCount(['doctors', 'nurses'])])
            ->find($id);

        if (! $specialization) {
            return response()->json(['status' => false, 'message' => 'Specialization not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data' => new SpecializationResource($specialization),
        ]);
    }

    public function doctors(Request $request, $id)
    {
        $specialization = Specialization::find($id);

        if (! $specialization) {
            return response()->json(['status' => false, 'message' => 'Specialization not found'], 404);
        }

        $perPage = $request->input('per_page', 15);
        $doctors = $specialization->doctors()
            ->with(['user', 'specializations'])
            ->paginate($perPage);

        $data = $doctors->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'doctor_id' => $doctor->doctor_id,
                'first_name' => optional($doctor->user)->first_name,
                'last_name' => optional($doctor->user)->last_name,
                'full_name' => optional($doctor->user)->first_name.' '.optional($doctor->user)->last_name,
                'email' => optional($doctor->user)->email,
                'specializations' => $doctor->specializations->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                ]),
                'profile_image' => optional($doctor->user)->profile_image ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'per_page' => $doctors->perPage(),
                'total' => $doctors->total(),
            ],
        ]);
    }

    public function nurses(Request $request, $id)
    {
        $specialization = Specialization::find($id);

        if (! $specialization) {
            return response()->json(['status' => false, 'message' => 'Specialization not found'], 404);
        }

        $perPage = $request->input('per_page', 15);
        $nurses = $specialization->nurses()
            ->with(['user', 'specializations'])
            ->paginate($perPage);

        $data = $nurses->map(function ($nurse) {
            return [
                'id' => $nurse->id,
                'nurse_id' => $nurse->nurse_id,
                'name' => optional($nurse->user)->first_name.' '.optional($nurse->user)->last_name,
                'specialization' => $nurse->specialization,
                'specializations' => $nurse->specializations->map(fn ($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                ]),
                'experience' => $nurse->experience,
                'hourly_rate' => $nurse->hourly_rate,
                'availability_status' => $nurse->availability_status,
                'profile_image' => optional($nurse->user)->profile_image ?? null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $nurses->currentPage(),
                'last_page' => $nurses->lastPage(),
                'per_page' => $nurses->perPage(),
                'total' => $nurses->total(),
            ],
        ]);
    }
}
