@php
    /* ── Refund amount (only for cancelled paid appointments) ── */
    $showRefundBanner = (
        $appointment->status == 'cancelled' &&
        optional($appointment->appointmenttransaction)->payment_status != 0 &&
        optional($appointment->appointmenttransaction)->transaction_type != 'cash'
    );
    $refundAmount = $showRefundBanner ? $appointment->getRefundAmount() : null;

    /* ── Service name ── */
    $serviceName = $appointment->booking_type === 'independent'
        ? optional($appointment->independentService)->name ?? '—'
        : optional($appointment->clinicservice)->name ?? '—';

    /* ── Status badge CSS class ── */
    $statusClass = match($appointment->status) {
        'pending'   => 'pp-status-pending',
        'confirmed' => 'pp-status-confirmed',
        'completed' => 'pp-status-completed',
        'cancelled' => 'pp-status-cancelled',
        'check_in'  => 'pp-status-checkin',
        'checkout'  => 'pp-status-checkout',
        default     => 'pp-status-pending',
    };
    $statusLabel = match($appointment->status) {
        'checkout' => __('frontend.completed'),
        default    => \Illuminate\Support\Str::title(str_replace('_', ' ', $appointment->status)),
    };

    /* ── Payment status ── */
    $payClass  = 'pp-pay-pending';
    $payLabel  = __('frontend.unpaid') ?: 'Unpaid';

    if ($appointment->appointmenttransaction && $appointment->appointmenttransaction->payment_status) {
        if ($appointment->status == 'cancelled') {
            if ($appointment->advance_paid_amount > 0) {
                $payClass = 'pp-pay-advance';
                $payLabel = __('frontend.advance_refunded');
            } else {
                $payClass = 'pp-pay-refunded';
                $payLabel = __('frontend.payment_refunded');
            }
        } else {
            $payClass = 'pp-pay-paid';
            $payLabel = __('frontend.paid') ?: 'Paid';
        }
    } elseif ($appointment->advance_paid_amount > 0) {
        if ($appointment->status == 'cancelled') {
            $payClass = 'pp-pay-advance';
            $payLabel = __('frontend.advance_refunded');
        } else {
            $payClass = 'pp-pay-advance';
            $payLabel = __('frontend.advance_paid');
        }
    }

    /* ── Total amount ── */
    $totalAmount = 0;
    if ($appointment->patientEncounter != null) {
        $totalAmount = !empty(optional($appointment->patientEncounter->billingrecord)->final_total_amount)
            ? optional($appointment->patientEncounter->billingrecord)->final_total_amount
            : $appointment->total_amount;
    } else {
        $totalAmount = $appointment->total_amount;
    }

    /* ── Service rating ── */
    $serviceRating = optional($appointment->clinicservice)->serviceRating;
    $canRate = !is_null($serviceRating) && $serviceRating->isEmpty() && $appointment->status == 'checkout';
@endphp

{{-- ── Refund Banner (above the card) ── --}}
@if($showRefundBanner)
<div class="pp-refund-banner {{ $refundAmount >= 0 ? 'pp-refund-positive' : 'pp-refund-negative' }} mb-2">
    <span>
        <i class="ph {{ $refundAmount >= 0 ? 'ph-arrow-circle-down' : 'ph-arrow-circle-up' }} me-1 align-middle"></i>
        {{ $refundAmount >= 0 ? __('frontend.refund_completed') : __('frontend.wallet_deducted') }}
    </span>
    <span>{{ \Currency::format(abs($refundAmount)) }}</span>
</div>
@endif

<li class="pp-appt-card">

    {{-- ── Header: date/time + status ── --}}
    <div class="pp-appt-header">
        <div class="pp-appt-datetime">
            <span class="pp-appt-date">
                <i class="ph ph-calendar-blank me-1 align-middle" style="font-size:0.85rem;opacity:0.7"></i>
                {{ DateFormate($appointment->appointment_date) }}
            </span>
            <span class="pp-appt-sep"></span>
            <span class="pp-appt-time">
                {{ \Carbon\Carbon::parse($appointment->appointment_time)->format(setting('time_formate') ?? 'h:i A') }}
            </span>
        </div>
        <div class="pp-appt-meta">
            <span class="pp-appt-id">
                {{ __('frontend.appointment_id') ?? 'ID' }}:
                <strong>#{{ $appointment->id }}</strong>
            </span>
            @if(optional($appointment->clinicservice)->is_video_consultancy)
            <span class="pp-badge pp-badge-teal" title="{{ __('frontend.video_call') ?? 'Video call' }}">
                <i class="ph ph-video-camera"></i>
                {{ __('frontend.video_call') ?? 'Video' }}
            </span>
            @endif
            <span class="pp-status {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </div>

    {{-- ── Body ── --}}
    <div class="pp-appt-body">

        {{-- Service name --}}
        <h5 class="pp-appt-service">{{ $serviceName }}</h5>

        {{-- Booked for another patient --}}
        @if($appointment->otherPatient)
        <div class="pp-booked-for">
            <span>{{ __('frontend.booked_for') ?? 'Booked for' }}</span>
            <img src="{{ optional($appointment->otherPatient)->profile_image ?: asset('images/default-avatar.png') }}"
                 alt="{{ optional($appointment->otherPatient)->first_name }}">
            <strong>{{ optional($appointment->otherPatient)->first_name }} {{ optional($appointment->otherPatient)->last_name }}</strong>
        </div>
        @endif

        {{-- Details grid --}}
        <div class="pp-appt-grid">
            <div class="pp-detail-item">
                <span class="pp-detail-label">{{ __('frontend.appointment_type') }}</span>
                <span class="pp-detail-value">
                    @if($appointment->booking_type === 'independent')
                        <span class="pp-badge pp-badge-info">{{ __('frontend.lbl_independent') }}</span>
                    @else
                        {{ \Illuminate\Support\Str::title(str_replace('_', ' ', optional($appointment->clinicservice)->type ?? '—')) }}
                    @endif
                </span>
            </div>
            <div class="pp-detail-item">
                <span class="pp-detail-label">{{ __('frontend.clinic_name') }}</span>
                <span class="pp-detail-value">
                    {{ $appointment->booking_type === 'independent' ? '—' : (optional($appointment->cliniccenter)->name ?? '—') }}
                </span>
            </div>
            <div class="pp-detail-item">
                <span class="pp-detail-label">{{ __('frontend.doctor_name') }}</span>
                <span class="pp-detail-value">{{ getDisplayName($appointment->doctor) }}</span>
            </div>
            <div class="pp-detail-item">
                <span class="pp-detail-label">{{ __('frontend.price') }}</span>
                <span class="pp-detail-value">{{ Currency::format($totalAmount) ?? '—' }}</span>
            </div>
            <div class="pp-detail-item">
                <span class="pp-detail-label">{{ __('frontend.payment_status') }}</span>
                <span class="pp-detail-value">
                    <span class="pp-pay-status {{ $payClass }}">
                        <i class="ph {{ str_contains($payClass, 'paid') || str_contains($payClass, 'refunded') ? 'ph-check-circle' : 'ph-clock' }} align-middle"></i>
                        {{ $payLabel }}
                    </span>
                </span>
            </div>
        </div>

        {{-- ── Actions footer ── --}}
        <div class="pp-appt-footer" style="margin: 0 -1.25rem -1rem; padding: 0.75rem 1.25rem;">
            <div class="pp-appt-actions">
                {{-- Cancel button --}}
                @if($appointment->status == 'pending' && optional($appointment->appointmenttransaction)->transaction_type == 'cash')
                    <button class="pp-btn pp-btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#cancel-appointment"
                        data-appointment-id="{{ $appointment->id }}"
                        data-charge="0"
                        type="button">
                        <i class="ph ph-x-circle"></i>{{ __('frontend.cancel') }}
                    </button>
                @elseif($appointment->status == 'pending' || $appointment->status == 'confirmed')
                    <button class="pp-btn pp-btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#cancel-appointment"
                        data-appointment-id="{{ $appointment->id }}"
                        data-charge="{{ $appointment->getCancellationCharges() }}"
                        type="button">
                        <i class="ph ph-x-circle"></i>{{ __('frontend.cancel') }}
                    </button>
                @endif

                {{-- Encounter button --}}
                @if($appointment->status == 'checkout' || $appointment->status == 'check_in')
                    <button class="pp-btn pp-btn-teal"
                        data-bs-toggle="modal"
                        data-bs-target="#encounter-details-view-{{ $appointment->id }}"
                        type="button">
                        <i class="ph ph-stethoscope"></i>{{ __('frontend.encounter') }}
                    </button>
                @endif
            </div>

            <div class="pp-appt-actions">
                {{-- Rate us --}}
                @if($canRate)
                <button class="pp-btn pp-btn-outline"
                    data-bs-toggle="modal"
                    data-service-id="{{ optional($appointment->clinicservice)->id }}"
                    data-doctor-id="{{ optional($appointment->doctor)->id }}"
                    data-bs-target="#review-service"
                    type="button">
                    <i class="ph-fill ph-star" style="color:var(--pp-checkout)"></i>{{ __('frontend.rate_us') }}
                </button>
                @endif

                {{-- View detail --}}
                <a href="{{ route('appointment-details', ['id' => $appointment->id]) }}"
                   class="pp-btn pp-btn-ghost">
                    {{ __('frontend.view_detail') }}<i class="ph ph-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>
</li>


{{-- ═══════════════════════════════════════════════════
     ENCOUNTER DETAIL MODAL
     ═══════════════════════════════════════════════════ --}}
@php
    $problems      = $medical_history->get('encounter_problem', collect());
    $observations  = $medical_history->get('encounter_observations', collect());
    $notes         = $medical_history->get('encounter_notes', collect());
@endphp

<div class="modal fade pp-modal" id="encounter-details-view-{{ $appointment->id }}" tabindex="-1"
     aria-labelledby="encounter-title-{{ $appointment->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="encounter-title-{{ $appointment->id }}">
                    <i class="ph ph-stethoscope me-2" style="color:var(--pp-teal)"></i>
                    {{ __('frontend.encounter') }}
                    <span style="font-size:0.75rem;font-weight:500;color:var(--pp-muted);margin-left:0.5rem">#{{ $appointment->id }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body">

                {{-- Basic info --}}
                <div class="pp-card mb-3">
                    <div class="pp-card-header">
                        <h6 class="pp-card-title"><i class="ph ph-info"></i>{{ __('frontend.basic_information') }}</h6>
                        @php $enc = optional($appointment->patientEncounter); @endphp
                        <span class="pp-status {{ $enc->status ? 'pp-status-confirmed' : 'pp-status-cancelled' }}">
                            {{ $enc->status ? 'Active' : 'Closed' }}
                        </span>
                    </div>
                    <div class="pp-card-body">
                        <div class="row g-2" style="font-family:var(--pp-font);font-size:0.82rem">
                            <div class="col-sm-4">
                                <span style="color:var(--pp-muted)">{{ __('frontend.appointment_id') }}:</span>
                                <strong style="color:var(--pp-teal);margin-left:4px">#{{ $appointment->id }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <span style="color:var(--pp-muted)">{{ __('frontend.doctor_name') }}:</span>
                                <strong style="margin-left:4px">{{ getDisplayName($appointment->doctor) }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <span style="color:var(--pp-muted)">{{ __('frontend.clinic_name') }}:</span>
                                <strong style="margin-left:4px">{{ optional($appointment->cliniccenter)->name ?? '—' }}</strong>
                            </div>
                            @if($enc->descrtiption)
                            <div class="col-12 mt-2" style="padding-top:0.625rem;border-top:1px solid var(--pp-border)">
                                <span style="color:var(--pp-muted)">{{ __('frontend.description') }}:</span>
                                <span style="margin-left:6px;font-weight:500">{{ $enc->descrtiption }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Problems --}}
                <div class="pp-encounter-section" id="enc-wrap-problems-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-problems-{{ $appointment->id }}', 'enc-wrap-problems-{{ $appointment->id }}')"
                         role="button" aria-expanded="false">
                        <h6 class="pp-encounter-toggle-title">
                            <i class="ph ph-warning-circle"></i>{{ __('frontend.problems') ?? 'Problems' }}
                            @if($problems->isNotEmpty())
                                <span class="pp-badge pp-badge-teal">{{ $problems->count() }}</span>
                            @endif
                        </h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon"></i>
                    </div>
                    <div id="enc-problems-{{ $appointment->id }}" class="pp-encounter-body" style="display:none">
                        @forelse($problems as $problem)
                            <p style="font-size:0.82rem;margin-bottom:4px">{{ $loop->iteration }}. {{ $problem->title }}</p>
                        @empty
                            <div class="pp-empty py-2"><i class="ph ph-circle-dashed"></i>{{ __('frontend.no_records_found') ?? 'No problems found' }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Observations --}}
                <div class="pp-encounter-section" id="enc-wrap-obs-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-obs-{{ $appointment->id }}', 'enc-wrap-obs-{{ $appointment->id }}')"
                         role="button" aria-expanded="false">
                        <h6 class="pp-encounter-toggle-title">
                            <i class="ph ph-eye"></i>{{ __('frontend.observations') ?? 'Observations' }}
                            @if($observations->isNotEmpty())
                                <span class="pp-badge pp-badge-teal">{{ $observations->count() }}</span>
                            @endif
                        </h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon"></i>
                    </div>
                    <div id="enc-obs-{{ $appointment->id }}" class="pp-encounter-body" style="display:none">
                        @forelse($observations as $obs)
                            <p style="font-size:0.82rem;margin-bottom:4px">{{ $loop->iteration }}. {{ $obs->title }}</p>
                        @empty
                            <div class="pp-empty py-2"><i class="ph ph-circle-dashed"></i>{{ __('frontend.no_records_found') ?? 'No observations found' }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Notes --}}
                <div class="pp-encounter-section" id="enc-wrap-notes-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-notes-{{ $appointment->id }}', 'enc-wrap-notes-{{ $appointment->id }}')"
                         role="button" aria-expanded="false">
                        <h6 class="pp-encounter-toggle-title">
                            <i class="ph ph-note"></i>{{ __('frontend.notes') ?? 'Notes' }}
                            @if($notes->isNotEmpty())
                                <span class="pp-badge pp-badge-teal">{{ $notes->count() }}</span>
                            @endif
                        </h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon"></i>
                    </div>
                    <div id="enc-notes-{{ $appointment->id }}" class="pp-encounter-body" style="display:none">
                        @forelse($notes as $note)
                            <p style="font-size:0.82rem;margin-bottom:4px">{{ $loop->iteration }}. {{ $note->title }}</p>
                        @empty
                            <div class="pp-empty py-2"><i class="ph ph-circle-dashed"></i>{{ __('frontend.no_records_found') ?? 'No notes found' }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Prescriptions --}}
                @if($prescriptions->isNotEmpty())
                <div class="pp-encounter-section" id="enc-wrap-rx-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-rx-{{ $appointment->id }}', 'enc-wrap-rx-{{ $appointment->id }}')"
                         role="button" aria-expanded="true">
                        <h6 class="pp-encounter-toggle-title">
                            <i class="ph ph-pill"></i>{{ __('frontend.prescription') ?? 'Prescriptions' }}
                            <span class="pp-badge pp-badge-teal">{{ $prescriptions->count() }}</span>
                        </h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon" style="transform:rotate(180deg)"></i>
                    </div>
                    <div id="enc-rx-{{ $appointment->id }}" class="pp-encounter-body">
                        @foreach($prescriptions as $rx)
                        <div class="pp-rx-item">
                            <p class="pp-rx-name">{{ $rx->name }}</p>
                            @if($rx->instruction)
                                <p style="font-size:0.78rem;color:var(--pp-muted);margin:0 0 8px">{{ $rx->instruction }}</p>
                            @endif
                            <div class="pp-rx-meta">
                                <span class="pp-rx-chip"><i class="ph ph-clock me-1"></i>{{ $rx->frequency }}</span>
                                <span class="pp-rx-chip"><i class="ph ph-calendar-blank me-1"></i>{{ $rx->duration }} {{ __('frontend.days') ?? 'Days' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div class="pp-encounter-section" id="enc-wrap-rx-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-rx-{{ $appointment->id }}', 'enc-wrap-rx-{{ $appointment->id }}')"
                         role="button">
                        <h6 class="pp-encounter-toggle-title"><i class="ph ph-pill"></i>{{ __('frontend.prescription') ?? 'Prescriptions' }}</h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon"></i>
                    </div>
                    <div id="enc-rx-{{ $appointment->id }}" class="pp-encounter-body" style="display:none">
                        <div class="pp-empty py-2"><i class="ph ph-circle-dashed"></i>{{ __('frontend.no_records_found') ?? 'No prescriptions found' }}</div>
                    </div>
                </div>
                @endif

                {{-- SOAP --}}
                <div class="pp-encounter-section" id="enc-wrap-soap-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-soap-{{ $appointment->id }}', 'enc-wrap-soap-{{ $appointment->id }}')"
                         role="button">
                        <h6 class="pp-encounter-toggle-title"><i class="ph ph-clipboard-text"></i>{{ __('frontend.soap') }}</h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon"></i>
                    </div>
                    <div id="enc-soap-{{ $appointment->id }}" class="pp-encounter-body" style="display:none">
                        @if($soap)
                            <div class="row g-3" style="font-family:var(--pp-font);font-size:0.82rem">
                                @foreach(['subjective' => __('frontend.subjective'),'objective' => __('frontend.objective'),'assessment' => __('frontend.assessment'),'plan' => __('frontend.plan')] as $key => $label)
                                @if($soap->$key)
                                <div class="col-sm-6">
                                    <p style="font-size:0.7rem;font-weight:700;color:var(--pp-muted);text-transform:uppercase;margin:0 0 4px;letter-spacing:0.05em">{{ $label }}</p>
                                    <p style="margin:0">{{ $soap->$key }}</p>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        @else
                            <div class="pp-empty py-2"><i class="ph ph-circle-dashed"></i>{{ __('frontend.no_soap_found') }}</div>
                        @endif
                    </div>
                </div>

                {{-- Medical Reports / Body Charts --}}
                @if($appointment->media->isNotEmpty() || ($medical_report && $medical_report->file_url) || $bodychart->isNotEmpty())
                <div class="pp-encounter-section" id="enc-wrap-files-{{ $appointment->id }}">
                    <div class="pp-encounter-toggle"
                         onclick="toggleEncSection('enc-files-{{ $appointment->id }}', 'enc-wrap-files-{{ $appointment->id }}')"
                         role="button">
                        <h6 class="pp-encounter-toggle-title"><i class="ph ph-files"></i>{{ __('frontend.files') ?? 'Files & Reports' }}</h6>
                        <i class="ph ph-caret-down pp-encounter-toggle-icon"></i>
                    </div>
                    <div id="enc-files-{{ $appointment->id }}" class="pp-encounter-body" style="display:none">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($appointment->media as $media)
                                <a href="{{ asset($media->getUrl()) }}" download
                                   class="pp-btn pp-btn-outline">
                                    <i class="ph ph-download-simple"></i>{{ __('frontend.download_report') ?? 'Download Report' }}
                                </a>
                            @endforeach
                            @if($medical_report && $medical_report->file_url)
                                <a href="{{ asset($medical_report->file_url) }}" download
                                   class="pp-btn pp-btn-outline">
                                    <i class="ph ph-download-simple"></i>{{ __('frontend.download_report') ?? 'Download Report' }}
                                </a>
                            @endif
                            @foreach($bodychart as $chart)
                                @foreach($chart->media as $media)
                                    <div style="text-align:center">
                                        <img src="{{ asset($media->getUrl()) }}" alt="{{ $media->name }}"
                                             style="width:80px;height:80px;object-fit:cover;border-radius:var(--pp-r-sm);border:1px solid var(--pp-border);display:block;margin-bottom:4px">
                                        <a href="{{ asset($media->getUrl()) }}" download class="pp-btn pp-btn-ghost" style="font-size:0.7rem">
                                            <i class="ph ph-download-simple"></i>
                                        </a>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

            </div>{{-- end modal-body --}}
        </div>
    </div>
</div>
