<?php

namespace Modules\Location\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Location\Models\Governorate;
use Modules\Location\Models\GovernorateCity;
use Yajra\DataTables\DataTables;

class GovernorateCitiesController extends Controller
{
    public function __construct()
    {
        $this->module_title = 'location.lbl_cities';
        $this->module_name = 'governorate-cities';
        $this->module_icon = 'ph ph-buildings';

        view()->share([
            'module_title' => $this->module_title,
            'module_icon' => $this->module_icon,
            'module_name' => $this->module_name,
        ]);
    }

    public function index(Request $request)
    {
        $module_name = $this->module_name;
        $pageTitle = trans('location.lbl_cities');
        $assets = ['datatable'];

        return view('location::backend.governorate-cities.index', compact('pageTitle', 'module_name', 'assets'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        try {
            $query = GovernorateCity::with('governorate');

            if ($request->has('filter') && isset($request->filter['governorate_id']) && $request->filter['governorate_id']) {
                $query->where('governorate_id', $request->filter['governorate_id']);
            }

            return $datatable->eloquent($query)
                ->addColumn('check', function ($data) {
                    return '<input type="checkbox" class="form-check-input select-table-row" id="datatable-row-' . $data->id . '" name="datatable_ids[]" value="' . $data->id . '" data-type="city" onclick="dataTableRowCheck(' . $data->id . ',this)">';
                })
                ->addColumn('governorate_name', function ($data) {
                    return $data->governorate ? e($data->governorate->name) : '-';
                })
                ->editColumn('name_ar', function ($data) {
                    return e($data->name_ar);
                })
                ->editColumn('name_en', function ($data) {
                    return e($data->name_en);
                })
                ->editColumn('updated_at', function ($data) {
                    $diff = Carbon::now()->diffInHours($data->updated_at);
                    return $diff < 25 ? $data->updated_at->diffForHumans() : $data->updated_at->isoFormat('llll');
                })
                ->addColumn('action', function ($data) {
                    $item = $data;
                    return view('location::backend.governorate-cities.datatable.action_column', compact('item'))->render();
                })
                ->addIndexColumn()
                ->rawColumns(['check', 'action'])
                ->toJson();
        } catch (\Exception $e) {
            return response()->json(['error' => __('messages.something_went_wrong')], 500);
        }
    }

    public function create(Request $request)
    {
        $id = $request->id;
        $data = GovernorateCity::find($id);

        $pageTitle = $data ? trans('location.lbl_edit_city') : trans('location.lbl_add_city');

        if (!$data) {
            $data = new GovernorateCity;
        }

        $governorates = Governorate::all();

        return view('location::backend.governorate-cities.create', compact('pageTitle', 'data', 'governorates'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'governorate_id' => 'required|exists:governorates,id',
            'name_ar' => 'required|string|max:100',
            'name_en' => 'required|string|max:100',
        ]);

        $id = $request->id;

        if ($id) {
            $item = GovernorateCity::findOrFail($id);
            $item->update([
                'governorate_id' => $request->governorate_id,
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
            ]);
            $message = __('location.city_update_success');
        } else {
            GovernorateCity::create([
                'governorate_id' => $request->governorate_id,
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
            ]);
            $message = __('location.city_save_success');
        }

        return redirect()->route('backend.governorate-cities.index')->withSuccess($message);
    }

    public function destroy($id)
    {
        $item = GovernorateCity::find($id);
        if ($item) {
            $item->delete();
            $msg = __('location.city_delete_success');
        } else {
            $msg = __('messages.msg_fail_to_delete', ['name' => __('location.lbl_city')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => $item ? true : false]);
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
            case 'delete':
                GovernorateCity::whereIn('id', $ids)->delete();
                $message = __('messages.bulk_service_delete');
                break;
            default:
                return response()->json(['status' => false, 'message' => __('service_providers.invalid_action')]);
        }

        return response()->json(['status' => true, 'message' => $message]);
    }
}
