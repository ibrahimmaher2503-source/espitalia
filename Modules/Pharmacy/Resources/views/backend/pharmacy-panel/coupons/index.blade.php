@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4 class="card-title">{{ __('pharmacy.lbl_coupons') }}</h4></div>
            <div class="col-md-6 text-end">
                <a href="{{ route('backend.pharmacy-panel-coupons.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> {{ __('messages.add_new') }}
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="search" class="form-control" placeholder="{{ __('messages.search') }}...">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>{{ __('pharmacy.lbl_code') }}</th>
                        <th>{{ __('pharmacy.lbl_title_en') }}</th>
                        <th>{{ __('pharmacy.lbl_discount_type') }}</th>
                        <th>{{ __('pharmacy.lbl_discount_value') }}</th>
                        <th>{{ __('pharmacy.lbl_used_count') }}</th>
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
    document.getElementById('search').addEventListener('keyup', () => loadData(1));

    function loadData(page) {
        const search = document.getElementById('search').value;
        fetch(`{{ route('backend.pharmacy-panel-coupons.index_data') }}?page=${page}&search=${search}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => `
                    <tr>
                        <td><code>${item.code}</code></td>
                        <td>${item.title_en}</td>
                        <td>${item.discount_type}</td>
                        <td>${item.discount_value}${item.discount_type === 'percentage' ? '%' : ''}</td>
                        <td>${item.used_count}${item.usage_limit ? '/' + item.usage_limit : ''}</td>
                        <td>
                            <button class="btn btn-xs ${item.is_active ? 'btn-success' : 'btn-secondary'}" onclick="toggleStatus(${item.id})">
                                ${item.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'}
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('backend.pharmacy-panel-coupons.edit', '') }}/${item.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>
                            <button onclick="deleteItem(${item.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    window.toggleStatus = function(id) {
        fetch(`{{ route('backend.pharmacy-panel-coupons.update_status', '') }}/${id}`, {
            method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(() => loadData(1));
    };

    window.deleteItem = function(id) {
        if (confirm('{{ __('messages.are_you_sure') }}')) {
            fetch(`{{ route('backend.pharmacy-panel-coupons.destroy', '') }}/${id}`, {
                method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(() => loadData(1));
        }
    };
});
</script>
@endsection
