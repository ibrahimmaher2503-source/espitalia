<?php

namespace Modules\HomeHealthcare\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\HomeHealthcare\Models\HomeHealthcare;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Yajra\DataTables\DataTables;

class HomeHealthcareController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'location.home_healthcare_plural';
        $this->module_name = 'home-healthcares';
        $this->module_icon = 'ph ph-house-line';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $module_name = $this->module_name;
        $pageTitle = trans('location.home_healthcare_plural');
        $assets = ['datatable'];

        return view('homehealthcare::backend.home-healthcares.index', compact('pageTitle', 'module_name', 'assets', 'filter'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $query = HomeHealthcare::with(['governorate', 'city']);

            if ($request->has('filter') && isset($request->filter['column_status'])) {
                $query->where('status', $request->filter['column_status']);
            }

            if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
                $query->withTrashed();
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="home-healthcare" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->editColumn('name_ar', function ($data) {
                    return e($data->name_ar);
                })
                ->editColumn('name_en', function ($data) {
                    return e($data->name_en);
                })
                ->addColumn('governorate_name', function ($data) {
                    return $data->governorate ? e($data->governorate->name) : '-';
                })
                ->editColumn('status', function ($row) {
                    $checked = $row->status ? 'checked="checked"' : '';
                    return '
                        <div class="form-check form-switch">
                            <input type="checkbox" data-url="' . route('backend.home-healthcares.update_status', $row->id) . '" data-token="' . csrf_token() . '" class="switch-status-change form-check-input" id="datatable-row-' . $row->id . '" name="status" value="' . $row->id . '" ' . $checked . '>
                        </div>
                    ';
                })
                ->editColumn('updated_at', function ($data) {
                    $diff = Carbon::now()->diffInHours($data->updated_at);
                    return $diff < 25 ? $data->updated_at->diffForHumans() : $data->updated_at->isoFormat('llll');
                })
                ->addColumn('action', function ($data) {
                    $item = $data;
                    return view('homehealthcare::backend.home-healthcares.datatable.action_column', compact('item'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'status', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = HomeHealthcare::find($id);

        $pageTitle = trans('location.home_healthcare_singular');

        if ($data) {
            $pageTitle = trans('location.home_healthcare_singular') . ' - ' . __('messages.edit');
        } else {
            $data = new HomeHealthcare;
        }

        $governorates = Governorate::all();
        $cities = collect();
        if ($data->governorate_id) {
            $cities = GovernorateCity::where('governorate_id', $data->governorate_id)->get();
        }

        return view('homehealthcare::backend.home-healthcares.create', compact('pageTitle', 'data', 'governorates', 'cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $id = $request->id;

        if ($id) {
            $item = HomeHealthcare::findOrFail($id);
            $item->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'service_type' => $request->service_type,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'status' => $request->status ?? 1,
            ]);
            $message = __('location.home_healthcare_update_success');
        } else {
            HomeHealthcare::create([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'service_type' => $request->service_type,
                'governorate_id' => $request->governorate_id,
                'city_id' => $request->city_id,
                'status' => $request->status ?? 1,
            ]);
            $message = __('location.home_healthcare_save_success');
        }

        return redirect()->route('backend.home-healthcares.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $item = HomeHealthcare::find($id);
        if ($item) {
            $item->delete();
            $msg = __('location.home_healthcare_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('location.home_healthcare_singular')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $item ? true : false]);
    }

    public function update_status(Request $request, HomeHealthcare $id)
    {
        $request->validate(['status' => 'required|in:0,1']);
        $id->update(['status' => $request->status]);

        return response()->json(['status' => true, 'message' => __('messages.status_update')]);
    }

    public function bulk_action(Request $request)
    {
        $request->validate([
            'rowIds' => 'required|string',
            'type' => 'required|string',
        ]);

        $ids = explode(',', $request->rowIds);
        $actionType = $request->action_type;
        $message = __('messages.bulk_update');

        switch ($actionType) {
            case 'change-status':
                HomeHealthcare::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = __('messages.bulk_service_update');
                break;
            case 'delete':
                HomeHealthcare::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;
            case 'restore':
                HomeHealthcare::withTrashed()->whereIn('id', $ids)->restore();
                $message = __('messages.bulk_service_restore');
                break;
            case 'permanently-delete':
                HomeHealthcare::withTrashed()->whereIn('id', $ids)->forceDelete();
                $message = __('messages.bulk_service_permanently_deleted');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
