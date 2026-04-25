@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label">{{ __('offer.lbl_starts_at') }}</label>
                <input type="date" id="fromDate" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label">{{ __('offer.lbl_ends_at') }}</label>
                <input type="date" id="toDate" class="form-control form-control-sm">
            </div>
            <div class="col-md-2 pt-4">
                <button class="btn btn-primary btn-sm mt-2" onclick="loadAnalytics()">{{ __('messages.filter') }}</button>
            </div>
        </div>

        {{-- Dashboard Stats --}}
        <div class="row mb-4" id="statsCards">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 id="activeCount">0</h3>
                        <small>{{ __('offer.lbl_active_offers') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 id="totalRedemptions">0</h3>
                        <small>{{ __('offer.lbl_total_redemptions') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3 id="totalDiscount">0</h3>
                        <small>{{ __('offer.lbl_total_discount_given') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 id="avgDiscount">0</h3>
                        <small>{{ __('offer.lbl_avg_discount') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Offers Table --}}
        <h5>{{ __('offer.lbl_top_offers') }}</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{ __('offer.lbl_offer') }}</th>
                    <th>{{ __('offer.lbl_total_redemptions') }}</th>
                    <th>{{ __('offer.lbl_total_discount_given') }}</th>
                </tr>
            </thead>
            <tbody id="topOffersBody"></tbody>
        </table>

        {{-- Revenue Impact --}}
        <h5 class="mt-4">{{ __('offer.lbl_revenue_impact') }}</h5>
        <div id="revenueImpact" class="row">
            <div class="col-md-4"><strong>Original Revenue:</strong> <span id="origRevenue">0</span></div>
            <div class="col-md-4"><strong>{{ __('offer.lbl_total_discount_given') }}:</strong> <span id="impactDiscount">0</span></div>
            <div class="col-md-4"><strong>Impact:</strong> <span id="impactPct">0%</span></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadAnalytics);

function loadAnalytics() {
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;
    const params = new URLSearchParams({from, to});

    fetch(`{{ route('backend.offer-analytics.data') }}?${params}`)
        .then(r => r.json()).then(res => {
            const d = res.data;
            document.getElementById('activeCount').textContent = d.dashboard.active_count;
            document.getElementById('totalRedemptions').textContent = d.dashboard.total_redemptions;
            document.getElementById('totalDiscount').textContent = parseFloat(d.dashboard.total_discount_given || 0).toFixed(2);
            document.getElementById('avgDiscount').textContent = parseFloat(d.dashboard.avg_discount_per_use || 0).toFixed(2);

            document.getElementById('topOffersBody').innerHTML = (d.top_offers || []).map(o => `
                <tr>
                    <td>${o.offer ? (o.offer.title_en || '') : 'Offer #'+o.offer_id}</td>
                    <td>${o.usage_count}</td>
                    <td>${parseFloat(o.total_discount || 0).toFixed(2)}</td>
                </tr>
            `).join('');

            const ri = d.revenue_impact || {};
            document.getElementById('origRevenue').textContent = parseFloat(ri.original_revenue || 0).toFixed(2);
            document.getElementById('impactDiscount').textContent = parseFloat(ri.total_discount || 0).toFixed(2);
            document.getElementById('impactPct').textContent = (ri.discount_percentage || 0) + '%';
        });
}
</script>
@endsection
