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
                    <tr><th>{{ __('pharmacy.lbl_pharmacy') }}</th><td>{{ $prescription->pharmacy->name_en ?? '-' }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_doctor_name') }}</th><td>{{ $prescription->doctor_name ?? '-' }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_status') }}</th><td><span class="badge bg-info">{{ $prescription->status }}</span></td></tr>
                    <tr><th>{{ __('pharmacy.lbl_notes') }}</th><td>{{ $prescription->notes ?? '-' }}</td></tr>
                    @if($prescription->reviewed_by)
                    <tr><th>{{ __('pharmacy.lbl_reviewed_by') }}</th><td>{{ $prescription->reviewer->first_name ?? '' }} {{ $prescription->reviewer->last_name ?? '' }}</td></tr>
                    <tr><th>{{ __('pharmacy.lbl_reviewed_at') }}</th><td>{{ $prescription->reviewed_at }}</td></tr>
                    @endif
                    @if($prescription->rejection_reason)
                    <tr><th>{{ __('pharmacy.lbl_rejection_reason') }}</th><td>{{ $prescription->rejection_reason }}</td></tr>
                    @endif
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

        <a href="{{ route('backend.pharmacy-prescriptions.index') }}" class="btn btn-secondary mt-3">{{ __('messages.back') }}</a>
    </div>
</div>
@endsection
