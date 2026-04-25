@extends('frontend::layouts.master')
@section('title', __('call_booking.book_a_call'))

@section('content')
@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">

                {{-- ── Page header ── --}}
                <div class="pp-page-header">
                    <div>
                        <h1 class="pp-page-title">{{ __('call_booking.book_a_call') }}</h1>
                        <p style="color:var(--pp-muted);font-size:0.875rem;margin:0;font-family:var(--pp-font)">
                            {{ $doctor->user->first_name }} {{ $doctor->user->last_name }}
                        </p>
                    </div>
                </div>

                {{-- ── Step indicator ── --}}
                <div class="pp-booking-steps" id="bookingSteps">
                    <div class="pp-booking-step active" data-step="0">
                        <span class="pp-booking-step-num">1</span>
                        <span class="pp-booking-step-label">{{ __('call_booking.select_call_service') }}</span>
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

                    {{-- Loading shimmer --}}
                    <div id="servicesLoading" class="d-flex gap-3 flex-wrap">
                        @for($i = 0; $i < 4; $i++)
                            <div class="pp-order-shimmer" style="flex:1;min-width:200px">
                                <div class="pp-sk pp-sk-h16 pp-sk-w60 mb-2"></div>
                                <div class="pp-sk pp-sk-h10 pp-sk-w80 mb-2"></div>
                                <div class="pp-sk pp-sk-h10 pp-sk-w40"></div>
                            </div>
                        @endfor
                    </div>

                    <div id="servicesContainer" style="display:none">
                        {{-- Video calls --}}
                        <div id="videoServicesSection" style="display:none">
                            <p class="pp-section-title mb-3">
                                <i class="ph ph-video-camera" style="color:var(--pp-teal)" aria-hidden="true"></i>
                                {{ __('call_booking.video_calls') }}
                            </p>
                            <div id="videoServices" class="row g-3 mb-4"></div>
                        </div>
                        {{-- Phone calls --}}
                        <div id="phoneServicesSection" style="display:none">
                            <p class="pp-section-title mb-3">
                                <i class="ph ph-phone" style="color:var(--pp-teal)" aria-hidden="true"></i>
                                {{ __('call_booking.phone_calls') }}
                            </p>
                            <div id="phoneServices" class="row g-3 mb-4"></div>
                        </div>
                        {{-- No services --}}
                        <div id="noServicesAlert" style="display:none">
                            <div class="pp-card text-center" style="padding:3rem 1.5rem">
                                <div class="pp-empty">
                                    <i class="ph ph-phone-slash" aria-hidden="true"></i>
                                    <p>{{ __('call_booking.no_call_services') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                <span class="pp-summary-label">{{ __('call_booking.call_type') }}</span>
                                <span class="pp-summary-value" id="summaryCallType">—</span>
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

                    <div id="callInfoAlert" class="pp-info-block mb-3" style="display:none">
                        <i class="ph ph-info" aria-hidden="true"></i>
                        <span id="callInfoText"></span>
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
                        <i class="ph ph-phone-call"></i>{{ __('call_booking.book_call') }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ── Booking confirmed modal ── --}}
<div class="modal fade pp-modal" id="meetingLinkModal" tabindex="-1"
     aria-labelledby="meetingLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="meetingLinkModalLabel">{{ __('call_booking.booking_confirmed') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('frontend.close') }}"></button>
            </div>
            <div class="modal-body text-center" style="padding:2rem">
                <i class="ph-fill ph-check-circle"
                   style="font-size:3rem;color:var(--pp-confirmed)" aria-hidden="true"></i>
                <p class="mt-3" id="modalMessage" style="font-family:var(--pp-font);color:var(--pp-text-2)"></p>
                <a href="#" id="modalMeetingLink"
                   class="pp-btn pp-btn-teal mt-2"
                   target="_blank"
                   style="display:none;justify-content:center">
                    <i class="ph ph-video-camera"></i>{{ __('call_booking.join_call') }}
                </a>
            </div>
            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
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
    var steps           = document.querySelectorAll('#bookingSteps .pp-booking-step');

    function goToStep(n) {
        steps.forEach(function (s) {
            var step = parseInt(s.dataset.step);
            s.classList.remove('active', 'done');
            if (step === n) s.classList.add('active');
            if (step < n)  s.classList.add('done');
        });
        document.querySelectorAll('.booking-step').forEach(function (el) { el.style.display = 'none'; });
        document.getElementById('step-' + n).style.display = '';
    }

    function buildServiceCard(service, callType) {
        var iconClass  = callType === 'video' ? 'ph-video-camera' : 'ph-phone';
        var typeLabel  = callType === 'video' ? '{{ __("call_booking.video") }}' : '{{ __("call_booking.phone") }}';
        var div        = document.createElement('div');
        div.className  = 'col-md-6';
        div.innerHTML  =
            '<div class="pp-service-select-card" role="button" tabindex="0"' +
            '     data-service-id="' + service.id + '"' +
            '     data-service-name="' + service.name + '"' +
            '     data-service-charges="' + service.charges + '"' +
            '     data-service-duration="' + service.duration_min + '"' +
            '     data-call-type="' + callType + '">' +
            '  <div class="d-flex align-items-center justify-content-between gap-2 mb-2">' +
            '    <p class="pp-service-select-card-name mb-0"><i class="ph ' + iconClass + ' me-1" aria-hidden="true"></i>' + service.name + '</p>' +
            '    <span class="pp-badge">' + typeLabel + '</span>' +
            '  </div>' +
            '  <p class="pp-service-select-card-desc">' + (service.description || '') + '</p>' +
            '  <div class="pp-service-select-card-meta">' +
            '    <span><i class="ph ph-clock" aria-hidden="true"></i> ' + service.duration_min + ' {{ __("call_booking.lbl_minutes") }}</span>' +
            '    <span class="pp-service-select-card-price">' + parseFloat(service.charges).toFixed(2) + '</span>' +
            '  </div>' +
            '</div>';
        div.querySelector('.pp-service-select-card').addEventListener('click', selectService);
        div.querySelector('.pp-service-select-card').addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); selectService.call(this); }
        });
        return div;
    }

    function selectService() {
        document.querySelectorAll('.pp-service-select-card').forEach(function (c) { c.classList.remove('selected'); });
        this.classList.add('selected');
        selectedService = {
            id:       this.dataset.serviceId,
            name:     this.dataset.serviceName,
            charges:  this.dataset.serviceCharges,
            duration: this.dataset.serviceDuration,
            callType: this.dataset.callType,
        };
        goToStep(1);
    }

    /* ── Load call services ── */
    $.ajax({
        url: '{{ route("book-call.services", $doctor->id) }}',
        method: 'GET',
        success: function (res) {
            document.getElementById('servicesLoading').style.display = 'none';
            document.getElementById('servicesContainer').style.display = '';

            if (!res.data || res.data.length === 0) {
                document.getElementById('noServicesAlert').style.display = '';
                return;
            }

            var videoServices = res.data.filter(function (s) { return s.call_type === 'video'; });
            var phoneServices = res.data.filter(function (s) { return s.call_type === 'phone'; });

            if (videoServices.length > 0) {
                document.getElementById('videoServicesSection').style.display = '';
                var vContainer = document.getElementById('videoServices');
                videoServices.forEach(function (s) { vContainer.appendChild(buildServiceCard(s, 'video')); });
            }
            if (phoneServices.length > 0) {
                document.getElementById('phoneServicesSection').style.display = '';
                var pContainer = document.getElementById('phoneServices');
                phoneServices.forEach(function (s) { pContainer.appendChild(buildServiceCard(s, 'phone')); });
            }
            if (videoServices.length === 0 && phoneServices.length === 0) {
                document.getElementById('noServicesAlert').style.display = '';
            }
        },
        error: function () {
            document.getElementById('servicesLoading').style.display = 'none';
            document.getElementById('servicesContainer').style.display = '';
            document.getElementById('noServicesAlert').style.display = '';
        }
    });

    /* ── Date → slots ── */
    document.getElementById('appointmentDate').addEventListener('change', function () {
        selectedDate = this.value;
        selectedTime = null;
        document.getElementById('btnToStep2').disabled = true;
        document.getElementById('timeSlots').innerHTML = '';
        if (!selectedDate) return;

        $.ajax({
            url: '{{ route("book-call.slots") }}',
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', appointment_date: selectedDate, doctor_id: doctorId, call_service_id: selectedService.id },
            success: function (res) {
                var container = document.getElementById('timeSlotsContainer');
                var slotsEl   = document.getElementById('timeSlots');
                container.style.display = '';
                if (res.status && res.data.length > 0) {
                    slotsEl.innerHTML = '';
                    res.data.forEach(function (slot) {
                        var btn = document.createElement('button');
                        btn.type = 'button'; btn.className = 'pp-time-slot'; btn.dataset.time = slot.value; btn.textContent = slot.label;
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

    /* ── Step 2 ── */
    document.getElementById('btnToStep2').addEventListener('click', function () {
        document.getElementById('summaryService').textContent   = selectedService.name;
        document.getElementById('summaryCallType').textContent  = selectedService.callType === 'video' ? '{{ __("call_booking.video") }}' : '{{ __("call_booking.phone") }}';
        document.getElementById('summaryDate').textContent      = selectedDate;
        document.getElementById('summaryTime').textContent      = selectedTime;
        document.getElementById('summaryAmount').textContent    = parseFloat(selectedService.charges).toFixed(2);

        var alertEl = document.getElementById('callInfoAlert');
        var alertText = document.getElementById('callInfoText');
        if (selectedService.callType === 'video') {
            alertText.textContent = '{{ __("call_booking.jitsi_link_info") }}';
            alertEl.style.display = '';
        } else {
            alertText.textContent = '{{ __("call_booking.phone_call_info") }}';
            alertEl.style.display = '';
        }
        goToStep(2);
    });

    /* ── Confirm ── */
    document.getElementById('btnConfirmBooking').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("messages.processing") }}...';

        $.ajax({
            url: '{{ route("book-call.save") }}',
            method: 'POST',
            data: {
                _token:           '{{ csrf_token() }}',
                doctor_id:        doctorId,
                call_service_id:  selectedService.id,
                appointment_date: selectedDate,
                appointment_time: selectedTime,
                transaction_type: document.getElementById('paymentMethod').value,
            },
            success: function (res) {
                if (res.status) {
                    document.getElementById('modalMessage').textContent = res.message || '{{ __("call_booking.booking_confirmed") }}';
                    if (res.meeting_link) {
                        var link = document.getElementById('modalMeetingLink');
                        link.href = res.meeting_link;
                        link.style.display = '';
                    }
                    var modal = new bootstrap.Modal(document.getElementById('meetingLinkModal'));
                    modal.show();
                    document.getElementById('meetingLinkModal').addEventListener('hidden.bs.modal', function () {
                        window.location.href = '{{ route("appointment-list") }}';
                    });
                    setTimeout(function () { window.location.href = '{{ route("appointment-list") }}'; }, 10000);
                } else {
                    window.errorSnackbar(res.message || '{{ __("messages.something_went_wrong") }}');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ph ph-phone-call"></i>{{ __("call_booking.book_call") }}';
                }
            },
            error: function (xhr) {
                window.errorSnackbar((xhr.responseJSON && xhr.responseJSON.message) || '{{ __("messages.something_went_wrong") }}');
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-phone-call"></i>{{ __("call_booking.book_call") }}';
            }
        });
    });
}());
</script>
@endpush
