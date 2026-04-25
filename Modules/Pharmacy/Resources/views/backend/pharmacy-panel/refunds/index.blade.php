@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_refunds') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="statusFilter" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="pending">{{ __('pharmacy.refund_status_pending') }}</option>
                    <option value="approved">{{ __('pharmacy.refund_status_approved') }}</option>
                    <option value="processed">{{ __('pharmacy.refund_status_processed') }}</option>
                    <option value="declined">{{ __('pharmacy.refund_status_declined') }}</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('pharmacy.lbl_refund_number') }}</th>
                        <th>{{ __('pharmacy.lbl_order') }}</th>
                        <th>{{ __('pharmacy.lbl_patient_name') }}</th>
                        <th>{{ __('pharmacy.lbl_refund_amount') }}</th>
                        <th>{{ __('pharmacy.lbl_status') }}</th>
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

    function loadData(page) {
        const status = document.getElementById('statusFilter').value;
        fetch(`{{ route('backend.pharmacy-panel-refunds.index_data') }}?page=${page}&status=${status}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.refund_number}</td>
                        <td>#${item.order_id}</td>
                        <td>${item.user ? item.user.first_name + ' ' + item.user.last_name : '-'}</td>
                        <td>${item.refund_amount}</td>
                        <td><span class="badge bg-${statusColor(item.status)}">${item.status}</span></td>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                        <td>
                            <a href="{{ route('backend.pharmacy-panel-refunds.show', '') }}/${item.id}" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function statusColor(status) {
        const colors = {pending: 'warning', approved: 'info', processed: 'success', declined: 'danger'};
        return colors[status] || 'secondary';
    }
});
</script>
@endsection
