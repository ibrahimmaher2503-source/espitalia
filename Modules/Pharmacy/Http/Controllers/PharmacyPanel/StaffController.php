<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Http\Requests\PharmacyStaffRequest;
use Modules\Pharmacy\Models\PharmacyStaff;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
        $this->middleware('can:view_pharmacy_staff')->only(['index', 'index_data']);
        $this->middleware('can:add_pharmacy_staff')->only(['create', 'store']);
        $this->middleware('can:edit_pharmacy_staff')->only(['edit', 'update']);
        $this->middleware('can:delete_pharmacy_staff')->only(['destroy']);
    }

    public function index()
    {
        $page_title = 'Staff';
        $page_title_icon = 'fa fa-users';

        return view('pharmacy::backend.pharmacy-panel.staff.index', compact('page_title', 'page_title_icon'));
    }

    public function index_data(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $query = PharmacyStaff::with('user')->where('pharmacy_id', $pharmacyId);

        if ($request->filled('search')) {
            $query->whereHas('user', fn ($q) => $q->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $total = $query->count();
        $perPage = $request->get('per_page', 10);
        $staff = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => $staff->items(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $staff->currentPage(),
            'last_page' => $staff->lastPage(),
        ]);
    }

    public function create()
    {
        $page_title = 'Add Staff';
        $page_title_icon = 'fa fa-plus';

        return view('pharmacy::backend.pharmacy-panel.staff.create', compact('page_title', 'page_title_icon'));
    }

    public function store(PharmacyStaffRequest $request)
    {
        $data = $request->validated();
        $data['pharmacy_id'] = auth()->user()->pharmacy_id;

        PharmacyStaff::create($data);

        return response()->json(['status' => true, 'message' => 'Staff added successfully']);
    }

    public function edit($id)
    {
        $staff = PharmacyStaff::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $page_title = 'Edit Staff';
        $page_title_icon = 'fa fa-edit';

        return view('pharmacy::backend.pharmacy-panel.staff.create', compact('page_title', 'page_title_icon', 'staff'));
    }

    public function update($id, PharmacyStaffRequest $request)
    {
        $staff = PharmacyStaff::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $staff->update($request->validated());

        return response()->json(['status' => true, 'message' => 'Staff updated successfully']);
    }

    public function destroy($id)
    {
        $staff = PharmacyStaff::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $staff->delete();

        return response()->json(['status' => true, 'message' => 'Staff removed successfully']);
    }

    public function update_status($id)
    {
        $staff = PharmacyStaff::where('pharmacy_id', auth()->user()->pharmacy_id)->findOrFail($id);
        $staff->update(['is_active' => ! $staff->is_active]);

        return response()->json(['status' => true, 'message' => 'Status updated']);
    }
}
