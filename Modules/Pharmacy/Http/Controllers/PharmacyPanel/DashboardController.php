<?php

namespace Modules\Pharmacy\Http\Controllers\PharmacyPanel;

use Illuminate\Routing\Controller;
use Modules\Pharmacy\Services\PharmacyReportService;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:pharmacy']);
    }

    public function index()
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        // Null-check now handled by EnsurePharmacyAssigned middleware at route group level
        $service = new PharmacyReportService;
        $dashboard = $service->getDashboardData($pharmacyId);
        $page_title = 'Pharmacy Dashboard';
        $page_title_icon = 'fa fa-chart-line';

        return view('pharmacy::backend.pharmacy-panel.dashboard.index', compact('page_title', 'page_title_icon', 'dashboard'));
    }
}
