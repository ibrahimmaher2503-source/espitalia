<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\DeliveryZoneRequest;
use Modules\Pharmacy\Models\PharmacyDeliveryZone;

class DeliveryZoneController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
        $this->middleware('can:view_pharmacy_delivery_zones')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_delivery_zones')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_delivery_zones')->only(['edit', 'update', 'update_status']);
        $this->middleware('can:delete_pharmacy_delivery_zones')->only(['destroy']);
    }

    public function index()
    {
        $page_title = 'Delivery Zones';
        $page_title_icon = 'fa fa-map-marker';

        return view('pharmacy::backend.pharmacy-panel.delivery-zones.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyDeliveryZone::with(['governorate', 'governorateCity'])
            ->where('pharmacy_id', $pharmacyId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%$search%")
                    ->orWhere('name_en', 'like', "%$search%");
            });
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $zones = $query->orderBy('sort_order')->paginate($perPage);

        return response()->json([
            'data' => $zones->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $zones->currentPage(),
            'last_page' => $zones->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = 'Add Delivery Zone';

        return view('pharmacy::backend.pharmacy-panel.delivery-zones.create', compact('page_title'));
    }

    public function store(DeliveryZoneRequest $request)
    {
        $data = $request->validated();
        $data['pharmacy_id'] = auth()->user()->pharmacy_id;

        PharmacyDeliveryZone::create($data);

        return response()->json(['status' => true, 'message' => 'Delivery zone created']);
    }

    public function edit($id)
    {
        $zone = PharmacyDeliveryZone::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $page_title = 'Edit Delivery Zone';

        return view('pharmacy::backend.pharmacy-panel.delivery-zones.create', compact('page_title', 'zone'));
    }

    public function update($id, DeliveryZoneRequest $request)
    {
        $zone = PharmacyDeliveryZone::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $zone->update($request->validated());

        return response()->json(['status' => true, 'message' => 'Delivery zone updated']);
    }

    public function destroy($id)
    {
        PharmacyDeliveryZone::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id)->delete();

        return response()->json(['status' => true, 'message' => 'Delivery zone deleted']);
    }

    public function update_status($id)
    {
        $zone = PharmacyDeliveryZone::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $zone->update(['is_active' => ! $zone->is_active]);

        return response()->json(['status' => true, 'message' => 'Status updated']);
    }
}
