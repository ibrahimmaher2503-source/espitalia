@extends('backend.layouts.app')
@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4>Order #{{ $order->id }} - {{ $order->user->first_name }} {{ $order->user->last_name }}</h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span class="badge bg-{{ getStatusColor($order->status) }}">{{ $order->status }}</span></p>
                        <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                        <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Subtotal:</strong> {{ number_format($order->subtotal, 2) }}</p>
                        <p><strong>Delivery Fee:</strong> {{ number_format($order->delivery_fee, 2) }}</p>
                        <p><strong>Total:</strong> <strong>{{ number_format($order->total, 2) }}</strong></p>
                    </div>
                </div>

                <h5>Items</h5>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr><th>Product</th><th>Qty</th><th>Price</th><th>Line Total</th></tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->price, 2) }}</td>
                                <td>{{ number_format($item->quantity * $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5>Delivery Address</h5>
                <p>
                    {{ $order->address->street ?? '' }}<br>
                    {{ $order->address->city ?? '' }}, {{ $order->address->state ?? '' }} {{ $order->address->postal_code ?? '' }}
                </p>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><h5>Order Actions</h5></div>
            <div class="card-body">
                @if($order->status === 'pending')
                    <button class="btn btn-success w-100 mb-2" onclick="accept()"><i class="fa fa-check"></i> Accept Order</button>
                    <button class="btn btn-danger w-100" onclick="reject()"><i class="fa fa-times"></i> Reject Order</button>
                @elseif(in_array($order->status, ['confirmed', 'preparing', 'on_the_way']))
                    <div class="mb-3">
                        <label class="form-label">Advance Status:</label>
                        <select class="form-control" id="nextStatus">
                            @if($order->status === 'confirmed')
                                <option value="preparing">Move to Preparing</option>
                            @elseif($order->status === 'preparing')
                                <option value="on_the_way">Move to On the Way</option>
                            @elseif($order->status === 'on_the_way')
                                <option value="delivered">Mark as Delivered</option>
                            @endif
                        </select>
                    </div>
                    <button class="btn btn-primary w-100" onclick="advance()"><i class="fa fa-arrow-right"></i> Update Status</button>
                @else
                    <div class="alert alert-info">No actions available for this order status.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@php
function getStatusColor($status) {
    $colors = ['pending' => 'warning', 'confirmed' => 'info', 'preparing' => 'primary', 'on_the_way' => 'secondary', 'delivered' => 'success', 'cancelled' => 'danger'];
    return $colors[$status] ?? 'secondary';
}
@endphp

<script>
    function accept() {
        if (confirm('Accept this order?')) {
            fetch(`{{ route('backend.pharmacy-panel-orders.accept', $order->id) }}`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(r => r.json()).then(d => {
                if (d.status) location.reload();
            });
        }
    }
    function reject() {
        if (confirm('Reject this order and restore stock?')) {
            fetch(`{{ route('backend.pharmacy-panel-orders.reject', $order->id) }}`, {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
            }).then(r => r.json()).then(d => {
                if (d.status) location.href = '{{ route('backend.pharmacy-panel-orders.index') }}';
            });
        }
    }
    function advance() {
        const status = document.getElementById('nextStatus')?.value;
        if (!status) return;
        fetch(`{{ route('backend.pharmacy-panel-orders.advance_status', $order->id) }}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({status: status})
        }).then(r => r.json()).then(d => {
            if (d.status) location.reload();
        });
    }
</script>
@endsection
