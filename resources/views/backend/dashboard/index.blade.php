@extends('backend.layouts.app', ['isBanner' => false])

@section('title')
    {{ 'Dashboard' }}
@endsection

@section('content')
<div class="dashboard-page">
    {{-- Greeting Header --}}
    <div class="dash-greeting mb-4 fade-in-up">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="dash-greeting-title mb-1">
                    <span class="fw-light">{{ greeting() }} </span>
                    <span class="text-capitalize">{{ $current_user }}</span>
                </h1>
                <p class="text-muted mb-0">{{ __('dashboard.lbl_performance') }}</p>
            </div>
            <form id="dateRangeForm" class="d-flex align-items-center gap-2">
                <div class="form-group my-0 d-flex gap-2">
                    <input type="text" name="date_range" id="revenuedateRangeInput" value="{{ $date_range }}"
                        class="form-control dashboard-date-range dash-date-input" placeholder="{{ __('messages.Select_Date') }}"
                        readonly="readonly" data-animate="true">
                    <a href="{{ route('backend.home') }}" class="btn btn-outline-secondary dash-btn-icon" id="refreshRevenuechart"
                        title="{{ __('appointment.reset') }}" data-bs-placement="top" data-bs-toggle="tooltip">
                        <i class="ph ph-arrow-counter-clockwise"></i>
                    </a>
                    <button type="submit" name="action" value="filter" class="btn btn-primary dash-btn-icon"
                        data-bs-toggle="tooltip" data-bs-title="{{ __('messages.submit_date_filter') }}"
                        id="submitBtn" disabled><i class="ph ph-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stat Cards Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4 col-xxl-2 fade-in-up fade-in-up-delay-1">
            <a href="{{ route('backend.appointments.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #6366f1; border-left: 4px solid #6366f1 !important;" data-stat="appointments">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                            <i class="ph ph-calendar-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('appointment.total_number_appointment') }}</p>
                            <h3 class="mb-0 fw-bold" id="total_booking_count" data-count="{{ $data['total_appointments'] }}">{{ $data['total_appointments'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xxl-2 fade-in-up fade-in-up-delay-2">
            <a href="{{ route('backend.services.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #10b981; border-left: 4px solid #10b981 !important;" data-stat="services">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                            <i class="ph ph-first-aid-kit"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_active_service') }}</p>
                            <h3 class="mb-0 fw-bold" id="total_active_service_count" data-count="{{ $data['total_clinicservice'] }}">{{ $data['total_clinicservice'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @if (multiVendor() == '1' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')))
        <div class="col-sm-6 col-lg-4 col-xxl-2 fade-in-up fade-in-up-delay-3">
            <a href="{{ route('backend.multivendors.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #f59e0b; border-left: 4px solid #f59e0b !important;" data-stat="vendors">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                            <i class="ph ph-storefront"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_active_vendors') }}</p>
                            <h3 class="mb-0 fw-bold" id="total_active_vendor_count" data-count="{{ $data['totalactivevendor'] }}">{{ $data['totalactivevendor'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif
        <div class="col-sm-6 col-lg-4 col-xxl-2 fade-in-up fade-in-up-delay-4">
            <a href="{{ route('backend.clinics.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #ec4899; border-left: 4px solid #ec4899 !important;" data-stat="clinics">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(236,72,153,0.1); color: #ec4899;">
                            <i class="ph ph-hospital"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_clinics') }}</p>
                            <h3 class="mb-0 fw-bold" data-count="{{ $data['total_clinics'] }}">{{ $data['total_clinics'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xxl-2 fade-in-up fade-in-up-delay-5">
            <a href="{{ route('backend.customers.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #3b82f6; border-left: 4px solid #3b82f6 !important;" data-stat="users">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                            <i class="ph ph-users-three"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_users') }}</p>
                            <h3 class="mb-0 fw-bold" id="total_user" data-count="{{ $data['total_user'] }}">{{ $data['total_user'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-4 col-xxl-2 fade-in-up fade-in-up-delay-6">
            <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #8b5cf6; border-left: 4px solid #8b5cf6 !important;" data-stat="revenue">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(139,92,246,0.1); color: #8b5cf6;">
                        <i class="ph ph-currency-circle-dollar"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{ __('dashboard.total_revenue') }}</p>
                        <h3 class="mb-0 fw-bold" id="total_revenue_amount">{{ Currency::format($data['total_revenue']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- New Module Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3 fade-in-up">
            <a href="{{ route('backend.nurse-requests.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #ef4444; border-left: 4px solid #ef4444 !important;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(239,68,68,0.1); color: #ef4444;">
                            <i class="ph ph-heartbeat"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_nurse_requests') }}</p>
                            <h3 class="mb-0 fw-bold" data-count="{{ $data['total_nurse_requests'] }}">{{ $data['total_nurse_requests'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 fade-in-up">
            <a href="{{ route('backend.test-orders.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #0ea5e9; border-left: 4px solid #0ea5e9 !important;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(14,165,233,0.1); color: #0ea5e9;">
                            <i class="ph ph-flask"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_lab_orders') }}</p>
                            <h3 class="mb-0 fw-bold" data-count="{{ $data['total_lab_orders'] }}">{{ $data['total_lab_orders'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 fade-in-up">
            <a href="{{ route('backend.icu-admission-requests.index') }}" class="text-decoration-none">
                <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #f97316; border-left: 4px solid #f97316 !important;">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="dash-stat-icon" style="background: rgba(249,115,22,0.1); color: #f97316;">
                            <i class="ph ph-bed"></i>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted small mb-1">{{ __('dashboard.total_icu_admissions') }}</p>
                            <h3 class="mb-0 fw-bold" data-count="{{ $data['total_icu_admissions'] }}">{{ $data['total_icu_admissions'] }}</h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm-6 col-lg-3 fade-in-up">
            <div class="card dash-stat-card gradient-overlay border-0 h-100" style="--card-color: #14b8a6; border-left: 4px solid #14b8a6 !important;">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="dash-stat-icon" style="background: rgba(20,184,166,0.1); color: #14b8a6;">
                        <i class="ph ph-user-circle-check"></i>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted small mb-1">{{ __('dashboard.total_independent_bookings') }}</p>
                        <h3 class="mb-0 fw-bold" data-count="{{ $data['total_independent_bookings'] }}">{{ $data['total_independent_bookings'] }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Revenue Chart --}}
        <div class="col-xxl-8 col-lg-7 scale-in">
            <div class="card dash-card border-0 h-100" data-chart="revenue">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0 sticky-header">
                    <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.lbl_tot_revenue') }}</h5>
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
        <div class="col-xxl-4 col-lg-5 scale-in">
            <div class="card dash-card border-0 h-100" data-widget="demographics">
                <div class="card-header bg-transparent border-0 pb-0">
                    <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.visited_patient') }}</h5>
                </div>
                <div class="card-body d-flex align-items-end">
                    <div class="d-flex align-items-end flex-grow-1 gap-2">
                        <div class="text-center flex-grow-1 dash-demo-item observe-animation" data-age-group="child">
                            <img src="{{ asset('img/dashboard/girl-and-boy-age-0-20.png') }}" class="img-fluid mb-2" alt="age-0-25" style="max-height: 90px;">
                            <h6 class="mb-0 fw-normal text-muted small">@lang('messages.0_25_age')</h6>
                            <div class="mt-3 pt-3 border-top">
                                <h4 class="mb-0 fw-bold" id="child_patient_count" data-count="{{ $data['child_patient_count'] }}">{{ $data['child_patient_count'] }}</h4>
                            </div>
                        </div>
                        <div class="text-center flex-grow-1 dash-demo-item observe-animation" data-age-group="adult">
                            <img src="{{ asset('img/dashboard/girl-and-boy-age-20-40.png') }}" class="img-fluid mb-2" alt="age-26-50" style="max-height: 90px;">
                            <h6 class="mb-0 fw-normal text-muted small">@lang('messages.26_50_age')</h6>
                            <div class="mt-3 pt-3 border-top">
                                <h4 class="mb-0 fw-bold" id="adult_patient_count" data-count="{{ $data['adult_patient_count'] }}">{{ $data['adult_patient_count'] }}</h4>
                            </div>
                        </div>
                        <div class="text-center flex-grow-1 dash-demo-item observe-animation" data-age-group="senior">
                            <img src="{{ asset('img/dashboard/girl-and-boy-age-40-80.png') }}" class="img-fluid mb-2" alt="age-50+" style="max-height: 90px;">
                            <h6 class="mb-0 fw-normal text-muted small">@lang('messages.50+_age')</h6>
                            <div class="mt-3 pt-3 border-top">
                                <h4 class="mb-0 fw-bold" id="old_patient_count" data-count="{{ $data['old_patient_count'] }}">{{ $data['old_patient_count'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Upcoming Appointments --}}
        <div class="col-xxl-{{ multiVendor() == '1' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) ? '4' : '5' }} col-lg-5 observe-animation">
            <div class="card dash-card border-0 h-100" data-widget="appointments">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0 sticky-header">
                    <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.lbl_upcoming_appointment') }}</h5>
                    @if (count($data['upcomming_appointments']) >= 5)
                        <a id="appointment_view_all_link" href="{{ route('backend.appointments.index') }}"
                            class="btn btn-sm btn-outline-primary d-none">{{ __('dashboard.view_all') }}</a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="upcoming-appointments">
                        <ul id="upcoming-appointments" class="list-inline p-0 m-0">
                            @forelse ($data['upcomming_appointments'] as $upcomming_appointments)
                                <li class="mb-3">
                                    <div class="dash-appt-item p-3 rounded-3">
                                        <div class="row align-items-center">
                                            <div class="col-3">
                                                <div class="text-center">
                                                    <p class="mb-0 text-primary fw-semibold small">
                                                        {{ date($data['dateformate'], strtotime($upcomming_appointments->appointment_date)) }}
                                                    </p>
                                                    <span class="text-primary small">
                                                        {{ $upcomming_appointments->appointment_time
                                                            ? \Carbon\Carbon::parse($upcomming_appointments->appointment_time)->timezone($timeZone)->format($data['timeformate'])
                                                            : '--' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-8 ps-0">
                                                <div class="border-start border-2 border-primary ps-3 ms-sm-2">
                                                    <h6 class="mb-1 fw-semibold">{{ optional($upcomming_appointments->user)->full_name }}</h6>
                                                    <p class="mb-0 text-muted small">{{ __('clinic.lbl_clinic_name') }}: {{ optional($upcomming_appointments->cliniccenter)->name }}</p>
                                                    <span class="text-muted small">{{ optional($upcomming_appointments->clinicservice)->name }} By <b>{{ optional($upcomming_appointments->doctor)->full_name }}</b></span>
                                                </div>
                                            </div>
                                            <div class="col-1 px-0">
                                                <a href="{{ route('backend.appointments.clinicAppointmentDetail', ['id' => $upcomming_appointments->id]) }}" class="text-muted">
                                                    <i class="ph ph-caret-right"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="text-center py-5">
                                    <i class="ph ph-calendar-x text-muted" style="font-size: 2.5rem;"></i>
                                    <p class="text-muted mt-2 mb-0">{{ __('dashboard.no_data_available') }}</p>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Registered Vendors (if multivendor) --}}
        @if (multiVendor() == '1' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')))
        <div class="col-xxl-4 col-lg-5">
            <div class="card dash-card border-0 h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0">
                    <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.register_vendor') }}</h5>
                    @if (count($data['register_vendor']) >= 4)
                        <a id="vendor_view_all_link" href="{{ route('backend.multivendors.index') }}"
                            class="btn btn-sm btn-outline-primary d-none">{{ __('dashboard.view_all') }}</a>
                    @endif
                </div>
                <div class="card-body">
                    <ul id="register_vendors_list" class="list-inline m-0 p-0 register-vendors-list">
                        @forelse ($data['register_vendor'] as $register_vendors)
                            <li class="mb-3">
                                <div class="dash-appt-item d-flex align-items-center justify-content-between p-3 rounded-3 gap-3 flex-sm-row flex-column">
                                    <div class="d-flex align-items-center gap-3 flex-sm-row flex-lg-nowrap flex-md-wrap flex-column flex-nowrap">
                                        <div class="flex-shrink-0">
                                            <img src="{{ $register_vendors->profile_image ?? default_user_avatar() }}"
                                                class="avatar-50 rounded-circle" alt="user-image" style="object-fit: cover;">
                                        </div>
                                        <div class="text-sm-start text-center">
                                            <h6 class="mb-1 fw-semibold">{{ $register_vendors->full_name }}</h6>
                                            <small class="text-muted">{{ date($data['dateformate'], strtotime($register_vendors->created_at)) }} At {{ date($data['timeformate'], strtotime($register_vendors->created_at)) }}</small>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <span class="badge {{ $register_vendors->email_verified_at !== null ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} px-3 py-2 rounded-pill">
                                            {{ $register_vendors->email_verified_at !== null ? __('messages.verified') : __('messages.unverified') }}
                                        </span>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-center py-5">
                                <i class="ph ph-storefront text-muted" style="font-size: 2.5rem;"></i>
                                <p class="text-muted mt-2 mb-0">{{ __('dashboard.no_data_available') }}</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Payment History --}}
        <div class="col-xxl-{{ multiVendor() == '1' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) ? '4' : '7' }} col-lg-7">
            <div class="card dash-card border-0 h-100">
                <div class="card-header bg-transparent d-flex align-items-center justify-content-between flex-wrap gap-3 border-0 pb-0">
                    <h5 class="card-title mb-0 fw-semibold">{{ __('dashboard.payment_history') }}</h5>
                    @if (count($data['payment_history']) >= 5)
                        <a id="payment_view_all_link" href="{{ route('backend.appointments.index') }}"
                            class="btn btn-sm btn-outline-primary d-none">{{ __('dashboard.view_all') }}</a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive rounded-3">
                        <table class="table dash-table m-0">
                            <thead>
                                <tr>
                                    <th>{{ __('sidebar.patient') }}</th>
                                    <th>{{ __('messages.date_time') }}</th>
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
                                        if ($transaction) {
                                            if ($transaction->payment_status == 1) {
                                                $payment_status = __('dashboard.paid');
                                                $badge_class = 'bg-success-subtle text-success';
                                            } elseif ($transaction->payment_status == 0 && $transaction->advance_payment_status == 1) {
                                                $payment_status = __('dashboard.advance_paid');
                                                $badge_class = 'bg-info-subtle text-info';
                                            } else {
                                                $payment_status = __('dashboard.pending');
                                                $badge_class = 'bg-warning-subtle text-warning';
                                            }
                                        }
                                    @endphp
                                    @if ($transaction)
                                        <tr>
                                            <td class="fw-medium">{{ optional($paymenthistory->user)->full_name }}</td>
                                            <td class="text-muted small">{{ date($data['dateformate'], strtotime($paymenthistory->appointment_date)) }} At {{ date($data['timeformate'], strtotime($paymenthistory->appointment_time)) }}</td>
                                            <td>{{ optional($paymenthistory->cliniccenter)->name }}</td>
                                            <td>{{ optional($paymenthistory->clinicservice)->name }}</td>
                                            <td class="fw-semibold">{{ Currency::format($transaction->total_amount) }}</td>
                                            <td>{{ ucfirst($transaction->transaction_type) }}</td>
                                            <td><span class="badge {{ $badge_class }} px-2 py-1 rounded-pill">{{ $payment_status }}</span></td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td class="text-center text-muted py-4" colspan="7">
                                            {{ __('messages.payment_history_notavailable') }}
                                        </td>
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

    /* Scrollable Areas */
    .upcoming-appointments {
        min-height: 22rem;
        max-height: 22rem;
        overflow-y: auto;
    }
    .register-vendors-list {
        height: 22rem;
        overflow-y: auto;
    }

    /* Demographics */
    .dash-demo-item {
        padding: 0.5rem;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.40.0/apexcharts.min.css"
    integrity="sha512-tJYqW5NWrT0JEkWYxrI4IK2jvT7PAiOwElIGTjALSyr8ZrilUQf+gjw2z6woWGSZqeXASyBXUr+WbtqiQgxUYg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush

@push('after-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.40.0/apexcharts.min.js"
    integrity="sha512-Kr1p/vGF2i84dZQTkoYZ2do8xHRaiqIa7ysnDugwoOcG0SbIx98erNekP/qms/hBDiBxj336//77d0dv53Jmew=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
                const formAction = `{{ url('app/daterange') }}/${encodedDateRange}`;
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
    });

    var dateRangeValue = $('#revenuedateRangeInput').val();

    if (dateRangeValue != '') {
        var dates = dateRangeValue.split(" to ");
        var startDate = dates[0];
        var endDate = dates[1];

        if (startDate != null && endDate != null) {
            revanue_chart('Free', startDate, endDate);
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

    var chart = null;
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
                            name: "{{ __('messages.total_revenue') }}",
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
                                formatter: function(value) {
                                    return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                        style: 'currency',
                                        currency: '{{ GetcurrentCurrency() }}'
                                    }).format(value);
                                }
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
                        tooltip: {
                            enabled: true,
                            y: {
                                formatter: function(value) {
                                    return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                                        style: 'currency',
                                        currency: '{{ GetcurrentCurrency() }}'
                                    }).format(value);
                                }
                            }
                        },
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
