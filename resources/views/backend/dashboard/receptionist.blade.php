@extends('backend.layouts.app', ['isBanner' => false])

@section('title')
{{ 'Dashboard' }}
@endsection

@section('content')
<div class="dashboard-page">
{{-- Breadcrumb + Greeting --}}
<div class="dash-greeting mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <span class="text-muted small">{{ __('menu.main') }}</span>
        <i class="ph ph-caret-double-right text-muted" style="font-size: 0.7rem;"></i>
        <span class="fw-medium small">{{ __('dashboard.title') }}</span>
    </div>
    <h1 class="dash-greeting-title mb-1">
        <span class="fw-light">{{greeting()}}</span>
        <span class="text-capitalize">{{$current_user}}</span>
    </h1>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('backend.appointments.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #6366f1 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        <i class="ph ph-calendar-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('appointment.total_number_appointment')}}</p>
                        <h3 class="mb-0 fw-bold">{{ $data['total_appointments']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('backend.customers.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('appointment.total_number_of_patients')}}</p>
                        <h3 class="mb-0 fw-bold">{{ $data['total_patient']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4">
        <a href="{{ route('backend.doctor.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="ph ph-stethoscope"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('appointment.total_number_of_doctors')}}</p>
                        <h3 class="mb-0 fw-bold">{{ $data['total_assign_doctor'] }}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Calendar --}}
<div class="row g-3">
    <div class="col-md-12">
        <div class="card dash-card border-0">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0 fw-semibold">
                    <i class="ph ph-calendar me-2 text-primary"></i>{{ __('dashboard.lbl_upcoming_appointment') ?? 'Appointment Calendar' }}
                </h5>
            </div>
            <div class="card-body">
                <div id='calendar'></div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@push('after-styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-modern.css') }}">
<style>
    .dash-greeting-title {
        font-size: 1.75rem;
        color: #1f2937;
    }

    /* Stat Cards */
    .dash-stat-card {
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .dash-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    .dash-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    /* General Cards */
    .dash-card {
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }

    /* FullCalendar refinements */
    .fc .fc-toolbar-title {
        font-size: 1.15rem;
        font-weight: 600;
    }
    .fc .fc-button-primary {
        border-radius: 6px;
        font-size: 0.85rem;
        padding: 0.35rem 0.75rem;
    }
    .fc .fc-daygrid-day-number {
        font-size: 0.85rem;
    }
</style>
@endpush

@push('after-scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            themeSystem: 'bootstrap',
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: "{{ route('backend.get-clinic-appointments') }}",
                    dataType: 'json',
                    success: function(response) {
                        if (response.status && response.data && Array.isArray(response.data)) {
                            var events = response.data.map(function(appointment) {
                                return {
                                    id: appointment.id,
                                    name: appointment.user.first_name,
                                    title: appointment.service_name,
                                    start: appointment.start_date_time,
                                };
                            });
                            successCallback(events);
                        } else {
                            failureCallback("Invalid data format.");
                        }
                    },
                    error: function(xhr, status, error) {
                        failureCallback(error);
                    }
                });
            },
            eventColor: 'rgb(99, 102, 241)',
            textColor: '#fff',
            timeFormat: {
                agenda: 'H(:mm)'
            },
            eventDidMount: function(info) {
                $(info.el).tooltip({
                    title: info.event.extendedProps.name + ' - ' + info.event.title,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            },
            eventClick: function(info) {
                var id = info.event.id;
                var url = "{{ URL::to('app/appointments/clinicAppointmentDetail') }}/" + id;
                window.location.replace(url);
            },
            timeFormat: 'H(:mm)',
            eventTimeFormat: {
                hour: 'numeric',
                minute: '2-digit',
                meridiem: 'short'
            },
            buttonText: {
                today: 'Today'
            },
        });
        calendar.render();
    });
</script>
@endpush
