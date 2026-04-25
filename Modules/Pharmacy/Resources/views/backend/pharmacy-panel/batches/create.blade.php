@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <form id="batchForm">
            @csrf
            @if(isset($batch)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_product') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="inventory_id" required>
                        <option value="">{{ __('messages.select') }}</option>
                        @foreach($inventories as $inv)
                            <option value="{{ $inv->id }}" {{ isset($batch) && $batch->inventory_id == $inv->id ? 'selected' : '' }}>
                                {{ $inv->product->name_en ?? 'Product #'.$inv->product_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_batch_number') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="batch_number" value="{{ $batch->batch_number ?? '' }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_quantity') }} <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" name="quantity" value="{{ $batch->quantity ?? 0 }}" min="0" required>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_manufacturing_date') }}</label>
                    <input type="date" class="form-control" name="manufacturing_date" value="{{ isset($batch) && $batch->manufacturing_date ? $batch->manufacturing_date->format('Y-m-d') : '' }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_expiry_date') }} <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="expiry_date" value="{{ isset($batch) && $batch->expiry_date ? $batch->expiry_date->format('Y-m-d') : '' }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_purchase_price') }}</label>
                    <input type="number" step="0.01" class="form-control" name="purchase_price" value="{{ $batch->purchase_price ?? '' }}">
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">{{ __('pharmacy.lbl_notes') }}</label>
                <textarea class="form-control" name="notes" rows="3">{{ $batch->notes ?? '' }}</textarea>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ isset($batch) ? ($batch->is_active ? 'checked' : '') : 'checked' }}>
                    {{ __('messages.active') }}
                </label>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            <a href="{{ route('backend.pharmacy-panel-batches.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>

<script>
document.getElementById('batchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = @if(isset($batch)) '{{ route('backend.pharmacy-panel-batches.update', $batch->id) }}' @else '{{ route('backend.pharmacy-panel-batches.store') }}' @endif;
    fetch(url, {
        method: @if(isset($batch)) 'PUT' @else 'POST' @endif,
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
        body: JSON.stringify(Object.fromEntries(formData))
    }).then(r => r.json()).then(data => {
        if (data.status) window.location.href = '{{ route('backend.pharmacy-panel-batches.index') }}';
    });
});
</script>
@endsection
