@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4 class="card-title">{{ __('pharmacy.lbl_delivery_zones') }}</h4></div>
            <div class="col-md-6 text-end">
                <a href="{{ route('backend.pharmacy-panel-delivery-zones.create') }}" class="btn btn-primary btn-sm">
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
                        <th>{{ __('pharmacy.lbl_name_en') }}</th>
                        <th>{{ __('pharmacy.lbl_name_ar') }}</th>
                        <th>{{ __('pharmacy.lbl_governorate') }}</th>
                        <th>{{ __('pharmacy.lbl_delivery_fee') }}</th>
                        <th>{{ __('pharmacy.lbl_min_order_amount') }}</th>
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
        fetch(`{{ route('backend.pharmacy-panel-delivery-zones.index_data') }}?page=${page}&search=${search}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => `
                    <tr>
                        <td>${item.name_en}</td>
                        <td>${item.name_ar}</td>
                        <td>${item.governorate ? item.governorate.name_en : '-'}</td>
                        <td>${item.delivery_fee}</td>
                        <td>${item.min_order_amount}</td>
                        <td>
                            <button class="btn btn-xs ${item.is_active ? 'btn-success' : 'btn-secondary'}" onclick="toggleStatus(${item.id})">
                                ${item.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'}
                            </button>
                        </td>
                        <td>
                            <a href="{{ route('backend.pharmacy-panel-delivery-zones.edit', '') }}/${item.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>
                            <button onclick="deleteItem(${item.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    window.toggleStatus = function(id) {
        fetch(`{{ route('backend.pharmacy-panel-delivery-zones.update_status', '') }}/${id}`, {
            method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        }).then(() => loadData(1));
    };

    window.deleteItem = function(id) {
        if (confirm('{{ __('messages.are_you_sure') }}')) {
            fetch(`{{ route('backend.pharmacy-panel-delivery-zones.destroy', '') }}/${id}`, {
                method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(() => loadData(1));
        }
    };
});
</script>
@endsection
