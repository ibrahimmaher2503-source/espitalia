@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <form id="couponForm">
            @csrf
            @if(isset($coupon)) @method('PUT') @endif

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_code') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="code" value="{{ $coupon->code ?? '' }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_title_en') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title_en" value="{{ $coupon->title_en ?? '' }}" required>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_title_ar') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title_ar" value="{{ $coupon->title_ar ?? '' }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_discount_type') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="discount_type" required>
                        <option value="percentage" {{ isset($coupon) && $coupon->discount_type == 'percentage' ? 'selected' : '' }}>{{ __('pharmacy.lbl_percentage') }}</option>
                        <option value="fixed" {{ isset($coupon) && $coupon->discount_type == 'fixed' ? 'selected' : '' }}>{{ __('pharmacy.lbl_fixed') }}</option>
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_discount_value') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="discount_value" value="{{ $coupon->discount_value ?? '' }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_max_discount_amount') }}</label>
                    <input type="number" step="0.01" class="form-control" name="max_discount_amount" value="{{ $coupon->max_discount_amount ?? '' }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_min_order_amount') }}</label>
                    <input type="number" step="0.01" class="form-control" name="min_order_amount" value="{{ $coupon->min_order_amount ?? 0 }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_usage_limit') }}</label>
                    <input type="number" class="form-control" name="usage_limit" value="{{ $coupon->usage_limit ?? '' }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_usage_per_user') }}</label>
                    <input type="number" class="form-control" name="usage_per_user" value="{{ $coupon->usage_per_user ?? 1 }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_starts_at') }}</label>
                    <input type="datetime-local" class="form-control" name="starts_at" value="{{ isset($coupon) && $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_expires_at') }}</label>
                    <input type="datetime-local" class="form-control" name="expires_at" value="{{ isset($coupon) && $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '' }}">
                </div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">
                    <input type="checkbox" name="is_active" value="1" {{ isset($coupon) ? ($coupon->is_active ? 'checked' : '') : 'checked' }}>
                    {{ __('messages.active') }}
                </label>
            </div>

            <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
            <a href="{{ route('backend.pharmacy-panel-coupons.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
        </form>
    </div>
</div>

<script>
document.getElementById('couponForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const url = @if(isset($coupon)) '{{ route('backend.pharmacy-panel-coupons.update', $coupon->id) }}' @else '{{ route('backend.pharmacy-panel-coupons.store') }}' @endif;
    fetch(url, {
        method: @if(isset($coupon)) 'PUT' @else 'POST' @endif,
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
        body: JSON.stringify(Object.fromEntries(formData))
    }).then(r => r.json()).then(data => {
        if (data.status) window.location.href = '{{ route('backend.pharmacy-panel-coupons.index') }}';
    });
});
</script>
@endsection
