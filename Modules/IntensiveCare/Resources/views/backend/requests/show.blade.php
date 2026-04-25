@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    {{-- Header --}}
    <div class="card icu-detail-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h5 class="mb-1">{{ __('intensive_care.request_detail') }} <span class="text-muted">{{ $admissionRequest->request_number }}</span></h5>
                    <p class="text-muted mb-0">
                        {{ __('intensive_care.lbl_created_at') }}: {{ $admissionRequest->created_at ? $admissionRequest->created_at->format('d M Y, H:i') : '-' }}
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    {!! $admissionRequest->status_badge !!}
                    {!! $admissionRequest->urgency_badge !!}
                    <a href="{{ route('backend.icu-admission-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-4">
            {{-- Patient Info Card --}}
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-user me-1"></i> {{ __('intensive_care.lbl_patient_info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_patient_name') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->patient_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_patient_age') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->patient_age }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_patient_gender') }}</span>
                        <span class="fw-medium">{{ __('intensive_care.gender_' . $admissionRequest->patient_gender) }}</span>
                    </div>
                    @if($admissionRequest->national_id)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_national_id') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->national_id }}</span>
                    </div>
                    @endif
                    @if($admissionRequest->insurance_number)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_insurance_number') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->insurance_number }}</span>
                    </div>
                    @endif
                    @if($admissionRequest->patient)
                        <hr class="my-2">
                        <small class="text-muted">{{ __('intensive_care.lbl_registered_user') }}</small>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <div class="icu-avatar icu-avatar-sm">
                                <i class="ph ph-user"></i>
                            </div>
                            <div>
                                <span class="fw-medium small">{{ $admissionRequest->patient->first_name }} {{ $admissionRequest->patient->last_name }}</span>
                                <br>
                                <span class="text-muted small">{{ $admissionRequest->patient->email }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Contact Info Card --}}
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-phone me-1"></i> {{ __('intensive_care.lbl_contact_info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_contact_name') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->contact_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_contact_phone') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->contact_phone }}</span>
                    </div>
                    @if($admissionRequest->relationship_to_patient)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_relationship') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->relationship_to_patient }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Payment Info Card --}}
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-wallet me-1"></i> {{ __('intensive_care.lbl_payment_method') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_payment_method') }}</span>
                        <span>{!! $admissionRequest->payment_badge !!}</span>
                    </div>
                    @if($admissionRequest->insurance_provider)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_insurance_provider') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->insurance_provider }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-8">
            {{-- Case Details Card --}}
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-clipboard-text me-1"></i> {{ __('intensive_care.lbl_clinical_info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <span class="text-muted small d-block">{{ __('intensive_care.lbl_hospital_name') }}</span>
                            <span class="fw-medium">{{ $admissionRequest->hospital->name ?? '-' }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">{{ __('intensive_care.lbl_department_name') }}</span>
                            <span class="fw-medium">{{ $admissionRequest->icuDepartment->name ?? __('intensive_care.lbl_any_available') }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">{{ __('intensive_care.lbl_case_type') }}</span>
                            <span class="fw-medium">{{ $admissionRequest->case_type_label }}</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <span class="text-muted small d-block">{{ __('intensive_care.lbl_urgency') }}</span>
                            <span>{!! $admissionRequest->urgency_badge !!}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">{{ __('intensive_care.lbl_needs_ventilator') }}</span>
                            <span>{!! $admissionRequest->needs_ventilator ? '<i class="ph ph-check-circle text-success fs-5"></i> ' . __('messages.yes') : '<i class="ph ph-x-circle text-danger fs-5"></i> ' . __('messages.no') !!}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted small d-block">{{ __('intensive_care.lbl_needs_oxygen') }}</span>
                            <span>{!! $admissionRequest->needs_oxygen ? '<i class="ph ph-check-circle text-success fs-5"></i> ' . __('messages.yes') : '<i class="ph ph-x-circle text-danger fs-5"></i> ' . __('messages.no') !!}</span>
                        </div>
                    </div>
                    @if($admissionRequest->medical_condition_ar || $admissionRequest->medical_condition_en)
                    <div class="mb-3">
                        <span class="text-muted small d-block">{{ __('intensive_care.lbl_medical_condition') }}</span>
                        <div class="row mt-1">
                            @if($admissionRequest->medical_condition_ar)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_medical_condition_ar') }}</span>
                                <p class="mb-0" dir="rtl">{{ $admissionRequest->medical_condition_ar }}</p>
                            </div>
                            @endif
                            @if($admissionRequest->medical_condition_en)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_medical_condition_en') }}</span>
                                <p class="mb-0" dir="ltr">{{ $admissionRequest->medical_condition_en }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($admissionRequest->diagnosis_ar || $admissionRequest->diagnosis_en)
                    <div class="mb-3">
                        <span class="text-muted small d-block">{{ __('intensive_care.lbl_diagnosis') }}</span>
                        <div class="row mt-1">
                            @if($admissionRequest->diagnosis_ar)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_diagnosis_ar') }}</span>
                                <p class="mb-0" dir="rtl">{{ $admissionRequest->diagnosis_ar }}</p>
                            </div>
                            @endif
                            @if($admissionRequest->diagnosis_en)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_diagnosis_en') }}</span>
                                <p class="mb-0" dir="ltr">{{ $admissionRequest->diagnosis_en }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($admissionRequest->current_location)
                    <div class="mb-3">
                        <span class="text-muted small d-block">{{ __('intensive_care.lbl_current_location') }}</span>
                        <p class="mb-0">{{ $admissionRequest->current_location }}</p>
                    </div>
                    @endif
                    @if($admissionRequest->needs_ambulance)
                    <div class="mb-0">
                        <span class="badge bg-warning text-dark"><i class="ph ph-ambulance me-1"></i> {{ __('intensive_care.lbl_needs_ambulance') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Medical Reports --}}
            @php
                $reports = $admissionRequest->getMedia('medical_reports');
            @endphp
            @if($reports->count() > 0)
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-files me-1"></i> {{ __('intensive_care.lbl_medical_reports') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($reports as $report)
                        <div class="col-md-4 mb-2">
                            <a href="{{ $report->getUrl() }}" target="_blank" class="d-flex align-items-center gap-2 p-2 rounded" style="background:rgba(0,0,0,0.03); text-decoration:none;">
                                <i class="ph ph-file-text fs-4 text-primary"></i>
                                <div>
                                    <span class="small fw-medium text-dark d-block">{{ $report->file_name }}</span>
                                    <span class="small text-muted">{{ number_format($report->size / 1024, 1) }} KB</span>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Response Section --}}
            @if($admissionRequest->respondedBy)
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-chat-dots me-1"></i> {{ __('intensive_care.lbl_response_info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_responded_by') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->respondedBy->first_name }} {{ $admissionRequest->respondedBy->last_name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_response_date') }}</span>
                        <span class="fw-medium">{{ $admissionRequest->response_date ? $admissionRequest->response_date->format('d M Y, H:i') : '-' }}</span>
                    </div>
                    @if($admissionRequest->admin_notes_ar || $admissionRequest->admin_notes_en)
                    <div class="mt-2">
                        <span class="text-muted small d-block">{{ __('intensive_care.lbl_admin_notes') }}</span>
                        <div class="row mt-1">
                            @if($admissionRequest->admin_notes_ar)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_admin_notes_ar') }}</span>
                                <p class="mb-0" dir="rtl">{{ $admissionRequest->admin_notes_ar }}</p>
                            </div>
                            @endif
                            @if($admissionRequest->admin_notes_en)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_admin_notes_en') }}</span>
                                <p class="mb-0" dir="ltr">{{ $admissionRequest->admin_notes_en }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($admissionRequest->rejection_reason_ar || $admissionRequest->rejection_reason_en)
                    <div class="mt-2">
                        <span class="text-muted small d-block">{{ __('intensive_care.lbl_rejection_reason') }}</span>
                        <div class="row mt-1">
                            @if($admissionRequest->rejection_reason_ar)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_rejection_reason_ar') }}</span>
                                <p class="mb-0 text-danger" dir="rtl">{{ $admissionRequest->rejection_reason_ar }}</p>
                            </div>
                            @endif
                            @if($admissionRequest->rejection_reason_en)
                            <div class="col-md-6">
                                <span class="text-muted small">{{ __('intensive_care.lbl_rejection_reason_en') }}</span>
                                <p class="mb-0 text-danger" dir="ltr">{{ $admissionRequest->rejection_reason_en }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Action Form --}}
            @if(count($allowedTransitions) > 0)
            @can('edit_icu_admission_requests')
            <div class="card icu-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-gear me-1"></i> {{ __('intensive_care.lbl_respond_to_request') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('intensive_care.lbl_admin_notes') }}</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="admin_notes_ar">{{ __('intensive_care.lbl_admin_notes_ar') }}</label>
                                <textarea class="form-control" id="admin_notes_ar" rows="3" dir="rtl" placeholder="{{ __('intensive_care.lbl_admin_notes_placeholder') }}">{{ $admissionRequest->admin_notes_ar }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="admin_notes_en">{{ __('intensive_care.lbl_admin_notes_en') }}</label>
                                <textarea class="form-control" id="admin_notes_en" rows="3" dir="ltr" placeholder="{{ __('intensive_care.lbl_admin_notes_placeholder') }}">{{ $admissionRequest->admin_notes_en }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 d-none" id="rejection-reason-group">
                        <label class="form-label">{{ __('intensive_care.lbl_rejection_reason') }} <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="rejection_reason_ar">{{ __('intensive_care.lbl_rejection_reason_ar') }}</label>
                                <textarea class="form-control" id="rejection_reason_ar" rows="2" dir="rtl" placeholder="{{ __('intensive_care.lbl_rejection_reason_placeholder') }}"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted" for="rejection_reason_en">{{ __('intensive_care.lbl_rejection_reason_en') }}</label>
                                <textarea class="form-control" id="rejection_reason_en" rows="2" dir="ltr" placeholder="{{ __('intensive_care.lbl_rejection_reason_placeholder') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        @if(in_array('accepted', $allowedTransitions))
                        <button type="button" class="btn btn-success respond-btn" data-action="accept">
                            <i class="ph ph-check-circle me-1"></i> {{ __('intensive_care.lbl_accept') }}
                        </button>
                        @endif
                        @if(in_array('rejected', $allowedTransitions))
                        <button type="button" class="btn btn-danger respond-btn" data-action="reject">
                            <i class="ph ph-x-circle me-1"></i> {{ __('intensive_care.lbl_reject') }}
                        </button>
                        @endif
                        @if(in_array('info_requested', $allowedTransitions))
                        <button type="button" class="btn btn-info respond-btn" data-action="request_info">
                            <i class="ph ph-question me-1"></i> {{ __('intensive_care.lbl_request_info') }}
                        </button>
                        @endif
                        @if(in_array('cancelled', $allowedTransitions))
                        <button type="button" class="btn btn-secondary respond-btn" data-action="cancel">
                            <i class="ph ph-prohibit me-1"></i> {{ __('intensive_care.lbl_cancel_request') }}
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @endcan
            @endif
        </div>
    </div>
@endsection

@push('after-styles')
<style>
    .icu-detail-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .icu-detail-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .icu-detail-card .card-header h6 {
        color: #374151;
        font-weight: 600;
    }
    .icu-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: rgba(99,102,241,0.1);
        color: #6366f1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .icu-avatar-sm {
        width: 32px;
        height: 32px;
        font-size: 0.9rem;
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        // Toggle rejection reason field
        $('.respond-btn[data-action="reject"]').on('click', function() {
            $('#rejection-reason-group').removeClass('d-none');
        });

        // Respond action
        $('.respond-btn').on('click', function() {
            var action = $(this).data('action');
            var adminNotesAr = $('#admin_notes_ar').val();
            var adminNotesEn = $('#admin_notes_en').val();
            var rejectionReasonAr = $('#rejection_reason_ar').val();
            var rejectionReasonEn = $('#rejection_reason_en').val();

            // Validate rejection reason when rejecting (at least one language required)
            if (action === 'reject' && !rejectionReasonAr && !rejectionReasonEn) {
                $('#rejection-reason-group').removeClass('d-none');
                $('#rejection_reason_ar').addClass('is-invalid');
                $('#rejection_reason_en').addClass('is-invalid');
                return;
            }

            var data = {
                _token: '{{ csrf_token() }}',
                action: action,
                admin_notes_ar: adminNotesAr,
                admin_notes_en: adminNotesEn,
                rejection_reason_ar: rejectionReasonAr,
                rejection_reason_en: rejectionReasonEn,
            };

            // For cancel action, use update_status instead
            if (action === 'cancel') {
                $.ajax({
                    url: '{{ route("backend.icu-admission-requests.update_status", $admissionRequest->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        status: 'cancelled',
                        cancellation_reason: adminNotesEn || adminNotesAr,
                    },
                    success: function(response) {
                        if (response.status) {
                            window.location.reload();
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('{{ __("intensive_care.lbl_error_loading") }}');
                    }
                });
                return;
            }

            $.ajax({
                url: '{{ route("backend.icu-admission-requests.respond", $admissionRequest->id) }}',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.status) {
                        window.location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('{{ __("intensive_care.lbl_error_loading") }}');
                }
            });
        });
    });
</script>
@endpush
