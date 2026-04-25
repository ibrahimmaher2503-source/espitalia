@extends('backend.layouts.app')
@section('content')
<div class="row" id="dashboardWidgets">
    <div class="col-md-3 mb-3">
        <div class="card text-center p-3">
            <h6>{{ __('pharmacy.lbl_today_orders') }}</h6>
            <h3 id="todayOrders">-</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center p-3">
            <h6>{{ __('pharmacy.lbl_today_revenue') }}</h6>
            <h3 id="todayRevenue">-</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center p-3">
            <h6>{{ __('pharmacy.lbl_pending_orders') }}</h6>
            <h3 id="pendingOrders">-</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card text-center p-3">
            <h6>{{ __('pharmacy.lbl_monthly_revenue') }}</h6>
            <h3 id="monthlyRevenue">-</h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card p-3">
            <h6>{{ __('pharmacy.lbl_low_stock_count') }}</h6>
            <h3 id="lowStockCount" class="text-warning">-</h3>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card p-3">
            <h6>{{ __('pharmacy.lbl_expiring_soon_count') }}</h6>
            <h3 id="expiringSoonCount" class="text-danger">-</h3>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card p-3">
            <h6>{{ __('pharmacy.lbl_pending_prescriptions') }}</h6>
            <h3 id="pendingPrescriptions" class="text-info">-</h3>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card p-3">
            <h5>{{ __('pharmacy.lbl_top_products') }}</h5>
            <table class="table table-sm" id="topProductsTable">
                <thead><tr><th>Product</th><th>Qty Sold</th></tr></thead>
                <tbody id="topProductsBody"></tbody>
            </table>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card p-3">
            <h5>Order Status Breakdown</h5>
            <table class="table table-sm" id="statusBreakdownTable">
                <thead><tr><th>Status</th><th>Count</th></tr></thead>
                <tbody id="statusBreakdownBody"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetch('{{ route('backend.pharmacy-panel-dashboard.data') }}')
        .then(r => r.json()).then(data => {
            if (!data.status) return;
            const d = data.data;
            document.getElementById('todayOrders').textContent = d.today_orders;
            document.getElementById('todayRevenue').textContent = d.today_revenue;
            document.getElementById('pendingOrders').textContent = d.pending_orders;
            document.getElementById('monthlyRevenue').textContent = d.monthly_revenue;
            document.getElementById('lowStockCount').textContent = d.low_stock_count;
            document.getElementById('expiringSoonCount').textContent = d.expiring_soon_count;
            document.getElementById('pendingPrescriptions').textContent = d.pending_prescriptions;

            if (d.top_products) {
                document.getElementById('topProductsBody').innerHTML = d.top_products.map(p => `<tr><td>${p.name}</td><td>${p.quantity_sold}</td></tr>`).join('');
            }
            if (d.order_status_breakdown) {
                document.getElementById('statusBreakdownBody').innerHTML = Object.entries(d.order_status_breakdown).map(([k,v]) => `<tr><td>${k}</td><td>${v}</td></tr>`).join('');
            }
        });
});
</script>
@endsection
