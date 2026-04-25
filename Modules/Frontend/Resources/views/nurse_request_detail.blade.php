@extends('frontend::layouts.master')
@section('title', __('request_nurse.nurse_request_detail') . ' #' . $nurseRequest->id)

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">
            <div class="row g-4">

                {{-- ── Sidebar ── --}}
                <div class="col-lg-4">
                    <div class="pp-detail-section" style="position:sticky;top:5rem">

                        <div class="pp-detail-section-header" style="justify-content:center;padding:1.5rem">
                            <i class="ph ph-first-aid" style="font-size:3rem;color:var(--pp-teal)" aria-hidden="true"></i>
                        </div>

                        <div class="pp-detail-section-body">
                            {{-- Status --}}
                            <div class="text-center mb-3">{!! $nurseRequest->status_badge !!}</div>

                            {{-- Key details --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-calendar flex-shrink-0" style="color:var(--pp-teal)"></i>
                                    <div>
                                        <div style="font-size:0.7rem;color:var(--pp-muted);text-transform:uppercase;letter-spacing:.05em;font-family:var(--pp-font)">
                                            {{ __('request_nurse.lbl_preferred_date') }}
                                        </div>
                                        <div style="font-weight:600;font-family:var(--pp-font);font-size:0.875rem">
                                            {{ $nurseRequest->preferred_date
                                                ? (is_string($nurseRequest->preferred_date)
                                                    ? \Carbon\Carbon::parse($nurseRequest->preferred_date)->format('d M Y')
                                                    : $nurseRequest->preferred_date->format('d M Y'))
                                                : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-clock flex-shrink-0" style="color:var(--pp-teal)"></i>
                                    <div>
                                        <div style="font-size:0.7rem;color:var(--pp-muted);text-transform:uppercase;letter-spacing:.05em;font-family:var(--pp-font)">
                                            {{ __('request_nurse.lbl_preferred_time') }}
                                        </div>
                                        <div style="font-weight:600;font-family:var(--pp-font);font-size:0.875rem">
                                            {{ $nurseRequest->preferred_time ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-hourglass flex-shrink-0" style="color:var(--pp-teal)"></i>
                                    <div>
                                        <div style="font-size:0.7rem;color:var(--pp-muted);text-transform:uppercase;letter-spacing:.05em;font-family:var(--pp-font)">
                                            {{ __('request_nurse.lbl_duration_hours') }}
                                        </div>
                                        <div style="font-weight:600;font-family:var(--pp-font);font-size:0.875rem">
                                            {{ $nurseRequest->duration_hours }} {{ __('request_nurse.lbl_hours_short') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Total amount pill --}}
                            <div class="rate-meta-box rounded-pill d-flex align-items-center justify-content-center gap-2 py-2 mb-3">
                                <i class="ph ph-currency-dollar" aria-hidden="true"></i>
                                <h5 class="m-0">{{ number_format($nurseRequest->total_amount, 2) }}</h5>
                            </div>

                            {{-- Payment status --}}
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <span style="font-size:0.78rem;color:var(--pp-muted);font-family:var(--pp-font)">
                                    {{ __('request_nurse.lbl_payment_status') }}
                                </span>
                                @if($nurseRequest->payment_status)
                                    <span class="pp-pay-status pp-pay-paid">
                                        <i class="ph ph-check-circle"></i>{{ __('request_nurse.status_paid') }}
                                    </span>
                                @else
                                    <span class="pp-pay-status pp-pay-pending">
                                        <i class="ph ph-clock"></i>{{ __('request_nurse.status_unpaid') }}
                                    </span>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <a href="{{ route('my-nurse-requests') }}" class="pp-btn pp-btn-outline w-100 mb-2">
                                <i class="ph ph-arrow-left"></i>{{ __('messages.back') }}
                            </a>

                            @php
                                $cancellableStatuses = [
                                    \Modules\RequestNurse\Models\NurseRequest::STATUS_PENDING,
                                    \Modules\RequestNurse\Models\NurseRequest::STATUS_CONFIRMED,
                                ];
                            @endphp

                            @if(in_array($nurseRequest->status, $cancellableStatuses))
                                <button type="button"
                                        class="pp-btn w-100"
                                        style="background:var(--pp-cancelled);color:#fff"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cancelRequestModal">
                                    <i class="ph ph-x-circle"></i>{{ __('request_nurse.lbl_cancel_request') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Main content ── --}}
                <div class="col-lg-8">

                    {{-- Cancellation alert --}}
                    @if($nurseRequest->status === \Modules\RequestNurse\Models\NurseRequest::STATUS_CANCELLED)
                        <div class="pp-cancel-alert mb-3">
                            <p class="pp-cancel-alert-title">
                                <i class="ph ph-x-circle" aria-hidden="true"></i>
                                {{ __('request_nurse.lbl_request_cancelled') }}
                            </p>
                            @if($nurseRequest->cancellation_reason)
                                <p style="font-size:0.875rem;color:var(--pp-text-2);margin:0 0 4px">
                                    <strong>{{ __('request_nurse.lbl_cancellation_reason') }}:</strong>
                                    {{ $nurseRequest->cancellation_reason }}
                                </p>
                            @endif
                            @if($nurseRequest->cancelled_by)
                                <p style="font-size:0.78rem;color:var(--pp-muted);margin:0">
                                    {{ __('request_nurse.lbl_by') }}: {{ $nurseRequest->cancelled_by }}
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Section 1: Service Details --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('request_nurse.lbl_service_details') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            @if($nurseRequest->service_description)
                                <p style="color:var(--pp-muted);font-size:0.875rem;margin-bottom:1rem">
                                    {{ $nurseRequest->service_description }}
                                </p>
                            @endif
                            <div class="pp-detail-grid">
                                <div>
                                    <p class="pp-detail-label">{{ __('request_nurse.lbl_preferred_date') }}</p>
                                    <p class="pp-detail-value">
                                        {{ $nurseRequest->preferred_date
                                            ? (is_string($nurseRequest->preferred_date)
                                                ? \Carbon\Carbon::parse($nurseRequest->preferred_date)->format('d M Y')
                                                : $nurseRequest->preferred_date->format('d M Y'))
                                            : '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('request_nurse.lbl_preferred_time') }}</p>
                                    <p class="pp-detail-value">{{ $nurseRequest->preferred_time ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('request_nurse.lbl_duration_hours') }}</p>
                                    <p class="pp-detail-value">{{ $nurseRequest->duration_hours }} {{ __('request_nurse.lbl_hours') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 2: Assigned Nurse --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-first-aid" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('request_nurse.lbl_assigned_nurse') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            @if($nurseRequest->nurse)
                                @php
                                    $nurseImg = optional(optional($nurseRequest->nurse)->getFirstMedia('profile_image'))->getUrl()
                                        ?: asset('img/avatar/avatar.webp');
                                @endphp
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $nurseImg }}"
                                         class="pp-patient-avatar"
                                         style="width:56px;height:56px"
                                         alt="{{ $nurseRequest->nurse->first_name ?? '' }}">
                                    <div>
                                        <p style="font-weight:700;font-family:var(--pp-font);color:var(--pp-navy);margin:0 0 2px">
                                            {{ $nurseRequest->nurse->first_name ?? '' }} {{ $nurseRequest->nurse->last_name ?? '' }}
                                        </p>
                                        @if($nurseRequest->nurseProfile && $nurseRequest->nurseProfile->specialization)
                                            <p style="font-size:0.78rem;color:var(--pp-muted);margin:0;font-family:var(--pp-font)">
                                                <i class="ph ph-first-aid" aria-hidden="true"></i>
                                                {{ $nurseRequest->nurseProfile->specialization }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="pp-empty" style="padding:1.5rem 0">
                                    <i class="ph ph-user-circle-dashed" aria-hidden="true"></i>
                                    <p>{{ __('request_nurse.lbl_unassigned') }} — {{ __('request_nurse.status_pending') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Section 3: Location --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-map-pin" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('request_nurse.lbl_address') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="d-flex align-items-start gap-2">
                                <i class="ph ph-map-pin flex-shrink-0 mt-1" style="color:var(--pp-teal)" aria-hidden="true"></i>
                                <div>
                                    <p style="font-size:0.875rem;margin:0 0 4px">{{ $nurseRequest->full_address ?? '-' }}</p>
                                    @if($nurseRequest->contact_number)
                                        <p style="font-size:0.78rem;color:var(--pp-muted);margin:0;font-family:var(--pp-font)">
                                            <i class="ph ph-phone" aria-hidden="true"></i> {{ $nurseRequest->contact_number }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 4: Patient Notes --}}
                    @if($nurseRequest->patient_notes)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-note" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('request_nurse.lbl_patient_notes') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <p style="color:var(--pp-muted);font-size:0.875rem;margin:0">{{ $nurseRequest->patient_notes }}</p>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    {{-- ── Cancel Modal ── --}}
    @if(in_array($nurseRequest->status, $cancellableStatuses ?? []))
        <div class="modal fade pp-modal pp-modal-danger" id="cancelRequestModal" tabindex="-1"
             aria-labelledby="cancelRequestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('cancel-nurse-request', $nurseRequest->id) }}" id="cancelRequestForm">
                        @csrf
                        <div class="modal-header">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ph ph-x-circle" style="color:var(--pp-cancelled);font-size:1.25rem" aria-hidden="true"></i>
                                <h5 class="modal-title m-0" id="cancelRequestModalLabel">
                                    {{ __('request_nurse.lbl_confirm_cancel') }}
                                </h5>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="{{ __('frontend.close') }}"></button>
                        </div>
                        <div class="modal-body">
                            <p style="color:var(--pp-muted);font-size:0.875rem;margin-bottom:1rem">{{ __('messages.are_you_sure') }}</p>
                            <div class="pp-form-group">
                                <label class="pp-label" for="cancellationReason">
                                    {{ __('request_nurse.lbl_cancellation_reason') }}<span class="pp-required">*</span>
                                </label>
                                <textarea class="pp-textarea" id="cancellationReason" name="cancellation_reason"
                                          rows="3" required
                                          placeholder="{{ __('request_nurse.lbl_cancellation_reason_placeholder') }}"></textarea>
                                <small id="cancelReasonError" class="pp-field-error d-none">
                                    {{ __('messages.please_enter_reason') }}
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">
                                {{ __('messages.go_back') }}
                            </button>
                            <button type="submit" class="pp-btn" id="confirmCancelBtn"
                                    style="background:var(--pp-cancelled);color:#fff">
                                <i class="ph ph-x-circle"></i>{{ __('request_nurse.lbl_cancel_request') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        (function () {
            'use strict';
            document.getElementById('cancelRequestForm').addEventListener('submit', function (e) {
                var reason  = document.getElementById('cancellationReason').value.trim();
                var errEl   = document.getElementById('cancelReasonError');
                var inp     = document.getElementById('cancellationReason');
                if (!reason) {
                    e.preventDefault();
                    inp.classList.add('is-invalid');
                    errEl.classList.remove('d-none');
                    return;
                }
                inp.classList.remove('is-invalid');
                errEl.classList.add('d-none');
                var btn = document.getElementById('confirmCancelBtn');
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __('messages.loading') }}...';
            });
        }());
        </script>
    @endif
@endsection
