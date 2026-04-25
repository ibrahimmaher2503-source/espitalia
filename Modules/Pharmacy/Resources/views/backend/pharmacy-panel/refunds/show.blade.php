@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr><th>{{ __('pharmacy.lbl_refund_number') }}</th><td>{{ $refund->refund_number }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_order') }}</th><td>#{{ $refund->order_id }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_patient_name') }}</th><td>{{ $refund->user->first_name ?? '' }} {{ $refund->user->last_name ?? '' }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_reason') }}</th><td>{{ $refund->reason }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_refund_type') }}</th><td>{{ $refund->refund_type }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_refund_amount') }}</th><td>{{ $refund->refund_amount }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_refund_method') }}</th><td>{{ $refund->refund_method }}</td></tr>
            <tr><th>{{ __('pharmacy.lbl_status') }}</th><td><span class="badge bg-info">{{ $refund->status }}</span></td></tr>
            @if($refund->admin_notes)
            <tr><th>{{ __('pharmacy.lbl_admin_notes') }}</th><td>{{ $refund->admin_notes }}</td></tr>
            @endif
            <tr><th>{{ __('messages.created_at') }}</th><td>{{ $refund->created_at }}</td></tr>
        </table>

        <a href="{{ route('backend.pharmacy-panel-refunds.index') }}" class="btn btn-secondary mt-3">{{ __('messages.back') }}</a>
    </div>
</div>
@endsection
