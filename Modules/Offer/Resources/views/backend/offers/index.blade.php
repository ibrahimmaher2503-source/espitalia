@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">{{ __('offer.lbl_offers') }}</h4>
        <div>
            <button id="bulkDeleteBtn" class="btn btn-danger btn-sm d-none">{{ __('messages.delete') }}</button>
            <a href="{{ route('backend.offers.create') }}" class="btn btn-primary btn-sm"><i class="ph ph-plus"></i> {{ __('offer.lbl_create_offer') }}</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-2">
                <select id="statusFilter" class="form-control form-control-sm">
                    <option value="">{{ __('messages.all') }} {{ __('offer.lbl_status') }}</option>
                    <option value="draft">{{ __('offer.status_draft') }}</option>
                    <option value="active">{{ __('offer.status_active') }}</option>
                    <option value="paused">{{ __('offer.status_paused') }}</option>
                    <option value="expired">{{ __('offer.status_expired') }}</option>
                    <option value="exhausted">{{ __('offer.status_exhausted') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <select id="typeFilter" class="form-control form-control-sm">
                    <option value="">{{ __('messages.all') }} {{ __('offer.lbl_discount_type') }}</option>
                    <option value="percentage">{{ __('offer.type_percentage') }}</option>
                    <option value="fixed_amount">{{ __('offer.type_fixed_amount') }}</option>
                    <option value="override_price">{{ __('offer.type_override_price') }}</option>
                    <option value="bundle_price">{{ __('offer.type_bundle_price') }}</option>
                    <option value="buy_x_get_y">{{ __('offer.type_buy_x_get_y') }}</option>
                    <option value="free_addon">{{ __('offer.type_free_addon') }}</option>
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
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>{{ __('offer.lbl_title_en') }}</th>
                        <th>{{ __('offer.lbl_coupon_code') }}</th>
                        <th>{{ __('offer.lbl_discount_type') }}</th>
                        <th>{{ __('offer.lbl_discount_value') }}</th>
                        <th>{{ __('offer.lbl_usage_limit') }}</th>
                        <th>{{ __('offer.lbl_status') }}</th>
                        <th>{{ __('messages.created_at') }}</th>
                        <th>{{ __('messages.actions') }}</th>
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
    document.getElementById('typeFilter').addEventListener('change', () => loadData(1));

    let searchTimeout;
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => loadData(1), 400);
    });

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        toggleBulkBtn();
    });

    document.getElementById('bulkDeleteBtn').addEventListener('click', function() {
        const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
        if (!ids.length || !confirm('Delete selected offers?')) return;
        fetch('{{ route("backend.offers.bulk_action") }}', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
            body: JSON.stringify({ids, action_type: 'delete'})
        }).then(r => r.json()).then(() => loadData(1));
    });

    function loadData(page) {
        const status = document.getElementById('statusFilter').value;
        const type = document.getElementById('typeFilter').value;
        const search = document.getElementById('searchInput').value;
        const params = new URLSearchParams({page, status, discount_type: type, search});

        fetch(`{{ route('backend.offers.index_data') }}?${params}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => `
                    <tr>
                        <td><input type="checkbox" class="row-check" value="${item.id}" onchange="toggleBulkBtn()"></td>
                        <td>
                            <strong>${item.title_en}</strong>
                            <br><small class="text-muted">${item.title_ar || ''}</small>
                        </td>
                        <td>${item.code ? '<code>'+item.code+'</code>' : '<span class=\"badge bg-info\">Auto</span>'}</td>
                        <td><span class="badge bg-secondary">${item.discount_type}</span></td>
                        <td>${item.discount_type === 'percentage' ? item.discount_value+'%' : item.discount_value}</td>
                        <td>${item.used_count}${item.usage_limit_total ? '/'+item.usage_limit_total : ''}</td>
                        <td><span class="badge bg-${statusColor(item.status)}">${item.status}</span></td>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                        <td>
                            <a href="{{ route('backend.offers.show', '') }}/${item.id}" class="btn btn-info btn-xs"><i class="ph ph-eye"></i></a>
                            <a href="{{ route('backend.offers.edit', '') }}/${item.id}" class="btn btn-warning btn-xs"><i class="ph ph-pencil-simple"></i></a>
                            <button class="btn btn-sm btn-${item.status === 'active' ? 'secondary' : 'success'}" onclick="toggleStatus(${item.id})">
                                <i class="ph ph-${item.status === 'active' ? 'pause' : 'play'}"></i>
                            </button>
                            <button class="btn btn-primary btn-xs" onclick="duplicateOffer(${item.id})"><i class="ph ph-copy"></i></button>
                        </td>
                    </tr>
                `).join('');

                renderPagination(data, page);
            });
    }

    function statusColor(s) {
        return {draft:'secondary', scheduled:'info', active:'success', paused:'warning', expired:'danger', exhausted:'dark'}[s] || 'secondary';
    }

    function renderPagination(data, current) {
        let html = '';
        for (let i = 1; i <= data.last_page; i++) {
            html += `<button class="btn btn-sm ${i===current?'btn-primary':'btn-outline-primary'} me-1" onclick="loadData(${i})">${i}</button>`;
        }
        document.getElementById('pagination').innerHTML = html;
    }

    window.loadData = loadData;

    window.toggleStatus = function(id) {
        fetch(`{{ url('app/offers') }}/${id}/update-status`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
        }).then(r => r.json()).then(() => loadData(1));
    };

    window.duplicateOffer = function(id) {
        fetch(`{{ url('app/offers') }}/${id}/duplicate`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'}
        }).then(r => r.json()).then(() => loadData(1));
    };

    window.toggleBulkBtn = function() {
        const checked = document.querySelectorAll('.row-check:checked').length;
        document.getElementById('bulkDeleteBtn').classList.toggle('d-none', checked === 0);
    };
});
</script>
@endsection
