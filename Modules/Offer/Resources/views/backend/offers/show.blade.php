@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title">{{ $page_title }}</h4>
        <div>
            <a href="{{ route('backend.offers.edit', $offer->id) }}" class="btn btn-warning btn-sm"><i class="ph ph-pencil-simple"></i> {{ __('messages.edit') }}</a>
            <a href="{{ route('backend.offers.index') }}" class="btn btn-secondary btn-sm">{{ __('messages.back') }}</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>{{ __('offer.lbl_title_en') }}</th><td>{{ $offer->title_en }}</td></tr>
                    <tr><th>{{ __('offer.lbl_title_ar') }}</th><td>{{ $offer->title_ar }}</td></tr>
                    <tr><th>{{ __('offer.lbl_internal_name') }}</th><td>{{ $offer->internal_name ?? '-' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_slug') }}</th><td>{{ $offer->slug ?? '-' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_discount_type') }}</th><td><span class="badge bg-secondary">{{ $offer->discount_type }}</span></td></tr>
                    <tr><th>{{ __('offer.lbl_discount_value') }}</th><td>{{ $offer->discount_value }}</td></tr>
                    <tr><th>{{ __('offer.lbl_max_discount_amount') }}</th><td>{{ $offer->max_discount_amount ?? '-' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_coupon_code') }}</th><td>{{ $offer->code ? '<code>'.$offer->code.'</code>' : 'N/A' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_auto_apply') }}</th><td>{{ $offer->auto_apply ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_status') }}</th><td><span class="badge bg-{{ $offer->status === 'active' ? 'success' : 'secondary' }}">{{ $offer->status }}</span></td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>{{ __('offer.lbl_target_scope') }}</th><td>{{ $offer->target_scope }}</td></tr>
                    <tr><th>{{ __('offer.lbl_customer_scope') }}</th><td>{{ $offer->customer_scope }}</td></tr>
                    <tr><th>{{ __('offer.lbl_starts_at') }}</th><td>{{ $offer->starts_at ?? 'Unlimited' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_ends_at') }}</th><td>{{ $offer->ends_at ?? 'Unlimited' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_usage_limit') }}</th><td>{{ $offer->usage_limit_total ?? 'Unlimited' }}</td></tr>
                    <tr><th>{{ __('offer.lbl_usage_per_user') }}</th><td>{{ $offer->usage_limit_per_user }}</td></tr>
                    <tr><th>{{ __('offer.lbl_used_count') }}</th><td>{{ $offer->used_count }}</td></tr>
                    <tr><th>{{ __('offer.lbl_priority') }}</th><td>{{ $offer->priority }}</td></tr>
                    <tr><th>{{ __('offer.lbl_stackable') }}</th><td>{{ $offer->stackable ? 'Yes' : 'No' }}</td></tr>
                    <tr><th>{{ __('messages.created_at') }}</th><td>{{ $offer->created_at }}</td></tr>
                </table>
            </div>
        </div>

        @if(!$offer->applies_to_all_services && $offer->services->count())
        <h5 class="mt-4">{{ __('offer.lbl_select_services') }} ({{ $offer->services->count() }})</h5>
        <ul>
            @foreach($offer->services as $s)
            <li>{{ $s->service?->name ?? 'Service #'.$s->service_id }}</li>
            @endforeach
        </ul>
        @endif

        @if(!$offer->applies_to_all_branches && $offer->branches->count())
        <h5 class="mt-4">{{ __('offer.lbl_select_branches') }} ({{ $offer->branches->count() }})</h5>
        <ul>
            @foreach($offer->branches as $b)
            <li>{{ $b->branch?->name ?? 'Branch #'.$b->branch_id }}</li>
            @endforeach
        </ul>
        @endif

        @if(!$offer->applies_to_all_categories && $offer->categories->count())
        <h5 class="mt-4">{{ __('offer.lbl_select_categories') }} ({{ $offer->categories->count() }})</h5>
        <ul>
            @foreach($offer->categories as $c)
            <li>{{ $c->category?->name ?? 'Category #'.$c->category_id }}</li>
            @endforeach
        </ul>
        @endif

        @if($offer->description_en || $offer->description_ar)
        <h5 class="mt-4">{{ __('offer.lbl_description_en') }}</h5>
        <p>{{ $offer->description_en }}</p>
        @if($offer->description_ar)
        <p dir="rtl">{{ $offer->description_ar }}</p>
        @endif
        @endif

        @if($offer->terms_en)
        <h5 class="mt-4">{{ __('offer.lbl_terms_en') }}</h5>
        <p>{{ $offer->terms_en }}</p>
        @endif
    </div>
</div>
@endsection
