@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-6"><h4>{{ __('pharmacy.lbl_pharmacy') }}</h4></div>
            <div class="col-md-6 text-end"><a href="{{ route('backend.pharmacies.create') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i></a></div>
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3"><div class="col-md-6"><input type="text" id="search" class="form-control" placeholder="Search..."></div></div>
        <table class="table table-striped"><thead><tr><th><input type="checkbox" id="selectAll"></th><th>Name</th><th>Phone</th><th>Delivery Fee</th><th>Status</th><th>Actions</th></tr></thead><tbody id="tableBody"></tbody></table>
    </div>
</div>
<script>
    function load() {
        fetch(`{{ route('backend.pharmacies.index_data') }}?search=${document.getElementById('search').value}`).then(r => r.json()).then(d => {
            document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td><input type="checkbox" name="ids" value="${i.id}"></td><td>${i.name}</td><td>${i.phone}</td><td>${i.delivery_fee}</td><td><button class="btn btn-xs ${i.is_active ? 'btn-success' : 'btn-secondary'}" onclick="status(${i.id})">${i.is_active ? 'Active' : 'Inactive'}</button></td><td><a href="{{ route('backend.pharmacies.edit', '') }}/${i.id}" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a><button onclick="del(${i.id})" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button></td></tr>`).join('');
        });
    }
    load();
    document.getElementById('search').addEventListener('keyup', load);
    window.status = function(id) { fetch(`{{ route('backend.pharmacies.update_status', '') }}/${id}`, {method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load()); };
    window.del = function(id) { if (confirm('Sure?')) fetch(`{{ route('backend.pharmacies.destroy', '') }}/${id}`, {method: 'DELETE', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}}).then(() => load()); };
</script>
@endsection
