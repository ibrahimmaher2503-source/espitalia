@extends('backend.layouts.app')

@section('title') {{ $module_title }} @endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="header-title">
                        <h4 class="card-title">{{ $module_title }}</h4>
                    </div>
                    <div class="card-header-toolbar d-flex align-items-center">
                        <a href="{{ route('backend.branches.create') }}" class="btn btn-primary">
                            <i class="{{ $module_icon }}"></i> {{ __('messages.new') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div id="branches-app">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="branches-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>{{ __('messages.name') }}</th>
                                        <th>{{ __('messages.type') }}</th>
                                        <th>{{ __('messages.status') }}</th>
                                        <th>{{ __('messages.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="branches-tbody"></tbody>
                            </table>
                        </div>
                        <div id="branches-pagination" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script>
    let currentPage = 1;

    function loadBranches(page = 1) {
        currentPage = page;
        fetch(`{{ route('backend.branches.index_data') }}?page=${page}`)
            .then(r => r.json())
            .then(data => {
                const tbody = document.getElementById('branches-tbody');
                tbody.innerHTML = data.data.map((item, i) => `
                    <tr>
                        <td>${(data.current_page - 1) * data.per_page + i + 1}</td>
                        <td>${item.name_en || item.name || ''} ${item.name_ar ? '<br><small class="text-muted">'+item.name_ar+'</small>' : ''}</td>
                        <td><span class="badge bg-info">${item.type || '-'}</span></td>
                        <td><span class="badge bg-${item.is_active ? 'success' : 'danger'}">${item.is_active ? 'Active' : 'Inactive'}</span></td>
                        <td>
                            <a href="{{ url('app/branches') }}/${item.id}/edit" class="btn btn-warning btn-xs"><i class="ph ph-pencil-simple"></i></a>
                            <button class="btn btn-danger btn-xs" onclick="deleteBranch(${item.id})"><i class="ph ph-trash"></i></button>
                        </td>
                    </tr>
                `).join('') || '<tr><td colspan="5" class="text-center">No branches found</td></tr>';
            });
    }

    function deleteBranch(id) {
        if (!confirm('Are you sure?')) return;
        fetch(`{{ url('app/branches') }}/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        }).then(() => loadBranches(currentPage));
    }

    loadBranches();
</script>
@endpush
