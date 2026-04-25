@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('pharmacy.lbl_prescriptions') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="search" class="form-control" placeholder="{{ __('messages.search') }}...">
            </div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-control">
                    <option value="">{{ __('messages.all') }}</option>
                    <option value="pending">{{ __('pharmacy.status_pending') }}</option>
                    <option value="under_review">{{ __('pharmacy.status_under_review') }}</option>
                    <option value="approved">{{ __('pharmacy.status_approved') }}</option>
                    <option value="rejected">{{ __('pharmacy.status_rejected') }}</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('pharmacy.lbl_prescription_number') }}</th>
                        <th>{{ __('pharmacy.lbl_patient_name') }}</th>
                        <th>{{ __('pharmacy.lbl_pharmacy') }}</th>
                        <th>{{ __('pharmacy.lbl_doctor_name') }}</th>
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
    document.getElementById('search').addEventListener('keyup', () => loadData(1));
    document.getElementById('statusFilter').addEventListener('change', () => loadData(1));

    @include('pharmacy::backend.partials.escape-helper')
    function loadData(page) {
        const search = document.getElementById('search').value;
        const status = document.getElementById('statusFilter').value;
        fetch(`{{ route('backend.pharmacy-prescriptions.index_data') }}?page=${page}&search=${search}&status=${status}`)
            .then(r => r.json())
            .then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map((item, i) => `
                    <tr>
                        <td>${i + 1}</td>
                        <td>${escapeHtml(item.prescription_number)}</td>
                        <td>${item.user ? escapeHtml(item.user.first_name) + ' ' + escapeHtml(item.user.last_name) : '-'}</td>
                        <td>${item.pharmacy ? escapeHtml(item.pharmacy.name_en) : '-'}</td>
                        <td>${escapeHtml(item.doctor_name) || '-'}</td>
                        <td><span class="badge bg-${statusColor(item.status)}">${escapeHtml(item.status)}</span></td>
                        <td>${new Date(item.created_at).toLocaleDateString()}</td>
                        <td>
                            <a href="{{ route('backend.pharmacy-prescriptions.show', '') }}/${item.id}" class="btn btn-info btn-xs">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function statusColor(status) {
        const colors = {pending: 'warning', under_review: 'info', approved: 'success', rejected: 'danger', linked_to_order: 'primary'};
        return colors[status] || 'secondary';
    }
});
</script>
@endsection
