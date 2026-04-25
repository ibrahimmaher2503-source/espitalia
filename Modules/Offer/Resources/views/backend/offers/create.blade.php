@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <form id="offerForm">
            @csrf
            @if(isset($offer)) @method('PUT') @endif

            {{-- Section 1: Basic Info --}}
            <h5 class="mb-3">{{ __('offer.lbl_internal_name') }}</h5>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_internal_name') }}</label>
                    <input type="text" class="form-control" name="internal_name" value="{{ $offer->internal_name ?? '' }}">
                </div>
            </div>

            {{-- Section 2: Bilingual Content --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_title_en') }} / {{ __('offer.lbl_title_ar') }}</h5>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_title_en') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title_en" value="{{ $offer->title_en ?? '' }}" required>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_title_ar') }} <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title_ar" value="{{ $offer->title_ar ?? '' }}" required dir="rtl">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_description_en') }}</label>
                    <textarea class="form-control" name="description_en" rows="3">{{ $offer->description_en ?? '' }}</textarea>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_description_ar') }}</label>
                    <textarea class="form-control" name="description_ar" rows="3" dir="rtl">{{ $offer->description_ar ?? '' }}</textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_short_label_en') }}</label>
                    <input type="text" class="form-control" name="short_label_en" value="{{ $offer->short_label_en ?? '' }}" placeholder="e.g. 20% OFF">
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_short_label_ar') }}</label>
                    <input type="text" class="form-control" name="short_label_ar" value="{{ $offer->short_label_ar ?? '' }}" dir="rtl">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_terms_en') }}</label>
                    <textarea class="form-control" name="terms_en" rows="2">{{ $offer->terms_en ?? '' }}</textarea>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_terms_ar') }}</label>
                    <textarea class="form-control" name="terms_ar" rows="2" dir="rtl">{{ $offer->terms_ar ?? '' }}</textarea>
                </div>
            </div>

            {{-- Section 3: Discount Settings --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_discount_type') }}</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_discount_type') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="discount_type" id="discountType" required>
                        <option value="percentage" {{ isset($offer) && $offer->discount_type == 'percentage' ? 'selected' : '' }}>{{ __('offer.type_percentage') }}</option>
                        <option value="fixed_amount" {{ isset($offer) && $offer->discount_type == 'fixed_amount' ? 'selected' : '' }}>{{ __('offer.type_fixed_amount') }}</option>
                        <option value="override_price" {{ isset($offer) && $offer->discount_type == 'override_price' ? 'selected' : '' }}>{{ __('offer.type_override_price') }}</option>
                        <option value="bundle_price" {{ isset($offer) && $offer->discount_type == 'bundle_price' ? 'selected' : '' }}>{{ __('offer.type_bundle_price') }}</option>
                        <option value="buy_x_get_y" {{ isset($offer) && $offer->discount_type == 'buy_x_get_y' ? 'selected' : '' }}>{{ __('offer.type_buy_x_get_y') }}</option>
                        <option value="free_addon" {{ isset($offer) && $offer->discount_type == 'free_addon' ? 'selected' : '' }}>{{ __('offer.type_free_addon') }}</option>
                    </select>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_discount_value') }} <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" name="discount_value" value="{{ $offer->discount_value ?? '' }}" required>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_max_discount_amount') }}</label>
                    <input type="number" step="0.01" class="form-control" name="max_discount_amount" value="{{ $offer->max_discount_amount ?? '' }}">
                </div>
            </div>
            <div class="row" id="overrideRow" style="display:none">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_override_price') }}</label>
                    <input type="number" step="0.01" class="form-control" name="override_price" value="{{ $offer->override_price ?? '' }}">
                </div>
            </div>
            <div class="row" id="buyGetRow" style="display:none">
                <div class="col-md-3 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_buy_quantity') }}</label>
                    <input type="number" class="form-control" name="buy_quantity" value="{{ $offer->buy_quantity ?? '' }}">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_get_quantity') }}</label>
                    <input type="number" class="form-control" name="get_quantity" value="{{ $offer->get_quantity ?? '' }}">
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_get_discount_percentage') }}</label>
                    <input type="number" step="0.01" class="form-control" name="get_discount_percentage" value="{{ $offer->get_discount_percentage ?? 100 }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_minimum_order_amount') }}</label>
                    <input type="number" step="0.01" class="form-control" name="minimum_order_amount" value="{{ $offer->minimum_order_amount ?? 0 }}">
                </div>
            </div>

            {{-- Section 4: Targeting --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_target_scope') }}</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_target_scope') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="target_scope" required>
                        @foreach(['global','service','category','branch','package','vendor'] as $scope)
                        <option value="{{ $scope }}" {{ isset($offer) && $offer->target_scope == $scope ? 'selected' : '' }}>{{ __('offer.scope_'.$scope) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label><input type="checkbox" name="applies_to_all_services" value="1" {{ isset($offer) ? ($offer->applies_to_all_services ? 'checked' : '') : 'checked' }}> {{ __('offer.lbl_applies_to_all_services') }}</label>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label><input type="checkbox" name="applies_to_all_branches" value="1" {{ isset($offer) ? ($offer->applies_to_all_branches ? 'checked' : '') : 'checked' }}> {{ __('offer.lbl_applies_to_all_branches') }}</label>
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label><input type="checkbox" name="applies_to_all_categories" value="1" {{ isset($offer) ? ($offer->applies_to_all_categories ? 'checked' : '') : 'checked' }}> {{ __('offer.lbl_applies_to_all_categories') }}</label>
                </div>
            </div>

            {{-- Section 5: Coupon --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_coupon_code') }}</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_coupon_code') }}</label>
                    <input type="text" class="form-control" name="code" value="{{ $offer->code ?? '' }}">
                </div>
                <div class="col-md-4 form-group mb-3 pt-4">
                    <label><input type="checkbox" name="requires_coupon" value="1" {{ isset($offer) && $offer->requires_coupon ? 'checked' : '' }}> {{ __('offer.lbl_requires_coupon') }}</label>
                </div>
                <div class="col-md-4 form-group mb-3 pt-4">
                    <label><input type="checkbox" name="auto_apply" value="1" {{ isset($offer) && $offer->auto_apply ? 'checked' : '' }}> {{ __('offer.lbl_auto_apply') }}</label>
                </div>
            </div>

            {{-- Section 6: Schedule --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_schedule') }}</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_starts_at') }}</label>
                    <input type="datetime-local" class="form-control" name="starts_at" value="{{ isset($offer) && $offer->starts_at ? $offer->starts_at->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_ends_at') }}</label>
                    <input type="datetime-local" class="form-control" name="ends_at" value="{{ isset($offer) && $offer->ends_at ? $offer->ends_at->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="col-md-4 form-group mb-3 pt-4">
                    <label><input type="checkbox" name="is_flash" value="1" {{ isset($offer) && $offer->is_flash ? 'checked' : '' }}> {{ __('offer.lbl_flash_sale') }}</label>
                </div>
            </div>

            {{-- Section 7: Customer Eligibility --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_customer_scope') }}</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_customer_scope') }}</label>
                    <select class="form-control" name="customer_scope">
                        <option value="all" {{ isset($offer) && $offer->customer_scope == 'all' ? 'selected' : '' }}>{{ __('offer.customer_all') }}</option>
                        <option value="first_time" {{ isset($offer) && $offer->customer_scope == 'first_time' ? 'selected' : '' }}>{{ __('offer.customer_first_time') }}</option>
                        <option value="returning" {{ isset($offer) && $offer->customer_scope == 'returning' ? 'selected' : '' }}>{{ __('offer.customer_returning') }}</option>
                        <option value="segment" {{ isset($offer) && $offer->customer_scope == 'segment' ? 'selected' : '' }}>{{ __('offer.customer_segment') }}</option>
                    </select>
                </div>
                <div class="col-md-4 form-group mb-3 pt-4">
                    <label><input type="checkbox" name="first_booking_only" value="1" {{ isset($offer) && $offer->first_booking_only ? 'checked' : '' }}> {{ __('offer.lbl_first_booking_only') }}</label>
                </div>
            </div>

            {{-- Section 8: Usage Limits --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_usage_limit') }}</h5>
            <div class="row">
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_usage_limit') }}</label>
                    <input type="number" class="form-control" name="usage_limit_total" value="{{ $offer->usage_limit_total ?? '' }}">
                </div>
                <div class="col-md-4 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_usage_per_user') }}</label>
                    <input type="number" class="form-control" name="usage_limit_per_user" value="{{ $offer->usage_limit_per_user ?? 1 }}">
                </div>
            </div>

            {{-- Section 9: Priority & Stacking --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_priority') }}</h5>
            <div class="row">
                <div class="col-md-3 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_priority') }} (0-100)</label>
                    <input type="number" class="form-control" name="priority" value="{{ $offer->priority ?? 0 }}" min="0" max="100">
                </div>
                <div class="col-md-3 form-group mb-3 pt-4">
                    <label><input type="checkbox" name="stackable" value="1" {{ isset($offer) && $offer->stackable ? 'checked' : '' }}> {{ __('offer.lbl_stackable') }}</label>
                </div>
                <div class="col-md-3 form-group mb-3">
                    <label class="form-label">{{ __('offer.lbl_exclusive_group') }}</label>
                    <input type="text" class="form-control" name="exclusive_group" value="{{ $offer->exclusive_group ?? '' }}">
                </div>
            </div>

            {{-- Section 10: Display --}}
            <h5 class="mb-3 mt-4">{{ __('offer.lbl_status') }}</h5>
            <div class="row">
                <div class="col-md-3 form-group mb-3 pt-2">
                    <label><input type="checkbox" name="is_active" value="1" {{ isset($offer) ? ($offer->is_active ? 'checked' : '') : 'checked' }}> {{ __('messages.active') }}</label>
                </div>
                <div class="col-md-3 form-group mb-3 pt-2">
                    <label><input type="checkbox" name="is_featured" value="1" {{ isset($offer) && $offer->is_featured ? 'checked' : '' }}> {{ __('offer.lbl_featured') }}</label>
                </div>
                <div class="col-md-3 form-group mb-3 pt-2">
                    <label><input type="checkbox" name="commission_on_original" value="1" {{ isset($offer) ? ($offer->commission_on_original ? 'checked' : '') : 'checked' }}> {{ __('offer.lbl_commission_on_original') }}</label>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">{{ __('messages.save') }}</button>
                <a href="{{ route('backend.offers.index') }}" class="btn btn-secondary">{{ __('messages.cancel') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('discountType');
    function toggleFields() {
        const type = typeSelect.value;
        document.getElementById('overrideRow').style.display = type === 'override_price' ? '' : 'none';
        document.getElementById('buyGetRow').style.display = type === 'buy_x_get_y' ? '' : 'none';
    }
    typeSelect.addEventListener('change', toggleFields);
    toggleFields();

    document.getElementById('offerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        // Handle checkboxes
        ['applies_to_all_services','applies_to_all_branches','applies_to_all_categories','requires_coupon','auto_apply','is_flash','first_booking_only','stackable','is_active','is_featured','commission_on_original'].forEach(field => {
            data[field] = formData.has(field) ? 1 : 0;
        });

        const url = @if(isset($offer)) '{{ route('backend.offers.update', $offer->id) }}' @else '{{ route('backend.offers.store') }}' @endif;
        const method = @if(isset($offer)) 'PUT' @else 'POST' @endif;

        fetch(url, {
            method: method,
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify(data)
        }).then(r => r.json()).then(res => {
            if (res.status) window.location.href = '{{ route('backend.offers.index') }}';
        });
    });
});
</script>
@endsection
