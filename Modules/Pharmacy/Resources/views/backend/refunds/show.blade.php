@extends('backend.layouts.app')
@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $page_title }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
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
                    @if($refund->processed_by)
                    <tr><th>{{ __('pharmacy.lbl_processed_by') }}</th><td>{{ $refund->processor->first_name ?? '' }} {{ $refund->processor->last_name ?? '' }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_processed_at') }}</th><td>{{ $refund->processed_at }}</td></tr>
                    @endif
                    <tr><th>{{ __('messages.created_at') }}</th><td>{{ $refund->created_at }}</td></tr>
                </table>
            </div>
        </div>

        @if($refund->status === 'pending')
        <div class="mt-3">
            <h5>{{ __('messages.actions') }}</h5>
            <form id="processForm">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_admin_notes') }}</label>
                    <textarea class="form-control" name="admin_notes" rows="3"></textarea>
                </div>
                <button type="button" class="btn btn-success" onclick="processRefund('approved')">{{ __('pharmacy.refund_status_approved') }}</button>
                <button type="button" class="btn btn-danger" onclick="processRefund('declined')">{{ __('pharmacy.refund_status_declined') }}</button>
            </form>
        </div>

        <script>
        function processRefund(status) {
            const notes = document.querySelector('textarea[name="admin_notes"]').value;
            fetch('{{ route('backend.pharmacy-refunds.process', $refund->id) }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({status, admin_notes: notes})
            }).then(r => r.json()).then(data => {
                if (data.status) location.reload();
            });
        }
        </script>
        @endif

        <a href="{{ route('backend.pharmacy-refunds.index') }}" class="btn btn-secondary mt-3">{{ __('messages.back') }}</a>
    </div>
</div>
@endsection
