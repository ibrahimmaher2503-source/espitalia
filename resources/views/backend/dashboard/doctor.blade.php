@extends('backend.layouts.app', ['isBanner' => false])

@section('title') {{ __('dashboard.title') }} @endsection

@push('after-styles')
<link rel="stylesheet" href="{{ asset('css/doctor-portal.css') }}">
@endpush

@section('content')
<div class="dp-page">

    {{-- Page Header --}}
    <div class="dp-header">
        <div class="dp-breadcrumb">
            <span>{{ __('menu.main') }}</span>
            <i class="ph ph-caret-right"></i>
            <span class="dp-bc-active">{{ __('dashboard.title') }}</span>
        </div>
        <div class="dp-greeting-row">
            <div>
                <h1 class="dp-greeting-title">
                    {{ greeting() }}, <em class="text-capitalize">{{ $current_user }}</em>
                </h1>
                <p class="dp-greeting-sub">
                    <i class="ph ph-calendar-blank"></i>
                    {{ now()->format('l, F j, Y') }}
                </p>
            </div>
            <div class="dp-date-badge">
                <span class="dp-date-badge__day">{{ now()->format('d') }}</span>
                <span class="dp-date-badge__month">{{ now()->format('M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3 dp-stat-anim" style="--dp-delay:0ms">
            <a href="{{ route('backend.appointments.index') }}" class="dp-stat-card dp-stat-blue">
                <div class="dp-stat__icon"><i class="ph ph-calendar-check"></i></div>
                <div class="dp-stat__body">
                    <p class="dp-stat__label">{{ __('appointment.total_number_appointment') }}</p>
                    <p class="dp-stat__value">{{ $data['total_appointments'] }}</p>
                </div>
                <i class="ph ph-arrow-right dp-stat__arrow"></i>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3 dp-stat-anim" style="--dp-delay:75ms">
            <a href="{{ route('backend.customers.index') }}" class="dp-stat-card dp-stat-green">
                <div class="dp-stat__icon"><i class="ph ph-users-three"></i></div>
                <div class="dp-stat__body">
                    <p class="dp-stat__label">{{ __('appointment.total_number_of_patients') }}</p>
                    <p class="dp-stat__value">{{ $data['total_patient'] }}</p>
                </div>
                <i class="ph ph-arrow-right dp-stat__arrow"></i>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3 dp-stat-anim" style="--dp-delay:150ms">
            <a href="{{ route('backend.services.index') }}" class="dp-stat-card dp-stat-teal">
                <div class="dp-stat__icon"><i class="ph ph-first-aid-kit"></i></div>
                <div class="dp-stat__body">
                    <p class="dp-stat__label">{{ __('appointment.total_number_of_services') }}</p>
                    <p class="dp-stat__value">{{ $data['total_service_count'] }}</p>
                </div>
                <i class="ph ph-arrow-right dp-stat__arrow"></i>
            </a>
        </div>
        <div class="col-sm-6 col-xl-3 dp-stat-anim" style="--dp-delay:225ms">
            <div class="dp-stat-card dp-stat-amber">
                <div class="dp-stat__icon"><i class="ph ph-currency-circle-dollar"></i></div>
                <div class="dp-stat__body">
                    <p class="dp-stat__label">{{ __('appointment.total_earning') }}</p>
                    <p class="dp-stat__value">{{ Currency::format($data['total_earning']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Calendar + Today's Schedule --}}
    <div class="row g-3">

        {{-- Appointment Calendar --}}
        <div class="col-lg-8">
            <div class="dp-card h-100">
                <div class="dp-card-header">
                    <h6 class="dp-card-title">
                        <i class="ph ph-calendar-dots"></i>
                        {{ __('dashboard.lbl_upcoming_appointment') ?? 'Appointment Calendar' }}
                    </h6>
                    <div id="dp-today-count-badge" class="dp-badge dp-badge-teal" style="display:none">
                        <i class="ph ph-circle-wavy-check"></i>
                        <span id="dp-today-count">0</span>&nbsp;today
                    </div>
                </div>
                <div class="dp-card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        {{-- Today's Schedule --}}
        <div class="col-lg-4">
            <div class="dp-card h-100">
                <div class="dp-card-header">
                    <h6 class="dp-card-title">
                        <i class="ph ph-clock-afternoon"></i>
                        {{ __('dashboard.today_schedule') ?? "Today's Schedule" }}
                    </h6>
                    <span class="dp-date-chip">{{ now()->format('M j') }}</span>
                </div>
                <div class="dp-today-body">
                    {{-- Loading skeleton --}}
                    <div id="dp-skel" class="dp-skeleton-list">
                        @for ($i = 0; $i < 5; $i++)
                        <div class="dp-skeleton-item">
                            <div class="dp-sk dp-sk-time"></div>
                            <div class="dp-sk-content">
                                <div class="dp-sk dp-sk-line dp-sk-w70"></div>
                                <div class="dp-sk dp-sk-line dp-sk-w45"></div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    {{-- Appointment list (populated by JS) --}}
                    <div id="dp-appt-list" class="dp-appt-list" style="display:none"></div>

                    {{-- Empty state --}}
                    <div id="dp-appt-empty" class="dp-empty" style="display:none">
                        <i class="ph ph-calendar-x"></i>
                        <p>No appointments<br>scheduled for today</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

@push('after-scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
(function () {
    'use strict';

    var TODAY_ISO = '{{ now()->format("Y-m-d") }}';

    /* Format ISO string → "9:30 AM" treating as UTC */
    function fmtTime(iso) {
        var d = new Date(iso);
        var h = d.getUTCHours();
        var m = d.getUTCMinutes();
        var suffix = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + (m ? ':' + String(m).padStart(2, '0') : '') + '\u202f' + suffix;
    }

    /* Render today's appointments into the side panel */
    function renderTodayPanel(events) {
        var skel  = document.getElementById('dp-skel');
        var list  = document.getElementById('dp-appt-list');
        var empty = document.getElementById('dp-appt-empty');
        var badge = document.getElementById('dp-today-count-badge');
        var count = document.getElementById('dp-today-count');

        skel.style.display = 'none';

        var todayEvents = events
            .filter(function (e) { return e.start && e.start.slice(0, 10) === TODAY_ISO; })
            .sort(function (a, b) { return new Date(a.start) - new Date(b.start); });

        if (!todayEvents.length) {
            empty.style.display = 'flex';
            return;
        }

        badge.style.display = 'inline-flex';
        count.textContent   = todayEvents.length;

        var html = '';
        todayEvents.forEach(function (ev, idx) {
            var time    = fmtTime(ev.start);
            var service = ev.title || 'Appointment';
            var patient = (ev.extendedProps && ev.extendedProps.patient) || '';
            var href    = '{{ URL::to("app/appointments/clinicAppointmentDetail") }}/' + ev.id;

            html += '<a href="' + href + '" class="dp-appt-item" style="--dp-idx:' + idx + '">' +
                '<div class="dp-appt-time">' + time + '</div>' +
                '<div class="dp-appt-dot"></div>' +
                '<div class="dp-appt-info">' +
                    '<p class="dp-appt-service">' + $('<span>').text(service).html() + '</p>' +
                    (patient ? '<p class="dp-appt-patient">' + $('<span>').text(patient).html() + '</p>' : '') +
                '</div>' +
                '<i class="ph ph-arrow-right dp-appt-go"></i>' +
            '</a>';
        });

        list.innerHTML = html;
        list.style.display = 'flex';
    }

    /* Initialise FullCalendar */
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');

        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,timeGridWeek'
            },
            height:   'auto',
            timeZone: 'UTC',

            events: function (info, successCb, failureCb) {
                $.ajax({
                    url:      '{{ route("backend.get-appointments") }}',
                    dataType: 'json',
                    success: function (res) {
                        if (res.status && Array.isArray(res.data)) {
                            var events = res.data.map(function (appt) {
                                return {
                                    id:              appt.id,
                                    title:           appt.service_name || '—',
                                    start:           appt.start_date_time,
                                    backgroundColor: '#0B6B65',
                                    borderColor:     '#0B6B65',
                                    extendedProps: {
                                        patient: appt.user ? appt.user.first_name : ''
                                    }
                                };
                            });
                            renderTodayPanel(events);
                            successCb(events);
                        } else {
                            renderTodayPanel([]);
                            failureCb('Unexpected format');
                        }
                    },
                    error: function () {
                        renderTodayPanel([]);
                        failureCb('Request failed');
                    }
                });
            },

            eventDidMount: function (info) {
                var patient = info.event.extendedProps.patient || '';
                var label   = patient
                    ? patient + ' \u2014 ' + info.event.title
                    : info.event.title;

                if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                    new bootstrap.Tooltip(info.el, {
                        title:     label,
                        placement: 'top',
                        trigger:   'hover',
                        container: 'body'
                    });
                }
            },

            eventTimeFormat: {
                hour:     'numeric',
                minute:   '2-digit',
                meridiem: 'short'
            },

            eventClick: function (info) {
                window.location.href =
                    '{{ URL::to("app/appointments/clinicAppointmentDetail") }}/' + info.event.id;
            }
        });

        calendar.render();
    });
}());
</script>
@endpush
