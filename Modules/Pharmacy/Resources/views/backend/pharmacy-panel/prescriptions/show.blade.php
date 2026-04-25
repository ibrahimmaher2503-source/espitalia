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
                    <tr><th>{{ __('pharmacy.lbl_prescription_number') }}</th><td>{{ $prescription->prescription_number }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_patient_name') }}</th><td>{{ $prescription->user->first_name ?? '' }} {{ $prescription->user->last_name ?? '' }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_doctor_name') }}</th><td>{{ $prescription->doctor_name ?? '-' }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_status') }}</th><td><span class="badge bg-info">{{ $prescription->status }}</span></td></tr>
                    <tr><th>{{ __('pharmacy.lbl_notes') }}</th><td>{{ $prescription->notes ?? '-' }}</td></tr>
                    <tr><th>{{ __('messages.created_at') }}</th><td>{{ $prescription->created_at }}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5>{{ __('pharmacy.lbl_prescription_images') }}</h5>
                @foreach($prescription->getMedia('prescription_images') as $media)
                    <img src="{{ $media->getUrl() }}" alt="Prescription" class="img-fluid mb-2" style="max-width: 400px;">
                @endforeach
            </div>
        </div>

        @if(in_array($prescription->status, ['pending', 'under_review']))
        <div class="mt-3">
            <h5>Review Prescription</h5>
            <form id="reviewForm">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">{{ __('pharmacy.lbl_rejection_reason') }}</label>
                    <textarea class="form-control" name="rejection_reason" rows="3" id="rejectionReason"></textarea>
                </div>
                <button type="button" class="btn btn-success" onclick="reviewPrescription('approved')">
                    <i class="fa fa-check"></i> {{ __('pharmacy.status_approved') }}
                </button>
                <button type="button" class="btn btn-danger" onclick="reviewPrescription('rejected')">
                    <i class="fa fa-times"></i> {{ __('pharmacy.status_rejected') }}
                </button>
            </form>
        </div>

        <script>
        function reviewPrescription(status) {
            const reason = document.getElementById('rejectionReason').value;
            if (status === 'rejected' && !reason) { alert('Please provide a rejection reason'); return; }
            fetch('{{ route('backend.pharmacy-panel-prescriptions.review', $prescription->id) }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({status, rejection_reason: reason})
            }).then(r => r.json()).then(data => {
                if (data.status) location.reload();
            });
        }
        </script>
        @endif

        <a href="{{ route('backend.pharmacy-panel-prescriptions.index') }}" class="btn btn-secondary mt-3">{{ __('messages.back') }}</a>
    </div>
</div>
@endsection
