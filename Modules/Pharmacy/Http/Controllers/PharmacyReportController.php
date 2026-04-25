<?php

namespace Modules\Pharmacy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Pharmacy\Services\PharmacyReportService;

class PharmacyReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:view_pharmacy_reports');
    }

    public function index()
    {
        $page_title = 'Pharmacy Reports';
        $page_title_icon = 'fa fa-chart-bar';

        return view('pharmacy::backend.reports.index', compact('page_title', 'page_title_icon'));
    }

    public function revenue(Request $request)
    {
        $request->validate([
            'pharmacy_id' => 'nullable|integer|exists:pharmacies,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'group_by' => 'nullable|in:daily,weekly,monthly',
        ]);

        $service = new PharmacyReportService;
        $data = $service->getRevenueReport($request->only(['pharmacy_id', 'from', 'to', 'group_by']));

        return response()->json(['status' => true, 'data' => $data]);
    }
}
