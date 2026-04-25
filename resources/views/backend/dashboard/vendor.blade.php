@extends('backend.layouts.app', ['isBanner' => false])

@section('title')
{{ 'Dashboard' }}
@endsection

@section('content')
<div class="dashboard-page">
{{-- Breadcrumb + Greeting --}}
<div class="dash-greeting mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="text-muted small">{{ __('menu.main') }}</span>
                <i class="ph ph-caret-double-right text-muted" style="font-size: 0.7rem;"></i>
                <span class="fw-medium small">{{ __('dashboard.title') }}</span>
            </div>
            <h1 class="dash-greeting-title mb-1">
                <span class="fw-light">{{greeting()}} </span>
                <span class="text-capitalize">{{$current_user}}</span>
            </h1>
            <p class="text-muted mb-0">{{ __('dashboard.lbl_performance') }}</p>
        </div>
        <form id="dateRangeForm" class="d-flex align-items-center gap-2">
            <div class="form-group my-0 d-flex gap-2">
                <input type="text" name="date_range" id="revenuedateRangeInput" value="{{ $date_range }}" class="form-control dashboard-date-range dash-date-input" placeholder="Select Date" readonly="readonly">
                <a href="{{ route('backend.vendor-dashboard') }}" class="btn btn-outline-secondary dash-btn-icon" id="refreshRevenuechart">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                </a>
                <button type="submit" name="action" value="filter" class="btn btn-primary dash-btn-icon" data-bs-toggle="tooltip" data-bs-title="{{ __('messages.submit_date_filter') }}" id="submitBtn" disabled>
                    <i class="ph ph-funnel"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4 col-xxl-2">
        <a href="{{ route('backend.clinics.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #ec4899 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(236,72,153,0.1); color: #ec4899;">
                        <i class="ph ph-hospital"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('dashboard.total_number_of')}} {{__('sidebar.clinic')}}</p>
                        <h3 class="mb-0 fw-bold">{{$data['total_location']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 col-xxl-2">
        <a href="{{ route('backend.services.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="ph ph-first-aid-kit"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{ __('dashboard.total_active_service') }}</p>
                        <h3 class="mb-0 fw-bold">{{$data['total_service']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 col-xxl-2">
        <a href="{{ route('backend.appointments.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #6366f1 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        <i class="ph ph-calendar-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('appointment.total_number_appointment')}}</p>
                        <h3 class="mb-0 fw-bold">{{$data['total_appointments']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 col-xxl-2">
        <a href="{{ route('backend.doctor.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                        <i class="ph ph-stethoscope"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('appointment.total_number_of_doctors')}}</p>
                        <h3 class="mb-0 fw-bold">{{$data['total_doctor']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 col-xxl-2">
        <a href="{{ route('backend.customers.index') }}" class="text-decoration-none">
            <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{__('appointment.total_number_of_patients')}}</p>
                        <h3 class="mb-0 fw-bold">{{$data['total_new_customers']}}</h3>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-lg-4 col-xxl-2">
        <div class="card dash-stat-card border-0 h-100" style="border-left: 4px solid #8b5cf6 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="dash-stat-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
                    <i class="ph ph-currency-circle-dollar"></i>
                </div>
                <div class="flex-grow-1">
                    <p class="text-muted small mb-1">{{__('dashboard.total_revenue')}}</p>
                    <h3 class="mb-0 fw-bold">{{Currency::format($data['total_revenue'])}}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Revenue Chart --}}
    <div class="col-xxl-8 col-lg-7">
        <div class="card dash-card border-0 h-100">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0">
                <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.lbl_tot_revenue') ?? 'Total Revenue' }}</h5>
                <div id="date_range" class="dropdown d-none">
                    <a href="#" class="dropdown-toggle btn btn-sm btn-outline-secondary total_revenue" id="dropdownTotalRevenue" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ __('dashboard.year') }}
                        <i class="ph ph-caret-down ms-1"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-soft-primary sub-dropdown" aria-labelledby="dropdownTotalRevenue">
                        <li><a class="revenue-dropdown-item dropdown-item" data-type="Year">{{ __('dashboard.year') }}</a></li>
                        <li><a class="revenue-dropdown-item dropdown-item" data-type="Month">{{ __('dashboard.month') }}</a></li>
                        <li><a class="revenue-dropdown-item dropdown-item" data-type="Week">{{ __('dashboard.week') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div id="total-revenue"></div>
            </div>
        </div>
    </div>

    {{-- Patient Demographics --}}
    <div class="col-xxl-4 col-lg-5">
        <div class="card dash-card border-0 h-100">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="card-title mb-0 fw-semibold">{{__('dashboard.visited_patient')}}</h5>
            </div>
            <div class="card-body d-flex align-items-end">
                <div class="d-flex align-items-end flex-grow-1 gap-2">
                    <div class="text-center flex-grow-1 dash-demo-item">
                        <img src="{{ asset('img/dashboard/girl-and-boy-age-0-20.png') }}" class="img-fluid mb-2" alt="age-0-25" style="max-height: 90px;">
                        <h6 class="mb-0 fw-normal text-muted small">@lang('messages.0_25_age')</h6>
                        <div class="mt-3 pt-3 border-top">
                            <h4 class="mb-0 fw-bold">{{$data['child_patient_count']}}</h4>
                        </div>
                    </div>
                    <div class="text-center flex-grow-1 dash-demo-item">
                        <img src="{{ asset('img/dashboard/girl-and-boy-age-20-40.png') }}" class="img-fluid mb-2" alt="age-26-50" style="max-height: 90px;">
                        <h6 class="mb-0 fw-normal text-muted small">@lang('messages.26_50_age')</h6>
                        <div class="mt-3 pt-3 border-top">
                            <h4 class="mb-0 fw-bold">{{$data['adult_patient_count']}}</h4>
                        </div>
                    </div>
                    <div class="text-center flex-grow-1 dash-demo-item">
                        <img src="{{ asset('img/dashboard/girl-and-boy-age-40-80.png') }}" class="img-fluid mb-2" alt="age-50+" style="max-height: 90px;">
                        <h6 class="mb-0 fw-normal text-muted small">@lang('messages.50+_age')</h6>
                        <div class="mt-3 pt-3 border-top">
                            <h4 class="mb-0 fw-bold">{{$data['old_patient_count']}}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Upcoming Appointments --}}
    <div class="col-xxl-4 col-lg-5">
        <div class="card dash-card border-0 h-100">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0">
                <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.lbl_upcoming_appointment') ?? 'Upcoming Appointment' }}</h5>
                @if(count($data['upcomming_appointments']) >= 10)
                <a href="{{ route('backend.appointments.index') }}" class="btn btn-sm btn-outline-primary">{{ __('clinic.view_all') }}</a>
                @endif
            </div>
            <div class="card-body">
                <div class="upcoming-appointments">
                    <ul class="list-inline p-0 m-0">
                        @forelse ($data['upcomming_appointments'] as $upcomming_appointments)
                        <li class="mb-3">
                            <div class="dash-appt-item d-flex align-items-center p-3 rounded-3">
                                <div class="flex-grow-1">
                                    <p class="mb-0 text-primary fw-semibold small">{{ date($data['dateformate'], strtotime($upcomming_appointments->start_date_time)) }}</p>
                                    <span class="text-primary small">{{ date($data['timeformate'], strtotime($upcomming_appointments->start_date_time)) }}</span>
                                </div>
                                <div class="border-start border-2 border-primary ps-3 ms-3 flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">{{ optional($upcomming_appointments->user)->full_name }}</h6>
                                    <p class="mb-0 text-muted small">{{__('clinic.lbl_clinic_name')}}: {{optional($upcomming_appointments->cliniccenter)->name}}</p>
                                    <span class="text-muted small">{{optional($upcomming_appointments->clinicservice)->name}} By <b>{{optional($upcomming_appointments->doctor)->full_name}}</b></span>
                                </div>
                                <div class="ms-2">
                                    <a href="{{ route('backend.appointments.clinicAppointmentDetail', ['id' => $upcomming_appointments->id]) }}" class="text-muted">
                                        <i class="ph ph-caret-right"></i>
                                    </a>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="text-center py-5">
                            <i class="ph ph-calendar-x text-muted" style="font-size: 2.5rem;"></i>
                            <p class="text-muted mt-2 mb-0">{{ __('dashboard.no_data_available') ?? 'No data available' }}</p>
                        </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Top 10 Clinics --}}
    <div class="col-xxl-4 col-lg-5">
        <div class="card dash-card border-0 h-100">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0">
                <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.top_10_clinics') }}</h5>
                @if(count($data['top_location']) >= 4)
                <a href="{{ route('backend.clinics.index') }}" class="btn btn-sm btn-outline-primary">{{ __('clinic.view_all') }}</a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive rounded-3">
                    <table class="table dash-table m-0">
                        <thead>
                            <tr>
                                <th>{{__('clinic.clinic_image')}}</th>
                                <th>{{__('clinic.lbl_clinic_name')}}</th>
                                <th>{{__('appointment.appointment_title')}}</th>
                                <th>{{__('appointment.amount')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['top_location'] as $toplocation)
                            <tr>
                                <td>
                                    <img src="{{ $toplocation['clinic_image'] ?? default_user_avatar() }}" class="avatar-36 rounded-circle" alt="table-image" style="object-fit: cover;">
                                </td>
                                <td class="fw-medium">{{ $toplocation['clinic_name'] }}</td>
                                <td>{{ $toplocation['appointment_count'] }}</td>
                                <td class="fw-semibold">{{ Currency::format($toplocation['total_amount']) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center text-muted py-4" colspan="4">{{ __('messages.top_location_not_available') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment History --}}
    <div class="col-xxl-4 col-lg-7">
        <div class="card dash-card border-0 h-100">
            <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0">
                <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.payment_history') ?? 'Payment History' }}</h5>
                @if(count($data['payment_history']) >= 5)
                <a href="{{ route('backend.appointments.index') }}" class="btn btn-sm btn-outline-primary">{{ __('clinic.view_all') }}</a>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive rounded-3">
                    <table class="table dash-table m-0">
                        <thead>
                            <tr>
                                <th>{{ __('messages.patient_name') }}</th>
                                <th>{{ __('messages.date') }}</th>
                                <th>{{ __('clinic.singular_title') }}</th>
                                <th>{{ __('messages.service') }}</th>
                                <th>{{ __('appointment.price') }}</th>
                                <th>{{ __('earning.lbl_payment_method') }}</th>
                                <th>{{ __('appointment.lbl_payment_status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($data['payment_history'] as $paymenthistory)
                            @php
                                $transaction = $paymenthistory->appointmenttransaction;
                                $is_paid = optional($transaction)->payment_status == 1;
                            @endphp
                            <tr>
                                <td class="fw-medium">{{ optional($paymenthistory->user)->full_name }}</td>
                                <td class="text-muted small">{{ date($data['dateformate'], strtotime($paymenthistory->appointment_date)) }} At {{ date($data['timeformate'], strtotime($paymenthistory->appointment_time)) }}</td>
                                <td>{{ optional($paymenthistory->cliniccenter)->name }}</td>
                                <td>{{ optional($paymenthistory->clinicservice)->name }}</td>
                                <td class="fw-semibold">{{ Currency::format(optional($transaction)->total_amount) }}</td>
                                <td>{{ optional($transaction)->transaction_type }}</td>
                                <td>
                                    <span class="badge {{ $is_paid ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} px-2 py-1 rounded-pill">
                                        {{ $is_paid ? __('dashboard.paid') : __('dashboard.pending') }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-center text-muted py-4" colspan="7">{{ __('messages.payment_history_notavailable') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
    .dash-date-input {
        border-radius: 8px;
        font-size: 0.875rem;
        min-width: 220px;
    }
    .dash-btn-icon {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        border-radius: 8px;
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

    /* Appointment Items */
    .dash-appt-item {
        background: #f9fafb;
        transition: background 0.15s ease;
    }
    .dash-appt-item:hover {
        background: #f0f4ff;
    }

    /* Tables */
    .dash-table {
        font-size: 0.875rem;
    }
    .dash-table thead th {
        background: #f9fafb;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
        padding: 0.75rem 1rem;
        white-space: nowrap;
    }
    .dash-table tbody td {
        padding: 0.75rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }
    .dash-table tbody tr:hover {
        background: #f9fafb;
    }

    /* Scrollable */
    .upcoming-appointments {
        min-height: 22rem;
        max-height: 22rem;
        overflow-y: auto;
    }

    /* Demographics */
    .dash-demo-item {
        padding: 0.5rem;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.40.0/apexcharts.min.css" integrity="sha512-tJYqW5NWrT0JEkWYxrI4IK2jvT7PAiOwElIGTjALSyr8ZrilUQf+gjw2z6woWGSZqeXASyBXUr+WbtqiQgxUYg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('after-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.40.0/apexcharts.min.js" integrity="sha512-Kr1p/vGF2i84dZQTkoYZ2do8xHRaiqIa7ysnDugwoOcG0SbIx98erNekP/qms/hBDiBxj336//77d0dv53Jmew==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{ asset('js/dashboard-animations.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.getElementById('revenuedateRangeInput');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('dateRangeForm');

        function isValidDateRange(dateRange) {
            const datePattern = /^\d{4}-\d{2}-\d{2} to \d{4}-\d{2}-\d{2}$/;
            return datePattern.test(dateRange.trim());
        }

        function toggleSubmitButton() {
            if (isValidDateRange(dateInput.value)) {
                submitBtn.removeAttribute('disabled');
            } else {
                submitBtn.setAttribute('disabled', 'disabled');
            }
        }

        dateInput.addEventListener('input', toggleSubmitButton);

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            if (isValidDateRange(dateInput.value)) {
                const encodedDateRange = encodeURIComponent(dateInput.value);
                const formAction = `{{ url('app/vendor-daterange') }}/${encodedDateRange}`;
                window.location.href = formAction;
            }
        });

        toggleSubmitButton();
    });

    $(document).ready(function() {
        if (document.querySelector('.upcoming-appointments')) {
            Scrollbar.init(document.querySelector('.upcoming-appointments'), {
                continuousScrolling: false,
                alwaysShowTracks: false
            });
        }

        const range_flatpicker = document.querySelectorAll('.dashboard-date-range');
        Array.from(range_flatpicker, (elem) => {
            if (typeof flatpickr !== typeof undefined) {
                flatpickr(elem, {
                    mode: "range",
                });
            }
        });

        if (document.querySelectorAll('#chart-02').length) {
            const variableColors = IQUtils.getVariableColor();
            const colors = [variableColors.secondary, variableColors.primary];
            const options = {
                series: [{
                        name: "Sales",
                        type: 'line',
                        data: @json($data['revenue_chart']['total_price']),
                    },
                    {
                        name: "Total Appointments",
                        type: 'column',
                        data: @json($data['revenue_chart']['total_bookings']),
                    }
                ],
                colors: colors,
                chart: {
                    height: "75%",
                    type: "line",
                    toolbar: { show: false },
                },
                dataLabels: {
                    enabled: true,
                    enabledOnSeries: [0]
                },
                legend: { show: false },
                stroke: {
                    show: true,
                    curve: 'smooth',
                    lineCap: 'butt',
                    width: 3
                },
                grid: {
                    show: true,
                    strokeDashArray: 3,
                },
                xaxis: {
                    categories: @json($data['revenue_chart']['xaxis']),
                    labels: { minHeight: 20, maxHeight: 20 },
                    axisBorder: { show: false }
                },
                yaxis: [{
                    title: { text: 'Sales' },
                    labels: { minWidth: 19, maxWidth: 19 },
                    tickAmount: 3,
                    min: 0
                }, {
                    title: { text: 'Appointments' },
                    opposite: true,
                    tickAmount: 3,
                    min: 0
                }]
            };

            const chart = new ApexCharts(document.querySelector("#chart-02"), options);
            chart.render();
        }
    });

    var dateRangeValue = $('#revenuedateRangeInput').val();

    if (dateRangeValue != '') {
        var dates = dateRangeValue.split(" to ");
        var startDate = dates[0];
        var endDate = dates[1];

        if (startDate != null && endDate != null) {
            revanue_chart('free', startDate, endDate);
            $('#refreshRevenuechart').removeClass('d-none');
            $('#date_range').addClass('d-none');
        }
    } else {
        revanue_chart('Year');
        $('#refreshRevenuechart').addClass('d-none');
        $('#date_range').removeClass('d-none');
    }

    $('#refreshRevenuechart').on('click', function() {
        $('#revenuedateRangeInput').val('');
        revanue_chart('Year');
        $('#date_range').removeClass('d-none');
    });

    let revenueInstance;

    function revanue_chart(type, startDate, endDate) {
        var Base_url = "{{ url('/') }}";
        var url = Base_url + "/app/get_revnue_chart_data/" + type;

        $("#revenue_loader").show();

        $.ajax({
            url: url,
            method: "GET",
            data: {
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                $("#revenue_loader").hide();
                $(".total_revenue").text(type);
                if (document.querySelectorAll('#total-revenue').length) {
                    const variableColors = IQUtils.getVariableColor();
                    const colors = [variableColors.primary, variableColors.info];
                    const monthlyTotals = response.data.chartData;
                    const category = response.data.category;
                    const options = {
                        series: [{
                            name: 'Total Revenue',
                            data: monthlyTotals
                        }],
                        chart: {
                            fontFamily: '"Inter", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"',
                            height: 300,
                            type: 'area',
                            toolbar: { show: false },
                            sparkline: { enabled: false },
                        },
                        colors: colors,
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 3 },
                        yaxis: {
                            show: true,
                            labels: {
                                show: true,
                                style: { colors: "#8A92A6" },
                                offsetX: -15,
                            },
                        },
                        legend: { show: false },
                        xaxis: {
                            labels: { minHeight: 22, maxHeight: 22, show: true },
                            lines: { show: false },
                            categories: category
                        },
                        grid: {
                            show: true,
                            borderColor: 'var(--bs-body-bg)',
                            strokeDashArray: 0,
                            position: 'back',
                            xaxis: { lines: { show: true } },
                            yaxis: { lines: { show: true } },
                        },
                        fill: { type: 'solid', opacity: 0 },
                        tooltip: { enabled: true },
                    };

                    if (revenueInstance) {
                        revenueInstance.updateOptions(options);
                    } else {
                        revenueInstance = new ApexCharts(document.querySelector("#total-revenue"), options);
                        revenueInstance.render();
                    }
                }
            }
        });
    }

    $(document).on('click', '.revenue-dropdown-item', function() {
        var type = $(this).data('type');
        $('#revenuedateRangeInput').val('');
        revanue_chart(type);
    });
</script>
@endpush
