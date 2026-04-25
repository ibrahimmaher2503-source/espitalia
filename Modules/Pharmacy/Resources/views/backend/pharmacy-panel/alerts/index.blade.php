@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_alerts') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="categoryFilter" class="form-control">
                    <option value="stock">{{ __('pharmacy.lbl_stock_alerts') }}</option>
                    <option value="expiry">{{ __('pharmacy.lbl_expiry_alerts') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="resolvedFilter" class="form-control">
                    <option value="">Unresolved</option>
                    <option value="1">Resolved</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead id="tableHead">
                    <tr>
                        <th>Type</th>
                        <th>Product</th>
                        <th>Details</th>
                        <th>{{ __('pharmacy.lbl_status') }}</th>
                        <th>{{ __('messages.created_at') }}</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
        <div id="pagination" class="mt-3"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadData(1);
    document.getElementById('categoryFilter').addEventListener('change', () => loadData(1));
    document.getElementById('resolvedFilter').addEventListener('change', () => loadData(1));

    function loadData(page) {
        const category = document.getElementById('categoryFilter').value;
        const resolved = document.getElementById('resolvedFilter').value;
        fetch(`{{ route('backend.pharmacy-panel-alerts.index_data') }}?page=${page}&alert_category=${category}&resolved=${resolved}`)
            .then(r => r.json()).then(data => {
                const isStock = category === 'stock';
                document.getElementById('tableBody').innerHTML = data.data.map(item => {
                    const productName = isStock
                        ? (item.inventory && item.inventory.product ? item.inventory.product.name_en : '-')
                        : (item.batch && item.batch.inventory && item.batch.inventory.product ? item.batch.inventory.product.name_en : '-');
                    const details = isStock
                        ? `Qty: ${item.current_qty} / Threshold: ${item.threshold}`
                        : `Expiry: ${item.expiry_date} (${item.days_until_expiry} days)`;
                    return `
                    <tr>
                        <td><span class="badge bg-${item.alert_type === 'out_of_stock' || item.alert_type === 'expired' ? 'danger' : 'warning'}">${item.alert_type}</span></td>
                        <td>${productName}</td>
                        <td>${details}</td>
                        <td>${item.is_resolved ? '<span class="badge bg-success">Resolved</span>' : '<span class="badge bg-warning">Active</span>'}</td>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                    </tr>`;
                }).join('');
            });
    }
});
</script>
@endsection
