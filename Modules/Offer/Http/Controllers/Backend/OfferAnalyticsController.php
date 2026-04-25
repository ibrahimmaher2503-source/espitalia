<?php

namespace Modules\Offer\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Offer\Services\OfferReportService;

class OfferAnalyticsController extends Controller
{
    protected OfferReportService $reportService;

    public function __construct(OfferReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->middleware('can:view_offer_analytics');
    }

    public function index()
    {
        $page_title = __('offer.lbl_offer_analytics');

        return view('offer::backend.analytics.index', compact('page_title'));
    }

    public function data(Request $request)
    {
        $data = [
            'dashboard' => $this->reportService->getDashboardStats(),
            'top_offers' => $this->reportService->getTopPerformingOffers($request->input('limit', 10)),
            'revenue_impact' => $this->reportService->getRevenueImpact(
                $request->input('from'),
                $request->input('to')
            ),
            'expiring_soon' => $this->reportService->getExpiringSoonOffers($request->input('days', 7)),
        ];

        if ($request->filled('offer_id')) {
            $offerId = $request->offer_id;
            $data['timeline'] = $this->reportService->getRedemptionTimeline(
                $offerId,
                $request->input('period', 'daily'),
                $request->input('from'),
                $request->input('to')
            );
            $data['branch_performance'] = $this->reportService->getBranchPerformance($offerId);
            $data['service_performance'] = $this->reportService->getServicePerformance($offerId);
            $data['customer_breakdown'] = $this->reportService->getCustomerBreakdown($offerId);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }
}
