@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4>My Orders</h4></div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4"><input type="text" id="search" class="form-control" placeholder="Search orders..."></div>
            <div class="col-md-4">
                <select id="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="preparing">Preparing</option>
                    <option value="on_the_way">On the Way</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>
        <table class="table table-striped"><thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody id="tableBody"></tbody></table>
    </div>
</div>
<script>
    function load() {
        const params = new URLSearchParams();
        params.append('search', document.getElementById('search').value);
        if (document.getElementById('status').value) params.append('status', document.getElementById('status').value);
        fetch(`{{ route('backend.pharmacy-panel-orders.index_data') }}?${params}`).then(r => r.json()).then(d => {
            document.getElementById('tableBody').innerHTML = d.data.map(i => `<tr><td>#${i.id}</td><td>${i.user?.first_name || ''} ${i.user?.last_name || ''}</td><td>${i.total}</td><td><span class="badge bg-${getStatusColor(i.status)}">${i.status}</span></td><td>${new Date(i.created_at).toLocaleDateString()}</td><td><a href="{{ route('backend.pharmacy-panel-orders.show', '') }}/${i.id}" class="btn btn-primary btn-xs"><i class="fa fa-eye"></i></a></td></tr>`).join('');
        });
    }
    function getStatusColor(status) {
        const colors = {pending: 'warning', confirmed: 'info', preparing: 'primary', on_the_way: 'secondary', delivered: 'success', cancelled: 'danger'};
        return colors[status] || 'secondary';
    }
    load();
    document.getElementById('search').addEventListener('keyup', load);
    document.getElementById('status').addEventListener('change', load);
</script>
@endsection
