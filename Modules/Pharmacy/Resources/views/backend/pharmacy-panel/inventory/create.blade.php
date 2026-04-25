@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header"><h4>{{ $page_title }}</h4></div>
    <div class="card-body">
        <form id="form">
            @csrf
            @if(isset($inventory))
                <input type="hidden" name="inventory_id" value="{{ $inventory->id }}">
            @endif

            <div class="form-group mb-3">
                <label for="product_id">Product <span class="text-danger">*</span></label>
                @if(isset($inventory))
                    <input type="text" class="form-control" value="{{ $inventory->product->name_en }}" disabled>
                    <input type="hidden" name="product_id" value="{{ $inventory->product_id }}">
                @else
                    <select class="form-control" id="product_id" name="product_id" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name_en }}</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="form-group mb-3">
                <label for="price">Price <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="{{ $inventory->price ?? '' }}" required>
            </div>

            <div class="form-group mb-3">
                <label for="stock_qty">Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="stock_qty" name="stock_qty" value="{{ $inventory->stock_qty ?? '' }}" required>
            </div>

            <div class="form-group mb-3">
                <label><input type="checkbox" name="is_available" value="1" {{ isset($inventory) && $inventory->is_available ? 'checked' : 'checked' }}> Available</label>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('backend.pharmacy-panel-inventory.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<script>
    document.getElementById('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const url = @if(isset($inventory)) '{{ route('backend.pharmacy-panel-inventory.update', $inventory->id) }}' @else '{{ route('backend.pharmacy-panel-inventory.store') }}' @endif;
        const method = @if(isset($inventory)) 'PUT' @else 'POST' @endif;
        fetch(url, {
            method: method,
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({
                product_id: document.getElementById('product_id')?.value || fd.get('product_id'),
                price: document.getElementById('price').value,
                stock_qty: document.getElementById('stock_qty').value,
                is_available: document.querySelector('[name=is_available]').checked ? 1 : 0
            })
        }).then(r => r.json()).then(d => { if (d.status) location.href = '{{ route('backend.pharmacy-panel-inventory.index') }}'; });
    });
</script>
@endsection
