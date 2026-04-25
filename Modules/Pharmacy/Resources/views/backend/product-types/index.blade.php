@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4>{{ __('pharmacy.lbl_product_type') }}</h4></div>
            <div class="col-md-6 text-end"><a href="{{ route('backend.pharmacy-product-types.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i></a></div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3"><div class="col-md-6"><input type="text" id="search" class="form-control" placeholder="Search..."></div></div>
        <table class="table table-striped"><thead><tr><th><input type="checkbox" id="selectAll"></th><th>EN</th><th>AR</th><th>Actions</th></tr></thead><tbody id="tableBody"></tbody></table>
    </div>
</div>
<script>
    function load() {
        fetch(`{{ route('backend.pharmacy-product-types.index_data') }}?search=${document.getElementById('search').value}`).then(r => r.json()).then(d => {
            document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td><input type="checkbox" name="ids" value="${i.id}"></td><td>${i.name_en}</td><td>${i.name_ar}</td><td><a href="{{ route('backend.pharmacy-product-types.edit', '') }}/${i.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a><button onclick="del(${i.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button></td></tr>`).join('');
        });
    }
    load();
    document.getElementById('search').addEventListener('keyup', load);
    window.del = function(id) {
        if (confirm('Sure?')) fetch(`{{ route('backend.pharmacy-product-types.destroy', '') }}/${id}`, {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load());
    };
</script>
@endsection
