@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4 class="card-title">{{ __('pharmacy.lbl_batches') }}</h4></div>
            <div class="col-md-6 text-end">
                <a href="{{ route('backend.pharmacy-panel-batches.create') }}" class="btn btn-primary btn-sm">
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
                        <th>{{ __('pharmacy.lbl_product') }}</th>
                        <th>{{ __('pharmacy.lbl_batch_number') }}</th>
                        <th>{{ __('pharmacy.lbl_quantity') }}</th>
                        <th>{{ __('pharmacy.lbl_expiry_date') }}</th>
                        <th>{{ __('pharmacy.lbl_days_until_expiry') }}</th>
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
        fetch(`{{ route('backend.pharmacy-panel-batches.index_data') }}?page=${page}&search=${search}`)
            .then(r => r.json()).then(data => {
                document.getElementById('tableBody').innerHTML = data.data.map(item => {
                    const daysLeft = item.days_until_expiry;
                    const expiryClass = daysLeft <= 0 ? 'text-danger' : (daysLeft <= 30 ? 'text-warning' : 'text-success');
                    return `
                    <tr>
                        <td>${item.inventory && item.inventory.product ? item.inventory.product.name_en : '-'}</td>
                        <td>${item.batch_number}</td>
                        <td>${item.quantity}</td>
                        <td>${item.expiry_date}</td>
                        <td class="${expiryClass}">${daysLeft <= 0 ? 'Expired' : daysLeft + ' days'}</td>
                        <td><span class="badge bg-${item.is_active ? 'success' : 'secondary'}">${item.is_active ? '{{ __('messages.active') }}' : '{{ __('messages.inactive') }}'}</span></td>
                        <td>
                            <a href="{{ route('backend.pharmacy-panel-batches.edit', '') }}/${item.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>
                            <button onclick="deleteItem(${item.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>`;
                }).join('');
            });
    }

    window.deleteItem = function(id) {
        if (confirm('{{ __('messages.are_you_sure') }}')) {
            fetch(`{{ route('backend.pharmacy-panel-batches.destroy', '') }}/${id}`, {
                method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(() => loadData(1));
        }
    };
});
</script>
@endsection
