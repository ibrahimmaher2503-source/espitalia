@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="statusFilter" class="form-control form-control-sm">
                    <option value="">{{ __('messages.all') }} {{ __('offer.lbl_usage_status') }}</option>
                    <option value="applied">Applied</option>
                    <option value="completed">Completed</option>
                    <option value="reversed">Reversed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" id="searchInput" class="form-control form-control-sm" placeholder="{{ __('messages.search') }}...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('messages.created_at') }}</th>
                        <th>{{ __('offer.lbl_offer') }}</th>
                        <th>{{ __('offer.lbl_coupon_code') }}</th>
                        <th>{{ __('messages.user') }}</th>
                        <th>{{ __('offer.lbl_booking_type') }}</th>
                        <th>{{ __('offer.lbl_original_amount') }}</th>
                        <th>{{ __('offer.lbl_discount_amount') }}</th>
                        <th>{{ __('offer.lbl_final_amount') }}</th>
                        <th>{{ __('offer.lbl_usage_status') }}</th>
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
    document.getElementById('statusFilter').addEventListener('change', () => loadData(1));

    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadData(1), 400);
    });

    function loadData(page) {
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        const params = new URLSearchParams({page, status, search});

        fetch(`{{ route('backend.offer-usage-logs.index_data') }}?${params}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => {
                    const offerTitle = item.offer ? item.offer.title_en : '-';
                    const userName = item.user ? (item.user.first_name + ' ' + item.user.last_name) : '-';
                    const statusColor = {applied:'info', completed:'success', reversed:'warning', cancelled:'danger'}[item.status] || 'secondary';
                    return `
                    <tr>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                        <td>${offerTitle}</td>
                        <td>${item.coupon_code || '-'}</td>
                        <td>${userName}</td>
                        <td>${item.booking_type} #${item.booking_id}</td>
                        <td>${parseFloat(item.original_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.discount_amount).toFixed(2)}</td>
                        <td>${parseFloat(item.final_amount).toFixed(2)}</td>
                        <td><span class="badge bg-${statusColor}">${item.status}</span></td>
                    </tr>`;
                }).join('');

                let pHtml = '';
                for (let i = 1; i <= data.last_page; i++) {
                    pHtml += `<button class="btn btn-sm ${i===page?'btn-primary':'btn-outline-primary'} me-1" onclick="loadData(${i})">${i}</button>`;
                }
                document.getElementById('pagination').innerHTML = pHtml;
            });
    }

    window.loadData = loadData;
});
</script>
@endsection
