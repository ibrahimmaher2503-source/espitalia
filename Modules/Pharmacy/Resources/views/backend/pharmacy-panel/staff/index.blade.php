@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4 class="card-title">{{ __('pharmacy.lbl_staff') }}</h4></div>
            <div class="col-md-6 text-end">
                <a href="{{ route('backend.pharmacy-panel-staff.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> {{ __('messages.add_new') }}
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('pharmacy.lbl_name') }}</th>
                        <th>{{ __('pharmacy.lbl_role') }}</th>
                        <th>{{ __('pharmacy.lbl_license_number') }}</th>
                        <th>{{ __('pharmacy.lbl_status') }}</th>
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

    function loadData(page) {
        fetch(`{{ route('backend.pharmacy-panel-staff.index_data') }}?page=${page}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.user ? item.user.first_name + ' ' + item.user.last_name : '-'}</td>
                        <td><span class="badge bg-primary">${item.role}</span></td>
                        <td>${item.license_number || '-'}</td>
                        <td><span class="badge bg-${item.is_active ? 'success' : 'secondary'}">${item.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'}</span></td>
                        <td>
                            <a href="{{ route('backend.pharmacy-panel-staff.edit', '') }}/${item.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>
                            <button onclick="deleteItem(${item.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    window.deleteItem = function(id) {
        if (confirm('{{ __('messages.are_you_sure') }}')) {
            fetch(`{{ route('backend.pharmacy-panel-staff.destroy', '') }}/${id}`, {
                method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(() => loadData(1));
        }
    };
});
</script>
@endsection
