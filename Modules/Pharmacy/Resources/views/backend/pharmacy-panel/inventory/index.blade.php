@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4>My Inventory</h4></div>
            <div class="col-md-6 text-end"><a href="{{ route('backend.pharmacy-panel-inventory.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add Product</a></div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3"><div class="col-md-6"><input type="text" id="search" class="form-control" placeholder="Search products..."></div></div>
        <table class="table table-striped"><thead><tr><th>Product</th><th>Price</th><th>Stock</th><th>Available</th><th>Actions</th></tr></thead><tbody id="tableBody"></tbody></table>
    </div>
</div>
<script>
    function load() {
        fetch(`{{ route('backend.pharmacy-panel-inventory.index_data') }}?search=${document.getElementById('search').value}`).then(r => r.json()).then(d => {
            document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td>${i.product?.name_en || ''}</td><td>${i.price}</td><td>${i.stock_qty}</td><td><button class="btn btn-xs ${i.is_available ? 'btn-success' : 'btn-secondary'}" onclick="status(${i.id})">${i.is_available ? 'Yes' : 'No'}</button></td><td><a href="{{ route('backend.pharmacy-panel-inventory.edit', '') }}/${i.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a><button onclick="del(${i.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button></td></tr>`).join('');
        });
    }
    load();
    document.getElementById('search').addEventListener('keyup', load);
    window.status = function(id) { fetch(`{{ route('backend.pharmacy-panel-inventory.update_status', '') }}/${id}`, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load()); };
    window.del = function(id) { if (confirm('Delete this inventory item?')) fetch(`{{ route('backend.pharmacy-panel-inventory.destroy', '') }}/${id}`, {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load()); };
</script>
@endsection
