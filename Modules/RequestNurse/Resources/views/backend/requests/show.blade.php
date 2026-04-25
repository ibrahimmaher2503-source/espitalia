@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@php
    $statusOrder = ['pending', 'confirmed', 'in_progress', 'completed'];
    $currentIndex = array_search($nurseRequest->status, $statusOrder);
    $isCancelled = $nurseRequest->status === \Modules\RequestNurse\Models\NurseRequest::STATUS_CANCELLED;
@endphp

@section('content')
    {{-- Header Row with Status Timeline --}}
    <div class="card nr-detail-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h5 class="mb-1">{{ __('request_nurse.nurse_request_detail') }} <span class="text-muted">#{{ $nurseRequest->id }}</span></h5>
                    <p class="text-muted mb-0">
                        {{ __('request_nurse.lbl_created') }}: {{ $nurseRequest->created_at ? $nurseRequest->created_at->format('d M Y, H:i') : '-' }}
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    {!! $nurseRequest->status_badge !!}
                    <a href="{{ route('backend.nurse-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </div>

            {{-- Status Timeline --}}
            @if(!$isCancelled)
            <div class="nr-timeline">
                @foreach($statusOrder as $i => $step)
                    @php
                        $isActive = $currentIndex !== false && $i <= $currentIndex;
                        $isCurrent = $currentIndex !== false && $i === $currentIndex;
                        $stepColors = [
                            'pending' => '#f59e0b',
                            'confirmed' => '#06b6d4',
                            'in_progress' => '#3b82f6',
                            'completed' => '#10b981',
                        ];
                        $color = $isActive ? $stepColors[$step] : '#d1d5db';
                    @endphp
                    <div class="nr-timeline-step {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="nr-timeline-dot" style="background: {{ $color }}; {{ $isCurrent ? 'box-shadow: 0 0 0 4px ' . $color . '33;' : '' }}">
                            @if($isActive && !$isCurrent)
                                <i class="ph ph-check" style="font-size:0.7rem;color:#fff;"></i>
                            @endif
                        </div>
                        <span class="nr-timeline-label" style="{{ $isActive ? 'color:' . $color . '; font-weight:600;' : '' }}">
                            {{ __('request_nurse.status_' . $step) }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="nr-timeline-line" style="background: {{ $isActive && $i < $currentIndex ? $stepColors[$statusOrder[$i+1]] : '#e5e7eb' }};"></div>
                    @endif
                @endforeach
            </div>
            @else
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" style="border-radius:10px;">
                    <i class="ph ph-x-circle fs-4"></i>
                    <div>
                        <strong>{{ __('request_nurse.lbl_request_cancelled') }}</strong>
                        @if($nurseRequest->cancelled_by)
                            <span class="ms-1 text-muted">{{ __('request_nurse.lbl_by') }} {{ $nurseRequest->cancelled_by }}</span>
                        @endif
                        @if($nurseRequest->cancellation_reason)
                            <p class="mb-0 mt-1">{{ $nurseRequest->cancellation_reason }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-4">
            {{-- Patient Card --}}
            <div class="card nr-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-user me-1"></i> {{ __('request_nurse.lbl_patient_name') }}</h6>
                </div>
                <div class="card-body text-center py-4">
                    @if($nurseRequest->patient)
                        <div class="mb-3">
                            <div class="nr-avatar mx-auto">
                                @if($nurseRequest->patient->profile_image)
                                    <img src="{{ $nurseRequest->patient->profile_image }}" alt="{{ $nurseRequest->patient->first_name }}">
                                @else
                                    <i class="ph ph-user"></i>
                                @endif
                            </div>
                        </div>
                        <h6 class="mb-2">{{ $nurseRequest->patient->first_name }} {{ $nurseRequest->patient->last_name }}</h6>
                        <div class="d-flex flex-column align-items-center gap-1">
                            <span class="text-muted small"><i class="ph ph-envelope-simple me-1"></i> {{ $nurseRequest->patient->email }}</span>
                            <span class="text-muted small"><i class="ph ph-phone me-1"></i> {{ $nurseRequest->contact_number }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Address Card --}}
            <div class="card nr-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-map-pin me-1"></i> {{ __('request_nurse.lbl_address') }}</h6>
                </div>
                <div class="card-body">
                    <div class="nr-address-block">
                        <p class="mb-1 fw-medium">{{ $nurseRequest->address_line_1 }}</p>
                        @if($nurseRequest->address_line_2)
                            <p class="mb-1">{{ $nurseRequest->address_line_2 }}</p>
                        @endif
                        <p class="mb-1">{{ $nurseRequest->city }}@if($nurseRequest->state), {{ $nurseRequest->state }}@endif</p>
                        @if($nurseRequest->postal_code)
                            <p class="mb-1">{{ $nurseRequest->postal_code }}</p>
                        @endif
                        @if($nurseRequest->country)
                            <p class="mb-0 text-muted">{{ $nurseRequest->country }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Assigned Nurse Card --}}
            <div class="card nr-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-first-aid me-1"></i> {{ __('request_nurse.lbl_assigned_nurse') }}</h6>
                </div>
                <div class="card-body">
                    @if($nurseRequest->nurse)
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="nr-avatar nr-avatar-sm">
                                <i class="ph ph-first-aid"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $nurseRequest->nurse->first_name }} {{ $nurseRequest->nurse->last_name }}</h6>
                                @if($nurseRequest->nurseProfile)
                                    <span class="text-muted small">{{ $nurseRequest->nurseProfile->specialization ?? '-' }}</span>
                                @endif
                            </div>
                        </div>
                        @if($nurseRequest->nurseProfile)
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                <span class="text-muted small">{{ __('request_nurse.lbl_hourly_rate') }}</span>
                                <span class="fw-semibold">{{ number_format($nurseRequest->nurseProfile->hourly_rate, 2) }}</span>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <div class="nr-avatar nr-avatar-placeholder mx-auto mb-2">
                                <i class="ph ph-user-plus"></i>
                            </div>
                            <p class="text-muted mb-3">{{ __('request_nurse.lbl_unassigned') }}</p>
                            @if($nurseRequest->status === \Modules\RequestNurse\Models\NurseRequest::STATUS_PENDING)
                                <div class="d-flex gap-2">
                                    <select class="form-control select2" id="assign-nurse-select" style="width:70%">
                                        <option value="">{{ __('request_nurse.lbl_select_nurse') }}</option>
                                        @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->nurse_id }}">{{ $nurse->user->first_name ?? '' }} {{ $nurse->user->last_name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-primary btn-sm" id="assign-nurse-btn">
                                        <i class="ph ph-user-plus"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-8">
            {{-- Service Details --}}
            <div class="card nr-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-clipboard-text me-1"></i> {{ __('request_nurse.lbl_service_details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:0.5px;">{{ __('request_nurse.lbl_service_description') }}</label>
                        <p class="mb-0" style="line-height:1.7;">{{ $nurseRequest->service_description }}</p>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="nr-info-block">
                                <i class="ph ph-calendar"></i>
                                <div>
                                    <span class="text-muted small">{{ __('request_nurse.lbl_preferred_date') }}</span>
                                    <p class="mb-0 fw-medium">{{ $nurseRequest->preferred_date ? $nurseRequest->preferred_date->format('d M Y') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="nr-info-block">
                                <i class="ph ph-clock"></i>
                                <div>
                                    <span class="text-muted small">{{ __('request_nurse.lbl_preferred_time') }}</span>
                                    <p class="mb-0 fw-medium">{{ $nurseRequest->preferred_time ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="nr-info-block">
                                <i class="ph ph-timer"></i>
                                <div>
                                    <span class="text-muted small">{{ __('request_nurse.lbl_duration_hours') }}</span>
                                    <p class="mb-0 fw-medium">{{ $nurseRequest->duration_hours }} {{ __('messages.hours') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="nr-info-block">
                                <i class="ph ph-currency-dollar"></i>
                                <div>
                                    <span class="text-muted small">{{ __('request_nurse.lbl_total_amount') }}</span>
                                    <p class="mb-0 fw-bold fs-5" style="color:#6366f1;">{{ number_format($nurseRequest->total_amount, 2) }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="nr-info-block">
                                <i class="ph ph-credit-card"></i>
                                <div>
                                    <span class="text-muted small">{{ __('request_nurse.lbl_payment_status') }}</span>
                                    <p class="mb-0">
                                        @if($nurseRequest->payment_status)
                                            <span class="badge bg-success">{{ __('request_nurse.status_paid') }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ __('request_nurse.status_unpaid') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="nr-info-block">
                                <i class="ph ph-calendar-blank"></i>
                                <div>
                                    <span class="text-muted small">{{ __('request_nurse.lbl_request_date') }}</span>
                                    <p class="mb-0 fw-medium">{{ $nurseRequest->request_date ? \Carbon\Carbon::parse($nurseRequest->request_date)->format('d M Y') : '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card nr-detail-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-note me-1"></i> {{ __('request_nurse.lbl_notes') }}</h6>
                </div>
                <div class="card-body">
                    @if($nurseRequest->patient_notes)
                        <div class="mb-4">
                            <label class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:0.5px;">{{ __('request_nurse.lbl_patient_notes') }}</label>
                            <div class="nr-note-block">
                                {{ $nurseRequest->patient_notes }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="text-muted small text-uppercase d-block mb-1" style="letter-spacing:0.5px;">{{ __('request_nurse.lbl_admin_notes') }}</label>
                        <textarea class="form-control" id="admin-notes" rows="3" placeholder="{{ __('request_nurse.lbl_admin_notes_placeholder') }}" style="border-radius:10px;">{{ $nurseRequest->admin_notes }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            @if(!in_array($nurseRequest->status, [\Modules\RequestNurse\Models\NurseRequest::STATUS_COMPLETED, \Modules\RequestNurse\Models\NurseRequest::STATUS_CANCELLED]))
                <div class="card nr-detail-card mb-4">
                    <div class="card-body">
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <span class="text-muted small me-2">{{ __('request_nurse.lbl_actions') }}:</span>

                            @if($nurseRequest->status === \Modules\RequestNurse\Models\NurseRequest::STATUS_PENDING && $nurseRequest->nurse_id)
                                <button class="btn btn-info btn-sm status-change-btn" data-status="confirmed">
                                    <i class="ph ph-check-circle me-1"></i> {{ __('request_nurse.status_confirmed') }}
                                </button>
                            @endif

                            @if($nurseRequest->status === \Modules\RequestNurse\Models\NurseRequest::STATUS_CONFIRMED)
                                <button class="btn btn-primary btn-sm status-change-btn" data-status="in_progress">
                                    <i class="ph ph-play me-1"></i> {{ __('request_nurse.status_in_progress') }}
                                </button>
                            @endif

                            @if($nurseRequest->status === \Modules\RequestNurse\Models\NurseRequest::STATUS_IN_PROGRESS)
                                <button class="btn btn-success btn-sm status-change-btn" data-status="completed">
                                    <i class="ph ph-check me-1"></i> {{ __('request_nurse.status_completed') }}
                                </button>
                            @endif

                            <button class="btn btn-outline-danger btn-sm ms-auto" id="cancel-request-btn">
                                <i class="ph ph-x-circle me-1"></i> {{ __('request_nurse.lbl_cancel_request') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-bottom:none;">
                    <h6 class="modal-title"><i class="ph ph-warning-circle text-danger me-1"></i> {{ __('request_nurse.lbl_cancel_request') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">{{ __('request_nurse.lbl_cancellation_reason') }}</label>
                    <textarea class="form-control" id="cancellation-reason" rows="3" style="border-radius:10px;" placeholder="{{ __('request_nurse.lbl_cancellation_reason_placeholder') }}"></textarea>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirm-cancel-btn">
                        <i class="ph ph-x-circle me-1"></i> {{ __('request_nurse.lbl_confirm_cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-styles')
<style>
    .nr-detail-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .nr-detail-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .nr-detail-card .card-header h6 {
        color: #374151;
        font-weight: 600;
    }

    /* Status Timeline */
    .nr-timeline {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        padding: 0.5rem 0;
    }
    .nr-timeline-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        z-index: 1;
    }
    .nr-timeline-dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    .nr-timeline-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #9ca3af;
        white-space: nowrap;
    }
    .nr-timeline-line {
        flex: 1;
        height: 3px;
        border-radius: 2px;
        margin: 0 -4px;
        margin-bottom: 24px;
        min-width: 40px;
    }

    /* Avatar */
    .nr-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(6,182,212,0.1));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #6366f1;
        overflow: hidden;
    }
    .nr-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .nr-avatar.nr-avatar-sm {
        width: 44px;
        height: 44px;
        font-size: 1.2rem;
    }
    .nr-avatar.nr-avatar-placeholder {
        border: 2px dashed rgba(99,102,241,0.3);
        background: rgba(99,102,241,0.05);
    }

    /* Info Block */
    .nr-info-block {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        background: #f9fafb;
        border-radius: 10px;
        height: 100%;
    }
    .nr-info-block > i {
        font-size: 1.25rem;
        color: #6366f1;
        margin-top: 2px;
    }

    /* Note Block */
    .nr-note-block {
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 10px;
        border-left: 3px solid #e5e7eb;
        line-height: 1.7;
        color: #4b5563;
    }

    /* Address Block */
    .nr-address-block {
        padding: 12px 16px;
        background: #f9fafb;
        border-radius: 10px;
    }

    @media (max-width: 768px) {
        .nr-timeline {
            flex-wrap: wrap;
            gap: 4px;
        }
        .nr-timeline-line {
            min-width: 20px;
        }
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // Assign nurse
        $('#assign-nurse-btn').on('click', function() {
            var nurseId = $('#assign-nurse-select').val();
            if (!nurseId) return;

            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="ph ph-spinner ph-spin"></i>');

            $.ajax({
                url: '{{ route("backend.nurse-requests.assign_nurse", $nurseRequest->id) }}',
                method: 'POST',
                data: {
                    nurse_id: nurseId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="ph ph-user-plus"></i>');
                }
            });
        });

        // Status change buttons
        $('.status-change-btn').on('click', function() {
            var newStatus = $(this).data('status');
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("backend.nurse-requests.update_status", $nurseRequest->id) }}',
                method: 'POST',
                data: {
                    status: newStatus,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

        // Cancel request
        $('#cancel-request-btn').on('click', function() {
            $('#cancelModal').modal('show');
        });

        $('#confirm-cancel-btn').on('click', function() {
            var $btn = $(this);
            $btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("backend.nurse-requests.update_status", $nurseRequest->id) }}',
                method: 'POST',
                data: {
                    status: 'cancelled',
                    cancellation_reason: $('#cancellation-reason').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
