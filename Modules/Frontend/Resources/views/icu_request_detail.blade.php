@extends('frontend::layouts.master')
@section('title', $admission->request_number)

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">
            <div class="row g-4">

                {{-- ── Sidebar ── --}}
                <div class="col-lg-4">
                    <div class="pp-detail-section" style="position:sticky;top:5rem">

                        <div class="pp-detail-section-header" style="justify-content:center;padding:1.5rem">
                            <i class="ph ph-bed" style="font-size:3rem;color:var(--pp-teal)" aria-hidden="true"></i>
                        </div>

                        <div class="pp-detail-section-body">
                            {{-- Request number + status --}}
                            <p class="pp-section-title mb-1">{{ $admission->request_number }}</p>
                            <div class="text-center mb-3 d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                {!! $admission->status_badge !!}
                                {!! $admission->urgency_badge !!}
                            </div>

                            {{-- Key detail rows --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-hospital flex-shrink-0" style="color:var(--pp-teal)"></i>
                                    <span>{{ $admission->hospital ? $admission->hospital->name : '-' }}</span>
                                </div>
                                @if($admission->icuDepartment)
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-buildings flex-shrink-0" style="color:var(--pp-teal)"></i>
                                        <span>{{ $admission->icuDepartment->name }}</span>
                                    </div>
                                @endif
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-calendar flex-shrink-0" style="color:var(--pp-teal)"></i>
                                    <span>{{ $admission->created_at->format('d M Y') }}</span>
                                </div>
                                @if($admission->response_date)
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-clock flex-shrink-0" style="color:var(--pp-teal)"></i>
                                        <span>{{ $admission->response_date->format('d M Y') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <a href="{{ route('my-icu-requests') }}" class="pp-btn pp-btn-outline w-100 mb-2">
                                <i class="ph ph-arrow-left"></i>{{ __('intensive_care.lbl_back_to_requests') }}
                            </a>

                            @if($admission->status === \Modules\IntensiveCare\Models\IcuAdmissionRequest::STATUS_INFO_REQUESTED)
                                <button type="button"
                                        class="pp-btn pp-btn-teal w-100 mb-2"
                                        data-bs-toggle="modal"
                                        data-bs-target="#respondModal">
                                    <i class="ph ph-paper-plane-right"></i>{{ __('intensive_care.lbl_respond_to_request') }}
                                </button>
                            @endif

                            @if($admission->canTransitionTo(\Modules\IntensiveCare\Models\IcuAdmissionRequest::STATUS_CANCELLED))
                                <button type="button"
                                        class="pp-btn w-100"
                                        style="background:var(--pp-cancelled);color:#fff"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelModal">
                                    <i class="ph ph-x-circle"></i>{{ __('intensive_care.lbl_cancel_request') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Main content ── --}}
                <div class="col-lg-8">

                    {{-- Section 1: Patient Information --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-user" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('intensive_care.lbl_patient_info') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="pp-detail-grid">
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_patient_name') }}</p>
                                    <p class="pp-detail-value">{{ $admission->patient_name }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_patient_age') }}</p>
                                    <p class="pp-detail-value">{{ $admission->patient_age }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_patient_gender') }}</p>
                                    <p class="pp-detail-value">{{ __('intensive_care.gender_' . $admission->patient_gender) }}</p>
                                </div>
                                @if($admission->national_id)
                                    <div>
                                        <p class="pp-detail-label">{{ __('intensive_care.lbl_national_id') }}</p>
                                        <p class="pp-detail-value">{{ $admission->national_id }}</p>
                                    </div>
                                @endif
                                @if($admission->insurance_number)
                                    <div>
                                        <p class="pp-detail-label">{{ __('intensive_care.lbl_insurance_number') }}</p>
                                        <p class="pp-detail-value">{{ $admission->insurance_number }}</p>
                                    </div>
                                @endif
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_payment_method') }}</p>
                                    <div>{!! $admission->payment_badge !!}</div>
                                </div>
                                @if($admission->insurance_provider)
                                    <div>
                                        <p class="pp-detail-label">{{ __('intensive_care.lbl_insurance_provider') }}</p>
                                        <p class="pp-detail-value">{{ $admission->insurance_provider }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Case Details --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('intensive_care.lbl_clinical_info') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="pp-detail-grid mb-3">
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_case_type') }}</p>
                                    <p class="pp-detail-value">{{ __('intensive_care.case_' . $admission->case_type) }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_urgency') }}</p>
                                    <div>{!! $admission->urgency_badge !!}</div>
                                </div>
                                @if($admission->current_location)
                                    <div>
                                        <p class="pp-detail-label">{{ __('intensive_care.lbl_current_location') }}</p>
                                        <p class="pp-detail-value">{{ $admission->current_location }}</p>
                                    </div>
                                @endif
                            </div>

                            {{-- Equipment needs --}}
                            <div class="d-flex gap-3 flex-wrap mb-3" style="font-size:0.825rem;font-family:var(--pp-font)">
                                <span class="d-flex align-items-center gap-1">
                                    @if($admission->needs_ventilator)
                                        <i class="ph-fill ph-check-circle" style="color:var(--pp-normal)" aria-hidden="true"></i>
                                    @else
                                        <i class="ph ph-x-circle" style="color:var(--pp-xmuted)" aria-hidden="true"></i>
                                    @endif
                                    {{ __('intensive_care.lbl_needs_ventilator') }}
                                </span>
                                <span class="d-flex align-items-center gap-1">
                                    @if($admission->needs_oxygen)
                                        <i class="ph-fill ph-check-circle" style="color:var(--pp-normal)" aria-hidden="true"></i>
                                    @else
                                        <i class="ph ph-x-circle" style="color:var(--pp-xmuted)" aria-hidden="true"></i>
                                    @endif
                                    {{ __('intensive_care.lbl_needs_oxygen') }}
                                </span>
                                <span class="d-flex align-items-center gap-1">
                                    @if($admission->needs_ambulance)
                                        <i class="ph-fill ph-check-circle" style="color:var(--pp-normal)" aria-hidden="true"></i>
                                    @else
                                        <i class="ph ph-x-circle" style="color:var(--pp-xmuted)" aria-hidden="true"></i>
                                    @endif
                                    {{ __('intensive_care.lbl_needs_ambulance') }}
                                </span>
                            </div>

                            <div>
                                <p class="pp-detail-label">{{ __('intensive_care.lbl_medical_condition') }}</p>
                                <p style="font-size:0.875rem;color:var(--pp-text-2);margin:0 0 0.75rem">{{ $admission->medical_condition }}</p>
                            </div>
                            @if($admission->diagnosis)
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_diagnosis') }}</p>
                                    <p style="font-size:0.875rem;color:var(--pp-text-2);margin:0">{{ $admission->diagnosis }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Section 3: Contact Information --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-phone" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('intensive_care.lbl_contact_info') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="pp-detail-grid">
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_contact_name') }}</p>
                                    <p class="pp-detail-value">{{ $admission->contact_name }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('intensive_care.lbl_contact_phone') }}</p>
                                    <p class="pp-detail-value">{{ $admission->contact_phone }}</p>
                                </div>
                                @if($admission->relationship_to_patient)
                                    <div>
                                        <p class="pp-detail-label">{{ __('intensive_care.lbl_relationship') }}</p>
                                        <p class="pp-detail-value">{{ $admission->relationship_to_patient }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: Medical Reports --}}
                    @php $mediaItems = $admission->getMedia('medical_reports'); @endphp
                    @if($mediaItems->count() > 0)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-file-text" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.lbl_medical_reports') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                @foreach($mediaItems as $media)
                                    <div class="pp-report-row">
                                        <div>
                                            <p class="pp-report-title">{{ $media->file_name }}</p>
                                            <p class="pp-report-meta">{{ number_format($media->size / 1024, 1) }} {{ __('intensive_care.lbl_kb') }}</p>
                                        </div>
                                        <a href="{{ $media->getUrl() }}"
                                           class="pp-btn pp-btn-outline flex-shrink-0"
                                           style="color:var(--pp-confirmed);border-color:var(--pp-confirmed);padding:0.35rem 0.75rem;font-size:0.78rem"
                                           target="_blank"
                                           rel="noopener">
                                            <i class="ph ph-download-simple"></i>{{ __('intensive_care.lbl_reports') }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Section 5: Admin Response --}}
                    @if($admission->admin_notes || $admission->rejection_reason || $admission->respondedBy)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-note" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.lbl_response_info') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="pp-detail-grid mb-3">
                                    @if($admission->respondedBy)
                                        <div>
                                            <p class="pp-detail-label">{{ __('intensive_care.lbl_responded_by') }}</p>
                                            <p class="pp-detail-value">{{ $admission->respondedBy->name ?? '-' }}</p>
                                        </div>
                                    @endif
                                    @if($admission->response_date)
                                        <div>
                                            <p class="pp-detail-label">{{ __('intensive_care.lbl_response_date') }}</p>
                                            <p class="pp-detail-value">{{ $admission->response_date->format('d M Y H:i') }}</p>
                                        </div>
                                    @endif
                                </div>
                                @if($admission->admin_notes)
                                    <div>
                                        <p class="pp-detail-label">{{ __('intensive_care.lbl_admin_notes') }}</p>
                                        <p style="font-size:0.875rem;margin:0">{{ $admission->admin_notes }}</p>
                                    </div>
                                @endif
                                @if($admission->rejection_reason)
                                    <div class="pp-cancel-alert mt-3">
                                        <p class="pp-cancel-alert-title">
                                            <i class="ph ph-x-circle" aria-hidden="true"></i>
                                            {{ __('intensive_care.lbl_rejection_reason') }}
                                        </p>
                                        <p style="font-size:0.875rem;color:var(--pp-text-2);margin:0">{{ $admission->rejection_reason }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Section 6: Cancellation reason --}}
                    @if($admission->status === \Modules\IntensiveCare\Models\IcuAdmissionRequest::STATUS_CANCELLED)
                        <div class="pp-cancel-alert mb-3">
                            <p class="pp-cancel-alert-title">
                                <i class="ph ph-x-circle" aria-hidden="true"></i>
                                {{ __('intensive_care.lbl_cancellation_reason') }}
                            </p>
                            <p style="color:var(--pp-text-2);font-size:0.875rem;margin:0">
                                {{ $admission->cancellation_reason ?? '-' }}
                            </p>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- ── Cancel Modal ── --}}
    @if($admission->canTransitionTo(\Modules\IntensiveCare\Models\IcuAdmissionRequest::STATUS_CANCELLED))
        <div class="modal fade pp-modal pp-modal-danger" id="cancelModal" tabindex="-1"
             aria-labelledby="cancelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cancel-icu-request', $admission->id) }}">
                        @csrf
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ph ph-x-circle" style="color:var(--pp-cancelled);font-size:1.25rem" aria-hidden="true"></i>
                                <h5 class="modal-title m-0" id="cancelModalLabel">{{ __('intensive_care.lbl_cancel_request') }}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="{{ __('frontend.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <p style="color:var(--pp-muted);font-size:0.875rem;margin-bottom:1rem">{{ __('intensive_care.lbl_are_you_sure') }}</p>
                            <div class="pp-form-group">
                                <label class="pp-label">{{ __('intensive_care.lbl_cancellation_reason') }}</label>
                                <textarea class="pp-textarea"
                                          name="cancellation_reason"
                                          rows="3"
                                          placeholder="{{ __('intensive_care.lbl_cancellation_reason') }}..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                            <button type="submit" class="pp-btn" style="background:var(--pp-cancelled);color:#fff">
                                <i class="ph ph-x-circle"></i>{{ __('intensive_care.lbl_cancel_request') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Respond Modal (info_requested) ── --}}
    @if($admission->status === \Modules\IntensiveCare\Models\IcuAdmissionRequest::STATUS_INFO_REQUESTED)
        <div class="modal fade pp-modal" id="respondModal" tabindex="-1"
             aria-labelledby="respondModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('respond-icu-request', $admission->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ph ph-paper-plane-right" style="color:var(--pp-teal);font-size:1.25rem" aria-hidden="true"></i>
                                <h5 class="modal-title m-0" id="respondModalLabel">{{ __('intensive_care.lbl_respond_to_request') }}</h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="{{ __('frontend.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            @if($admission->admin_notes)
                                <div class="pp-detail-section mb-3">
                                    <div class="pp-detail-section-header">
                                        <i class="ph ph-info" aria-hidden="true"></i>
                                        <span style="font-size:0.78rem;font-weight:700;font-family:var(--pp-font)">
                                            {{ __('intensive_care.lbl_admin_notes') }}
                                        </span>
                                    </div>
                                    <div class="pp-detail-section-body">
                                        <p style="font-size:0.875rem;margin:0;color:var(--pp-text-2)">{{ $admission->admin_notes }}</p>
                                    </div>
                                </div>
                            @endif
                            <div class="pp-form-group">
                                <label class="pp-label">{{ __('intensive_care.lbl_medical_reports') }}</label>
                                <input type="file" class="pp-input" name="medical_reports[]" multiple
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                       style="padding:0.4rem 0.875rem">
                                <small style="font-size:0.72rem;color:var(--pp-muted);margin-top:4px;display:block;font-family:var(--pp-font)">
                                    {{ __('intensive_care.lbl_file_format_hint') }}
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                            <button type="submit" class="pp-btn pp-btn-teal">
                                <i class="ph ph-paper-plane-right"></i>{{ __('intensive_care.lbl_submit') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
