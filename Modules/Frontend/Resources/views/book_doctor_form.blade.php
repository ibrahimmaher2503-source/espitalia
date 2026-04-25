@extends('frontend::layouts.master')
@section('title', __('frontend.book_doctor'))

@section('content')
@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">

                {{-- ── Page header ── --}}
                <div class="pp-page-header">
                    <div>
                        <h1 class="pp-page-title">{{ __('frontend.book_doctor') }}</h1>
                        <p style="color:var(--pp-muted);font-size:0.875rem;margin:0;font-family:var(--pp-font)">
                            {{ $doctor->user->first_name }} {{ $doctor->user->last_name }}
                        </p>
                    </div>
                </div>

                {{-- ── Step indicator ── --}}
                <div class="pp-booking-steps" id="bookingSteps">
                    <div class="pp-booking-step active" data-step="0">
                        <span class="pp-booking-step-num">1</span>
                        <span class="pp-booking-step-label">{{ __('frontend.lbl_select_independent_service') }}</span>
                    </div>
                    <div class="pp-booking-step-line"></div>
                    <div class="pp-booking-step" data-step="1">
                        <span class="pp-booking-step-num">2</span>
                        <span class="pp-booking-step-label">{{ __('frontend.lbl_select_date_time') }}</span>
                    </div>
                    <div class="pp-booking-step-line"></div>
                    <div class="pp-booking-step" data-step="2">
                        <span class="pp-booking-step-num">3</span>
                        <span class="pp-booking-step-label">{{ __('frontend.lbl_review_and_pay') }}</span>
                    </div>
                </div>

                {{-- ── Step 0: Service Selection ── --}}
                <div id="step-0" class="booking-step">
                    @if($services->count() > 0)
                        <div class="row g-3">
                            @foreach($services as $service)
                            <div class="col-md-6">
                                <div class="pp-service-select-card"
                                     data-service-id="{{ $service->id }}"
                                     data-service-name="{{ $service->name }}"
                                     data-service-charges="{{ $service->charges }}"
                                     data-service-duration="{{ $service->duration_min }}"
                                     role="button"
                                     tabindex="0"
                                     aria-label="{{ $service->name }}">
                                    <p class="pp-service-select-card-name">{{ $service->name }}</p>
                                    @if($service->description)
                                        <p class="pp-service-select-card-desc">{{ $service->description }}</p>
                                    @endif
                                    <div class="pp-service-select-card-meta">
                                        <span>
                                            <i class="ph ph-clock" aria-hidden="true"></i>
                                            {{ $service->duration_min }} {{ __('frontend.lbl_minutes') }}
                                        </span>
                                        <span class="pp-service-select-card-price">
                                            {{ number_format($service->charges, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="pp-card text-center" style="padding:3rem 1.5rem">
                            <div class="pp-empty">
                                <i class="ph ph-calendar-x" aria-hidden="true"></i>
                                <p>{{ __('frontend.lbl_no_independent_services') }}</p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ── Step 1: Date & Time ── --}}
                <div id="step-1" class="booking-step" style="display:none">
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-calendar" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('frontend.choose_date_time') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="pp-form-group mb-4">
                                <label class="pp-label" for="appointmentDate">{{ __('messages.lbl_date') }}</label>
                                <div class="pp-input-group">
                                    <input type="date" id="appointmentDate" class="pp-input" min="{{ date('Y-m-d') }}">
                                    <span class="pp-input-icon"><i class="ph ph-calendar"></i></span>
                                </div>
                            </div>

                            <div id="timeSlotsContainer" style="display:none">
                                <label class="pp-label">{{ __('messages.lbl_time') }}</label>
                                <div id="timeSlots" class="d-flex flex-wrap gap-2 mt-2"></div>
                            </div>
                        </div>
                    </div>

                    <button id="btnToStep2" class="pp-btn pp-btn-teal" disabled style="width:100%;justify-content:center">
                        <i class="ph ph-arrow-right"></i>{{ __('messages.next') }}
                    </button>
                </div>

                {{-- ── Step 2: Review & Confirm ── --}}
                <div id="step-2" class="booking-step" style="display:none">
                    <div class="pp-detail-section mb-4">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-receipt" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('messages.summary') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('messages.lbl_doctor') }}</span>
                                <span class="pp-summary-value">{{ $doctor->user->first_name }} {{ $doctor->user->last_name }}</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('messages.lbl_service') }}</span>
                                <span class="pp-summary-value" id="summaryService">—</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('messages.lbl_date') }}</span>
                                <span class="pp-summary-value" id="summaryDate">—</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('messages.lbl_time') }}</span>
                                <span class="pp-summary-value" id="summaryTime">—</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label pp-summary-total">{{ __('messages.lbl_amount') }}</span>
                                <span class="pp-summary-value pp-summary-total" id="summaryAmount">—</span>
                            </div>
                        </div>
                    </div>

                    <div class="pp-form-group mb-4">
                        <label class="pp-label" for="paymentMethod">{{ __('messages.lbl_payment_method') }}</label>
                        <select id="paymentMethod" class="pp-select">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button id="btnConfirmBooking" class="pp-btn pp-btn-teal" style="width:100%;justify-content:center;font-size:1rem;padding:0.75rem">
                        <i class="ph ph-check-circle"></i>{{ __('frontend.lbl_book_directly') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('after-scripts')
<script>
(function () {
    'use strict';

    var selectedService = null;
    var selectedDate    = null;
    var selectedTime    = null;
    var doctorId        = {{ $doctor->id }};

    var steps      = document.querySelectorAll('#bookingSteps .pp-booking-step');
    var stepLines  = document.querySelectorAll('#bookingSteps .pp-booking-step-line');

    function goToStep(n) {
        steps.forEach(function (s, i) {
            var step = parseInt(s.dataset.step);
            s.classList.remove('active', 'done');
            if (step === n) s.classList.add('active');
            if (step < n)  s.classList.add('done');
        });
        document.querySelectorAll('.booking-step').forEach(function (el) { el.style.display = 'none'; });
        document.getElementById('step-' + n).style.display = '';
    }

    /* ── Service selection ── */
    document.querySelectorAll('.pp-service-select-card').forEach(function (card) {
        function select() {
            document.querySelectorAll('.pp-service-select-card').forEach(function (c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            selectedService = {
                id:       card.dataset.serviceId,
                name:     card.dataset.serviceName,
                charges:  card.dataset.serviceCharges,
                duration: card.dataset.serviceDuration,
            };
            goToStep(1);
        }
        card.addEventListener('click', select);
        card.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); select(); } });
    });

    /* ── Date change → fetch slots ── */
    document.getElementById('appointmentDate').addEventListener('change', function () {
        selectedDate = this.value;
        selectedTime = null;
        document.getElementById('btnToStep2').disabled = true;
        document.getElementById('timeSlots').innerHTML = '';
        if (!selectedDate) return;

        $.ajax({
            url:  '{{ route("book-doctor.slots") }}',
            method: 'POST',
            data: {
                _token:                  '{{ csrf_token() }}',
                appointment_date:        selectedDate,
                doctor_id:               doctorId,
                independent_service_id:  selectedService.id,
            },
            success: function (res) {
                var container = document.getElementById('timeSlotsContainer');
                var slotsEl   = document.getElementById('timeSlots');
                container.style.display = '';
                if (res.status && res.data.length > 0) {
                    slotsEl.innerHTML = '';
                    res.data.forEach(function (slot) {
                        var btn = document.createElement('button');
                        btn.type      = 'button';
                        btn.className = 'pp-time-slot';
                        btn.dataset.time = slot.value;
                        btn.textContent  = slot.label;
                        btn.addEventListener('click', function () {
                            document.querySelectorAll('.pp-time-slot').forEach(function (b) { b.classList.remove('selected'); });
                            btn.classList.add('selected');
                            selectedTime = slot.value;
                            document.getElementById('btnToStep2').disabled = false;
                        });
                        slotsEl.appendChild(btn);
                    });
                } else {
                    slotsEl.innerHTML = '<p style="color:var(--pp-muted);font-size:0.82rem;font-family:var(--pp-font)">{{ __("frontend.lbl_no_slots_available") }}</p>';
                }
            }
        });
    });

    /* ── Next: go to step 2 ── */
    document.getElementById('btnToStep2').addEventListener('click', function () {
        document.getElementById('summaryService').textContent = selectedService.name;
        document.getElementById('summaryDate').textContent    = selectedDate;
        document.getElementById('summaryTime').textContent    = selectedTime;
        document.getElementById('summaryAmount').textContent  = parseFloat(selectedService.charges).toFixed(2);
        goToStep(2);
    });

    /* ── Confirm booking ── */
    document.getElementById('btnConfirmBooking').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("messages.processing") }}...';

        $.ajax({
            url:  '{{ route("book-doctor.save") }}',
            method: 'POST',
            data: {
                _token:                  '{{ csrf_token() }}',
                doctor_id:               doctorId,
                independent_service_id:  selectedService.id,
                appointment_date:        selectedDate,
                appointment_time:        selectedTime,
                transaction_type:        document.getElementById('paymentMethod').value,
            },
            success: function (res) {
                if (res.status) {
                    window.location.href = '{{ route("appointment-list") }}';
                } else {
                    window.errorSnackbar(res.message || '{{ __("messages.something_went_wrong") }}');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-check-circle"></i>{{ __("frontend.lbl_book_directly") }}';
                }
            },
            error: function (xhr) {
                window.errorSnackbar((xhr.responseJSON && xhr.responseJSON.message) || '{{ __("messages.something_went_wrong") }}');
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-check-circle"></i>{{ __("frontend.lbl_book_directly") }}';
            }
        });
    });
}());
</script>
@endpush
