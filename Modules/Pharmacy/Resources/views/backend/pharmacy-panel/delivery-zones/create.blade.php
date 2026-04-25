@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <form id="zoneForm">
            @csrf
            @if(isset($zone)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_name_en') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name_en" value="{{ $zone->name_en ?? '' }}" required>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_name_ar') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name_ar" value="{{ $zone->name_ar ?? '' }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_governorate') }}</label>
                    <select class="form-control" name="governorate_id">
                        <option value="">{{ __('messages.select') }}</option>
                        @foreach(\Modules\Location\Models\Governorate::all() as $gov)
                            <option value="{{ $gov->id }}" {{ isset($zone) && $zone->governorate_id == $gov->id ? 'selected' : '' }}>{{ $gov->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_city') }}</label>
                    <select class="form-control" name="city_id">
                        <option value="">{{ __('messages.select') }}</option>
                        @foreach(\Modules\Location\Models\GovernorateCity::all() as $city)
                            <option value="{{ $city->id }}" {{ isset($zone) && $zone->city_id == $city->id ? 'selected' : '' }}>{{ $city->name_en }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_delivery_fee') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="delivery_fee" value="{{ $zone->delivery_fee ?? 0 }}" required>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_min_order_amount') }}</label>
                    <input type="number" step="0.01" class="form-control" name="min_order_amount" value="{{ $zone->min_order_amount ?? 0 }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_estimated_delivery_minutes') }}</label>
                    <input type="number" class="form-control" name="estimated_delivery_minutes" value="{{ $zone->estimated_delivery_minutes ?? 60 }}">
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ isset($zone) ? ($zone->is_active ? 'checked' : '') : 'checked' }}>
                    {{ __('messages.active') }}
                </label>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            <a href="{{ route('backend.pharmacy-panel-delivery-zones.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>

<script>
document.getElementById('zoneForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = @if(isset($zone)) '{{ route('backend.pharmacy-panel-delivery-zones.update', $zone->id) }}' @else '{{ route('backend.pharmacy-panel-delivery-zones.store') }}' @endif;
    fetch(url, {
        method: @if(isset($zone)) 'PUT' @else 'POST' @endif,
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
        body: JSON.stringify(Object.fromEntries(formData))
    }).then(r => r.json()).then(data => {
        if (data.status) window.location.href = '{{ route('backend.pharmacy-panel-delivery-zones.index') }}';
    });
});
</script>
@endsection
