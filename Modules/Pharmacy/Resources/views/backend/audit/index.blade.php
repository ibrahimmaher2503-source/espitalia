@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_audit_log') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-3">
                <select id="actionFilter" class="form-control">
                    <option value="">{{ __('messages.all') }} Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                    <option value="status_changed">Status Changed</option>
                    <option value="stock_adjusted">Stock Adjusted</option>
                    <option value="order_placed">Order Placed</option>
                    <option value="refund_processed">Refund Processed</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="pharmacyFilter" class="form-control">
                    <option value="">{{ __('messages.all') }} Pharmacies</option>
                    @foreach(\Modules\Pharmacy\Models\Pharmacy::where('is_active', true)->get() as $pharmacy)
                        <option value="{{ $pharmacy->id }}">{{ $pharmacy->name_en }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('messages.date') }}</th>
                        <th>User</th>
                        <th>{{ __('pharmacy.lbl_action') }}</th>
                        <th>{{ __('pharmacy.lbl_entity') }}</th>
                        <th>Details</th>
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
    document.getElementById('actionFilter').addEventListener('change', () => loadData(1));
    document.getElementById('pharmacyFilter').addEventListener('change', () => loadData(1));

    function loadData(page) {
        const action = document.getElementById('actionFilter').value;
        const pharmacyId = document.getElementById('pharmacyFilter').value;
        fetch(`{{ route('backend.pharmacy-audit.index_data') }}?page=${page}&action=${action}&pharmacy_id=${pharmacyId}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => `
                    <tr>
                        <td>${new Date(item.created_at).toLocaleString()}</td>
                        <td>${item.user ? item.user.first_name + ' ' + item.user.last_name : 'System'}</td>
                        <td><span class="badge bg-secondary">${item.action}</span></td>
                        <td>${item.auditable_type ? item.auditable_type.split('\\\\').pop() : '-'} #${item.auditable_id}</td>
                        <td><small>${item.new_values ? JSON.stringify(item.new_values).substring(0, 100) : '-'}</small></td>
                    </tr>
                `).join('');
            });
    }
});
</script>
@endsection
