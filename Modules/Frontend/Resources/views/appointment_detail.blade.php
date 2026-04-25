@extends('frontend::layouts.master')

@section('title', __('frontend.appointment_detail'))

@section('content')
@include('frontend::components.section.breadcrumb')

@php
    $id         = $appointment ? $appointment->id : 0;
    $status     = $appointment ? $appointment->status : null;
    $pay_status = $appointment ? optional($appointment->appointmenttransaction)->payment_status : 0;
@endphp

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- ── Page header ── --}}
        <div class="pp-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <h1 class="pp-page-title">{{ __('frontend.appointment_detail') }}</h1>
            @if ($pay_status == 1 && $status == 'checkout')
                <a class="pp-btn pp-btn-teal"
                   href="{{ route('download_invoice', ['id' => $appointment->id]) }}">
                    <i class="ph ph-download-simple" aria-hidden="true"></i>
                    {{ __('frontend.lbl_download_invoice') }}
                </a>
            @endif
        </div>

        <div class="row">
            <div class="col-lg-8">

                {{-- ── Rate-us prompt ── --}}
                @if(empty($appointment->serviceRating) && $appointment->status == 'checkout' && optional($appointment->appointmenttransaction)->payment_status)
                    <div class="pp-detail-section mb-3 d-flex align-items-center justify-content-between flex-wrap gap-3"
                         style="padding:1rem 1.25rem">
                        <p class="mb-0" style="font-size:0.88rem;font-weight:600;color:var(--pp-navy)">
                            {{ __('frontend.havent_rated') }}
                        </p>
                        <button class="pp-btn pp-btn-teal"
                                data-bs-toggle="modal"
                                data-service-id="{{ optional($appointment->clinicservice)->id }}"
                                data-doctor-id="{{ optional($appointment->doctor)->id }}"
                                data-bs-target="#review-service">
                            <i class="ph-fill ph-star" aria-hidden="true"></i>
                            {{ __('frontend.rate_us') }}
                        </button>
                    </div>
                @endif

                {{-- ── Appointment ID ── --}}
                <div class="pp-detail-section mb-3 d-flex align-items-center justify-content-between gap-3"
                     style="padding:0.875rem 1.25rem">
                    <span class="pp-detail-label" style="margin-bottom:0">{{ __('frontend.appointment_id') }}</span>
                    <span class="pp-detail-id" style="color:var(--pp-teal)">#{{ $appointment->id }}</span>
                </div>

                {{-- ── Booking details ── --}}
                <div class="mb-4">
                    <h2 class="pp-section-title">{{ __('frontend.booking_detail') }}</h2>
                    <div class="pp-detail-section">
                        <div class="pp-detail-grid" style="padding:1.25rem">
                            <div>
                                <span class="pp-detail-label">{{ __('frontend.appointment_date_time') }}</span>
                                <p class="mb-0 fw-semibold" style="font-size:0.88rem;color:var(--pp-navy);font-family:var(--pp-font)">
                                    {{ DateFormate($appointment->appointment_date) }}
                                    <span style="color:var(--pp-muted);font-weight:400"> {{ __('messages.at') ?? 'at' }} </span>
                                    {{ \Carbon\Carbon::parse($appointment->appointment_time)->format(setting('time_formate') ?? 'h:i A') }}
                                </p>
                            </div>
                            <div>
                                <span class="pp-detail-label">{{ __('frontend.service_name') }}</span>
                                @if($appointment->booking_type === 'independent')
                                    <p class="mb-0 fw-semibold" style="font-size:0.88rem;color:var(--pp-navy)">
                                        {{ optional($appointment->independentService)->name ?? '-' }}
                                    </p>
                                @elseif(optional($appointment->clinicservice)->id)
                                    <a href="{{ route('service-details', ['id' => $appointment->clinicservice->id]) }}"
                                       class="fw-semibold" style="font-size:0.88rem;color:var(--pp-teal)">
                                        {{ $appointment->clinicservice->name }}
                                    </a>
                                @else
                                    <p class="mb-0" style="color:var(--pp-muted)">-</p>
                                @endif
                            </div>
                            <div>
                                <span class="pp-detail-label">{{ __('frontend.doctor') }}</span>
                                @if ($appointment->doctor === null)
                                    <p class="mb-0" style="color:var(--pp-muted)">-</p>
                                @else
                                    <div class="d-flex gap-2 align-items-center mt-1">
                                        <img src="{{ optional($appointment->doctor)->profile_image ?? default_user_avatar() }}"
                                             alt="avatar"
                                             style="width:30px;height:30px;border-radius:50%;object-fit:cover;flex-shrink:0">
                                        <div>
                                            <p class="mb-0 fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                                {{ getDisplayName($appointment->doctor) }}
                                            </p>
                                            @php $doctorEmail = optional($appointment->doctor)->email; @endphp
                                            @if ($doctorEmail)
                                                <a href="mailto:{{ $doctorEmail }}" style="font-size:0.72rem;color:var(--pp-teal)">
                                                    {{ $doctorEmail }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="border-top" style="padding:1.25rem">
                            <div class="pp-detail-grid">
                                <div>
                                    <span class="pp-detail-label">{{ __('frontend.clinic_name') }}</span>
                                    <div class="d-flex gap-2 align-items-center mt-1">
                                        <img src="{{ optional($appointment->cliniccenter)->file_url ?? default_file_url() }}"
                                             alt="clinic"
                                             style="width:26px;height:26px;border-radius:6px;object-fit:cover;flex-shrink:0">
                                        <p class="mb-0 fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                            {{ $appointment->cliniccenter ? optional($appointment->cliniccenter)->name : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <span class="pp-detail-label">{{ __('frontend.booking_status') }}</span>
                                    @php
                                        $apptStatusClass = match($appointment->status) {
                                            'pending'   => 'pp-status pp-status-pending',
                                            'confirmed' => 'pp-status pp-status-confirmed',
                                            'check_in'  => 'pp-status pp-status-checkin',
                                            'checkout'  => 'pp-status pp-status-completed',
                                            'cancelled' => 'pp-status pp-status-cancelled',
                                            default     => 'pp-status pp-status-pending',
                                        };
                                        $apptStatusLabel = match($appointment->status) {
                                            'checkout'  => __('frontend.completed') ?? 'Completed',
                                            'check_in'  => __('frontend.check_in') ?? 'Check In',
                                            default     => \Illuminate\Support\Str::title(str_replace('_', ' ', $appointment->status)),
                                        };
                                    @endphp
                                    <span class="{{ $apptStatusClass }} mt-1 d-inline-block">{{ $apptStatusLabel }}</span>
                                </div>
                                <div>
                                    <span class="pp-detail-label">{{ __('frontend.payment_status') }}</span>
                                    <div class="mt-1">
                                        @if($appointment->appointmenttransaction && $appointment->appointmenttransaction->payment_status)
                                            @if($appointment->status == 'cancelled')
                                                @if($appointment->advance_paid_amount > 0)
                                                    <span class="pp-pay-status pp-pay-refunded">{{ __('frontend.advance_refunded') }}</span>
                                                @else
                                                    <span class="pp-pay-status pp-pay-refunded">{{ __('frontend.payment_refunded') }}</span>
                                                @endif
                                            @else
                                                @if($appointment->appointmenttransaction->payment_method == 'cash')
                                                    <span class="pp-pay-status pp-pay-pending">{{ __('frontend.pending') }}</span>
                                                @else
                                                    <span class="pp-pay-status pp-pay-paid">{{ __('frontend.paid') }}</span>
                                                @endif
                                            @endif
                                        @elseif($advancePaid)
                                            @if($appointment->status == 'cancelled')
                                                <span class="pp-pay-status pp-pay-refunded">{{ __('frontend.advance_refunded') }}</span>
                                            @else
                                                <span class="pp-pay-status pp-pay-paid">{{ __('frontend.advance_paid') }}</span>
                                            @endif
                                        @else
                                            <span class="pp-pay-status pp-pay-pending">{{ __('frontend.pending') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top" style="padding:1.25rem">
                            <span class="pp-detail-label d-block mb-2">{{ __('frontend.booked_for') }}</span>
                            @if ($appointment->user === null)
                                <p class="mb-0" style="color:var(--pp-muted)">-</p>
                            @elseif($appointment->otherPatient)
                                <div class="d-flex gap-2 align-items-center">
                                    <img src="{{ optional($appointment->otherPatient)->profile_image ?? default_user_avatar() }}"
                                         alt="avatar"
                                         style="width:30px;height:30px;border-radius:50%;object-fit:cover">
                                    <p class="mb-0 fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                        {{ optional($appointment->otherPatient)->first_name . ' ' . optional($appointment->otherPatient)->last_name ?? '-' }}
                                    </p>
                                </div>
                            @else
                                <div class="d-flex gap-2 align-items-center">
                                    <img src="{{ optional($appointment->user)->profile_image ?? default_user_avatar() }}"
                                         alt="avatar"
                                         style="width:30px;height:30px;border-radius:50%;object-fit:cover">
                                    <div>
                                        <p class="mb-0 fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                            {{ optional($appointment->user)->first_name . ' ' . optional($appointment->user)->last_name ?? '-' }}
                                        </p>
                                        @php $userEmail = optional($appointment->user)->email; @endphp
                                        @if ($userEmail)
                                            <a href="mailto:{{ $userEmail }}" style="font-size:0.72rem;color:var(--pp-teal)">
                                                {{ $userEmail }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Service detail ── --}}
                <div class="mb-4">
                    <h2 class="pp-section-title">{{ __('frontend.service_detail') }}</h2>
                    <div class="pp-detail-section">

                        @if ($appointment->patientEncounter == null)
                            <div style="padding:1.25rem">
                                <div class="d-flex align-items-start gap-3">
                                    @if($appointment->booking_type === 'independent')
                                        <img src="{{ default_file_url() }}" alt="service"
                                             style="width:60px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0">
                                    @else
                                        <img src="{{ optional($appointment->clinicservice)->file_url ?? default_file_url() }}"
                                             alt="service"
                                             style="width:60px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0">
                                    @endif
                                    <div>
                                        @if($appointment->booking_type === 'independent')
                                            <p class="mb-1 fw-semibold" style="color:var(--pp-navy)">
                                                {{ optional($appointment->independentService)->name ?? '-' }}
                                            </p>
                                        @elseif(optional($appointment->clinicservice)->id)
                                            <a href="{{ route('service-details', ['id' => $appointment->clinicservice->id]) }}"
                                               class="fw-semibold" style="color:var(--pp-teal)">
                                                {{ $appointment->clinicservice->name }}
                                            </a>
                                            @if(optional($appointment->clinicservice)->description)
                                                <p class="mb-0 mt-1" style="font-size:0.8rem;color:var(--pp-muted)">
                                                    {{ optional($appointment->clinicservice)->description }}
                                                </p>
                                            @endif
                                        @else
                                            <p class="mb-0 fw-semibold" style="color:var(--pp-navy)">-</p>
                                        @endif

                                        @php
                                            if (optional($appointment->appointmenttransaction)->discount_type === 'percentage') {
                                                $payable_Amount = $appointment->service_price -
                                                    $appointment->service_price *
                                                    (optional($appointment->appointmenttransaction)->discount_value / 100);
                                            } else {
                                                $payable_Amount = $appointment->service_price -
                                                    optional($appointment->appointmenttransaction)->discount_value;
                                            }
                                            $total_tax = 0;
                                            $sub_total = $payable_Amount + (optional($appointment->appointmenttransaction)->inclusive_tax_price ?? 0);
                                            $inclusive_tax_data = json_decode(optional($appointment->appointmenttransaction)->inclusive_tax, true);
                                        @endphp

                                        @if (optional($appointment->appointmenttransaction)->discount_value > 0)
                                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                                <span class="fw-semibold" style="color:var(--pp-navy)">
                                                    {{ Currency::format($sub_total) }}
                                                </span>
                                                <span style="font-size:0.78rem;color:var(--pp-confirmed)">
                                                    @if (optional($appointment->appointmenttransaction)->discount_type === 'percentage')
                                                        ({{ optional($appointment->appointmenttransaction)->discount_value ?? '--' }}% off)
                                                    @else
                                                        ({{ Currency::format(optional($appointment->appointmenttransaction)->discount_value) ?? '--' }} off)
                                                    @endif
                                                </span>
                                                <del style="font-size:0.82rem;color:var(--pp-muted)">
                                                    {{ Currency::format($appointment->service_price) }}
                                                </del>
                                            </div>
                                        @else
                                            <p class="mb-0 mt-2 fw-semibold" style="color:var(--pp-navy)">
                                                {{ Currency::format($appointment->service_amount) }}
                                            </p>
                                        @endif

                                        @if(optional($appointment->appointmenttransaction)->inclusive_tax_price != null && $appointment->patientEncounter == null)
                                            <small style="color:var(--pp-muted);font-style:italic">
                                                {{ __('messages.lbl_with_inclusive_tax') }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($appointment->patientEncounter !== null &&
                             optional(optional($appointment->patientEncounter)->billingrecord)->billingItem != null)
                            @foreach (optional(optional($appointment->patientEncounter)->billingrecord)->billingItem as $billingItem)
                                <div class="d-flex align-items-start gap-3"
                                     style="padding:1.25rem;{{ !$loop->first ? 'border-top:1px solid var(--pp-border)' : '' }}">
                                    <img src="{{ optional($billingItem->clinicservice)->file_url ?? default_file_url() }}"
                                         alt="service"
                                         style="width:60px;height:60px;border-radius:10px;object-fit:cover;flex-shrink:0">
                                    <div>
                                        <p class="mb-1 fw-semibold" style="color:var(--pp-navy)">
                                            {{ optional($billingItem->clinicservice)->name }}
                                        </p>
                                        @if(optional($billingItem->clinicservice)->description)
                                            <p class="mb-1" style="font-size:0.8rem;color:var(--pp-muted)">
                                                {{ optional($billingItem->clinicservice)->description }}
                                            </p>
                                        @endif
                                        @php
                                            if ($billingItem->discount_type === 'percentage') {
                                                $payable_Amount = $billingItem->service_amount -
                                                    $billingItem->service_amount * ($billingItem->discount_value / 100);
                                            } else {
                                                $payable_Amount = $billingItem->service_amount - $billingItem->discount_value;
                                            }
                                        @endphp
                                        @if ($billingItem->discount_value > 0)
                                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                                <span style="font-size:0.8rem;color:var(--pp-muted)">
                                                    {{ Currency::format($billingItem->total_amount) }} × {{ $billingItem->quantity }} =
                                                </span>
                                                <span class="fw-semibold" style="color:var(--pp-navy)">
                                                    {{ Currency::format($billingItem->total_amount * $billingItem->quantity) }}
                                                </span>
                                                <span style="font-size:0.78rem;color:var(--pp-confirmed)">
                                                    @if ($billingItem->discount_type === 'percentage')
                                                        ({{ $billingItem->discount_value ?? '--' }}% off)
                                                    @else
                                                        ({{ Currency::format($billingItem->discount_value) ?? '--' }} off)
                                                    @endif
                                                </span>
                                                @if($billingItem->inclusive_tax_amount > 0)
                                                    <small style="color:var(--pp-muted);font-style:italic">
                                                        {{ __('messages.lbl_with_inclusive_tax') }}
                                                    </small>
                                                @endif
                                                <del style="font-size:0.8rem;color:var(--pp-muted)">
                                                    {{ Currency::format($billingItem->service_amount * $billingItem->quantity) }}
                                                </del>
                                            </div>
                                        @else
                                            <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                                                <span style="font-size:0.8rem;color:var(--pp-muted)">
                                                    {{ Currency::format($billingItem->total_amount) }} × {{ $billingItem->quantity }} =
                                                </span>
                                                <span class="fw-semibold" style="color:var(--pp-navy)">
                                                    {{ Currency::format($billingItem->total_amount * $billingItem->quantity) }}
                                                </span>
                                                @if($billingItem->inclusive_tax_amount > 0)
                                                    <small style="color:var(--pp-muted);font-style:italic">
                                                        {{ __('messages.lbl_with_inclusive_tax') }}
                                                    </small>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- ── Medical reports ── --}}
                @if(isset($medical_reports) && $medical_reports->count())
                    <div class="mb-4">
                        <h2 class="pp-section-title">{{ __('appointment.medical_report') }}</h2>
                        <div class="row g-3">
                            @foreach($medical_reports as $report)
                                <div class="col-12 col-sm-6 col-md-4">
                                    <a href="{{ $report->file_url }}" target="_blank" rel="noopener"
                                       class="pp-order-card d-block" style="padding:1rem">
                                        <p class="mb-1 fw-semibold" style="font-size:0.88rem;color:var(--pp-navy)">
                                            {{ $report->name }}
                                        </p>
                                        <p class="mb-2" style="font-size:0.75rem;color:var(--pp-muted)">
                                            {{ $report->date }}
                                        </p>
                                        <span class="pp-status pp-status-checkin" style="pointer-events:none">
                                            <i class="ph ph-eye" aria-hidden="true"></i>
                                            {{ __('messages.lbl_view') }}
                                        </span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── Encounter link ── --}}
                @if ($appointment->status == 'checkout' || $appointment->status == 'check_in')
                    <div class="pp-detail-section d-flex align-items-center justify-content-between gap-3 mb-4"
                         style="padding:1rem 1.25rem">
                        <span style="font-weight:600;color:var(--pp-navy);font-family:var(--pp-font)">
                            {{ __('frontend.encounter_details') }}
                        </span>
                        <a data-bs-toggle="modal" data-bs-target="#encounter-details-view"
                           class="pp-btn pp-btn-outline" style="font-size:0.78rem;padding:0.3rem 0.875rem">
                            {{ __('messages.lbl_view') }}
                        </a>
                    </div>
                @endif

                {{-- ── Review component ── --}}
                <x-frontend::section.review />

                {{-- ── Your review ── --}}
                @if($review)
                    <div class="mb-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                            <h2 class="pp-section-title mb-0">{{ __('frontend.your_review') }}</h2>
                            <div class="d-flex align-items-center gap-2 rate-us-btn">
                                <button class="pp-btn pp-btn-outline" style="padding:0.3rem 0.625rem"
                                        data-bs-toggle="modal"
                                        data-service-id="{{ optional($appointment->clinicservice)->id }}"
                                        data-doctor-id="{{ optional($appointment->doctor)->id }}"
                                        data-review-id="{{ $review->id }}"
                                        data-rating="{{ $review->rating }}"
                                        data-review-msg="{{ $review->review_msg }}"
                                        data-bs-target="#review-service"
                                        aria-label="{{ __('frontend.edit_review') ?? 'Edit review' }}">
                                    <i class="ph ph-pencil-simple-line" aria-hidden="true"></i>
                                </button>
                                <x-frontend::section.review />
                                <button class="delete-rating-btn pp-btn pp-btn-outline"
                                        style="padding:0.3rem 0.625rem;color:var(--pp-cancelled)"
                                        data-review-id="{{ $review->id }}"
                                        aria-label="{{ __('frontend.delete_review') ?? 'Delete review' }}">
                                    <i class="ph ph-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </div>
                        <div class="pp-detail-section" style="padding:1.25rem">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="pp-badge pp-badge-teal d-flex align-items-center gap-1">
                                        <i class="ph-fill ph-star" aria-hidden="true"></i>
                                        {{ $review->rating }}
                                    </span>
                                    <span class="fw-semibold" style="color:var(--pp-navy);font-family:var(--pp-font)">
                                        {{ $review->title }}
                                    </span>
                                </div>
                                <span class="pp-badge pp-badge-navy">{{ optional($review->clinic_service)->name }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <img src="{{ optional($review->user)->profile_image }}" alt="user"
                                     style="width:34px;height:34px;border-radius:50%;object-fit:cover;flex-shrink:0">
                                <div>
                                    <p class="mb-0 fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                        {{ optional($review->user)->gender == 'female' ? 'Miss.' : 'Mr.' }}
                                        {{ optional($review->user)->first_name . ' ' . optional($review->user)->last_name }}
                                    </p>
                                    <small style="color:var(--pp-muted)">{{ $review->updated_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            <p class="mb-0" style="font-size:0.88rem;color:var(--pp-text-alt)">
                                {{ $review->review_msg }}
                            </p>
                        </div>
                    </div>
                @endif

            </div>

            {{-- ── Payment summary sidebar ── --}}
            @php
                $service_total_amount = 0;
                $total_tax = 0;
            @endphp
            @if ($appointment->patientEncounter !== null)
                @foreach (optional(optional($appointment->patientEncounter)->billingrecord)->billingItem as $item)
                    @php
                        $quantity = $item->quantity ?? 1;
                        $service_total_amount += $item->total_amount;
                        if ($quantity > 1) {
                            if (isset($item->inclusive_tax_amount) && $item->inclusive_tax_amount > 0) {
                                $service_total_amount += $item->inclusive_tax_amount * $quantity;
                            }
                            if (!empty($item->discount_type)) {
                                if ($item->discount_type === 'fixed') {
                                    $service_total_amount -= $item->discount_value * $quantity;
                                } elseif ($item->discount_type === 'percentage') {
                                    $service_total_amount -= ($item->total_amount * $item->discount_value / 100);
                                }
                            }
                        }
                    @endphp
                @endforeach
            @endif

            <?php
                $transaction     = $appointment->appointmenttransaction ?: null;
                $discount_amount = 0;
                $total_amount    = 0;
                if ($transaction !== null) {
                    $tax = json_decode(optional($transaction)->tax_percentage, true);
                }
                if ($appointment->patientEncounter !== null) {
                    $transaction = optional($appointment->patientEncounter)->billingrecord ?: null;
                    if ($transaction !== null) {
                        if ($transaction['final_discount_type'] == 'percentage') {
                            $discount_amount = $service_total_amount * ($transaction['final_discount_value'] / 100);
                        } else {
                            $discount_amount = $transaction['final_discount_value'];
                        }
                        foreach (optional(optional($appointment->patientEncounter)->billingrecord)->billingItem as $billingItem) {
                            $total_amount += $billingItem->total_amount;
                        }
                        $tax = json_decode(optional($transaction)->tax_data, true);
                    }
                    $sub_total = $service_total_amount - $discount_amount;
                } else {
                    $sub_total = $appointment->service_amount;
                }
                if ($appointment->appointmenttransaction == null) {
                    $tax = Modules\Tax\Models\Tax::active()
                        ->whereNull('module_type')
                        ->orWhere('module_type', 'services')
                        ->where('status', 1)
                        ->where('tax_type', 'exclusive')
                        ->get();
                }
            ?>

            <div class="col-lg-4 mt-4 mt-lg-0">
                <h2 class="pp-section-title">{{ __('frontend.payment_details') }}</h2>

                {{-- ── Refund / wallet-deduction card (cancelled + paid) ── --}}
                @if(
                    $appointment->status == 'cancelled' &&
                    optional($appointment->appointmenttransaction)->payment_status != 0 &&
                    optional($appointment->appointmenttransaction)->transaction_type != 'cash'
                )
                    @php $refundAmount = $appointment->getRefundAmount(); @endphp
                    <div class="pp-detail-section mb-3" style="padding:1.25rem">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:0.75rem;color:var(--pp-muted)">
                                {{ formatDate($appointment->updated_at) }}
                            </span>
                            <span class="{{ $refundAmount >= 0 ? 'pp-status pp-status-completed' : 'pp-status pp-status-cancelled' }}">
                                {{ $refundAmount >= 0 ? __('frontend.refund_completed') : __('frontend.wallet_deducted') }}
                            </span>
                        </div>
                        <p class="fw-bold mb-3" style="color:var(--pp-navy);font-family:var(--pp-font)">
                            {{ $refundAmount >= 0
                                ? __('messages.refund_of')    . ' ' . \Currency::format($refundAmount)
                                : __('messages.wallet_deduction') . ' ' . \Currency::format(abs($refundAmount)) }}
                        </p>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:0.82rem;color:var(--pp-muted)">{{ __('earning.lbl_payment_method') }}</span>
                            <span style="font-size:0.82rem;font-weight:600;color:var(--pp-teal)">{{ __('messages.wallet') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:0.82rem;color:var(--pp-muted)">{{ __('clinic.price') }}</span>
                            <span style="font-size:0.82rem;color:var(--pp-navy)">{{ \Currency::format($appointment->total_amount) }}</span>
                        </div>
                        @if ($appointment->advance_paid_amount != 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">{{ __('messages.advanced_payment') }}</span>
                                <span style="font-size:0.82rem;color:var(--pp-navy)">{{ \Currency::format($appointment->advance_paid_amount) }}</span>
                            </div>
                        @endif
                        @if($appointment->cancellation_charge_amount != 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">
                                    {{ __('messages.cancellation_fee') }}
                                    @if($appointment->cancellation_type === 'percentage')
                                        ({{ $appointment->cancellation_charge }}%)
                                    @else
                                        ({{ Currency::format($appointment->cancellation_charge) }})
                                    @endif
                                </span>
                                <span style="font-size:0.82rem;color:var(--pp-navy)">
                                    {{ Currency::format($appointment->cancellation_charge_amount) }}
                                </span>
                            </div>
                        @endif
                        <div class="border-top mt-3 pt-3">
                            <div class="{{ $refundAmount >= 0 ? 'pp-refund-row-success' : 'pp-refund-row-danger' }}">
                                <span>{{ $refundAmount >= 0 ? __('messages.refund_amount') : __('frontend.wallet_deducted') }}</span>
                                <span class="fw-bold" style="color:var(--pp-navy)">{{ \Currency::format(abs($refundAmount)) }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Payment breakdown ── --}}
                <div class="pp-detail-section" style="padding:1.25rem">
                    @if ($transaction !== null)
                        @if ($appointment->patientEncounter !== null)
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">{{ __('messages.total') }}</span>
                                <span style="font-size:0.82rem;font-weight:600;color:var(--pp-navy)">
                                    {{ Currency::format($service_total_amount) ?? '--' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">
                                    {{ __('messages.discount') }}
                                    (<span style="color:var(--pp-confirmed)">
                                        @if ($transaction->final_discount_type === 'percentage')
                                            {{ $transaction->final_discount_value ?? '--' }}%
                                        @else
                                            {{ Currency::format($transaction->final_discount_value) ?? '--' }}
                                        @endif
                                    </span>)
                                </span>
                                <span style="font-size:0.82rem;font-weight:600;color:var(--pp-confirmed)">
                                    - {{ Currency::format($discount_amount) ?? '--' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">{{ __('frontend.sub_total') }}</span>
                                <span style="font-size:0.82rem;font-weight:600;color:var(--pp-navy)">
                                    {{ Currency::format($sub_total) ?? '--' }}
                                </span>
                            </div>
                        @endif

                        @if ($appointment->patientEncounter == null)
                            <div class="d-flex justify-content-between mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">
                                    {{ __('messages.total') }}
                                    @if ($appointment->appointmenttransaction && $appointment->appointmenttransaction->inclusive_tax_price > 0)
                                        <small style="font-style:italic">{{ __('messages.lbl_with_inclusive_tax') }}</small>
                                    @endif
                                </span>
                                <span style="font-size:0.82rem;font-weight:600;color:var(--pp-navy)">
                                    {{ Currency::format($sub_total) ?? '--' }}
                                </span>
                            </div>
                        @endif
                    @else
                        <div class="d-flex justify-content-between mb-2">
                            <span style="font-size:0.82rem;color:var(--pp-muted)">{{ __('messages.total') }}</span>
                            <span style="font-size:0.82rem;font-weight:600;color:var(--pp-navy)">
                                {{ Currency::format($sub_total) ?? '--' }}
                            </span>
                        </div>
                    @endif

                    @foreach ($tax as $t)
                        @if ($t['type'] == 'percent')
                            <?php
                                $tax_amount = 0;
                                if ($sub_total > 0) {
                                    $tax_amount = ($sub_total * $t['value']) / 100;
                                }
                                $total_tax += $tax_amount;
                            ?>
                        @elseif($t['type'] == 'fixed')
                            @php $total_tax += $t['value']; @endphp
                        @endif
                    @endforeach

                    <div class="d-flex justify-content-between mb-2">
                        <span style="font-size:0.82rem;color:var(--pp-muted)">
                            {{ __('appointment.tax') }}
                            @if($transaction !== null)
                                <button type="button"
                                        class="btn p-0 border-0 ms-1"
                                        style="color:var(--pp-muted);font-size:0.82rem;line-height:1;vertical-align:middle"
                                        data-bs-toggle="modal"
                                        data-bs-target="#taxDetailsModal"
                                        aria-label="{{ __('frontend.tax_detail') }}">
                                    <i class="ph ph-info" aria-hidden="true"></i>
                                </button>
                            @endif
                        </span>
                        <span style="font-size:0.82rem;color:var(--pp-muted)">{{ Currency::format($total_tax) ?? '--' }}</span>
                    </div>

                    @php
                        $grand_total = $appointment->patientEncounter
                            ? $service_total_amount + $total_tax - $discount_amount
                            : $appointment->total_amount;
                    @endphp

                    <div class="border-top mt-3 pt-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold" style="color:var(--pp-navy);font-family:var(--pp-font)">
                                {{ __('appointment.total') }}
                            </span>
                            <span class="fw-bold" style="color:var(--pp-teal);font-size:1.05rem;font-family:var(--pp-font)">
                                {{ Currency::format($grand_total) }}
                            </span>
                        </div>

                        @if($advancePaid)
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size:0.82rem;color:var(--pp-muted)">
                                    {{ __('frontend.advance_paid_amount') }} ({{ $appointment->advance_payment_amount }}%)
                                </span>
                                <span style="font-size:0.82rem;font-weight:600;color:var(--pp-confirmed)">
                                    {{ Currency::format($appointment->advance_paid_amount) }}
                                </span>
                            </div>

                            @if($appointment->status != 'cancelled')
                                <div class="d-flex justify-content-between align-items-center">
                                    <span style="font-size:0.82rem;color:var(--pp-muted)">
                                        {{ __('frontend.remaining_amount') }}
                                        @if(optional($appointment->appointmenttransaction)->payment_status == 1)
                                            <span class="pp-pay-status pp-pay-paid ms-1">{{ __('frontend.paid') }}</span>
                                        @else
                                            <span class="pp-pay-status pp-pay-pending ms-1">{{ __('frontend.pending') }}</span>
                                        @endif
                                    </span>
                                    <span style="font-size:0.82rem;font-weight:600;color:var(--pp-navy)">
                                        {{ Currency::format($grand_total - $appointment->advance_paid_amount) }}
                                    </span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- ── Pay now button ── --}}
                @if($advancePaid && $appointment->status == 'check_in' && optional($appointment->appointmenttransaction)->payment_status == 0 && optional($appointment->patientEncounter)->status == 1)
                    <div class="text-end mt-3">
                        <a href="#" class="pp-btn pp-btn-teal" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            {{ __('frontend.pay_now') }} {{ Currency::format($grand_total - $appointment->advance_paid_amount) }}
                        </a>
                    </div>
                @elseif($appointment->status == 'check_in' && optional($appointment->appointmenttransaction)->payment_status == 0 && optional($appointment->patientEncounter)->status == 1)
                    <div class="text-end mt-3">
                        <a href="#" class="pp-btn pp-btn-teal" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            {{ __('frontend.pay_now') }} {{ Currency::format($grand_total) }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     ENCOUNTER DETAILS MODAL
     ══════════════════════════════════════════════════ --}}
<div class="modal modal-xl fade pp-modal" id="encounter-details-view" tabindex="-1" aria-labelledby="encounterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="encounterModalLabel">
                    <i class="ph ph-stethoscope me-2" aria-hidden="true"></i>
                    {{ __('frontend.encounter_details') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem">

                {{-- Basic info --}}
                <div style="background:var(--pp-navy-50);border:1px solid var(--pp-border);border-radius:var(--pp-r);padding:1rem;margin-bottom:1.25rem">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="pp-detail-label" style="margin-bottom:0">{{ __('frontend.appointment_id') }}</span>
                                <span class="pp-detail-id" style="color:var(--pp-teal)">#{{ $appointment->id }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="pp-detail-label" style="margin-bottom:0">{{ __('frontend.doctor_name') }}</span>
                                <span class="fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                    {{ optional($appointment->doctor)->first_name . ' ' . optional($appointment->doctor)->last_name ?? '-' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="pp-detail-label" style="margin-bottom:0">{{ __('frontend.clinic_name') }}</span>
                                <span class="fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                    {{ optional($appointment->cliniccenter)->name ?? '-' }}
                                </span>
                            </div>
                        </div>
                        @if(optional($appointment->patientEncounter)->status)
                            <span class="pp-status pp-status-confirmed">{{ __('frontend.active') ?? 'Active' }}</span>
                        @else
                            <span class="pp-status pp-status-cancelled">{{ __('frontend.closed') ?? 'Closed' }}</span>
                        @endif
                    </div>
                    @if(optional($appointment->patientEncounter)->descrtiption)
                        <div class="border-top mt-3 pt-3">
                            <span class="pp-detail-label">{{ __('frontend.description') }}</span>
                            <p class="mb-0 fw-semibold" style="font-size:0.85rem;color:var(--pp-navy)">
                                {{ optional($appointment->patientEncounter)->descrtiption }}
                            </p>
                        </div>
                    @endif
                </div>

                @php
                    $problems     = $medical_history->get('encounter_problem',      collect());
                    $observations = $medical_history->get('encounter_observations', collect());
                    $notes        = $medical_history->get('encounter_notes',        collect());
                @endphp

                {{-- Problems --}}
                <div class="pp-encounter-section mb-3">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('frontend.problem') }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if($problems->isNotEmpty())
                            @foreach($problems as $problem)
                                <p class="mb-1" style="font-size:0.85rem">{{ $loop->iteration }}. {{ $problem->title }}</p>
                            @endforeach
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-cancelled)">
                                {{ __('frontend.no_problems_found') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Observations --}}
                <div class="pp-encounter-section mb-3">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('frontend.observation') }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if($observations->isNotEmpty())
                            @foreach($observations as $observation)
                                <p class="mb-1" style="font-size:0.85rem">{{ $loop->iteration }}. {{ $observation->title }}</p>
                            @endforeach
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-cancelled)">
                                {{ __('frontend.no_observation_found') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Notes --}}
                <div class="pp-encounter-section mb-3">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('frontend.notes') }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if($notes->isNotEmpty())
                            @foreach($notes as $note)
                                <p class="mb-1" style="font-size:0.85rem">{{ $loop->iteration }}. {{ $note->title }}</p>
                            @endforeach
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-cancelled)">
                                {{ __('frontend.no_note_found') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Medical report --}}
                <div class="pp-encounter-section mb-3">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('appointment.medical_report') ?? 'Medical Report' }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if ($medical_report && $medical_report->file_url)
                            <a href="{{ asset($medical_report->file_url) }}" download
                               class="pp-btn pp-btn-teal" style="font-size:0.82rem">
                                <i class="ph ph-download-simple" aria-hidden="true"></i>
                                {{ __('messages.lbl_download') ?? 'Download Report' }}
                            </a>
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-muted)">
                                {{ __('messages.no_data_found') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Body chart --}}
                <div class="pp-encounter-section mb-3">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('frontend.body_chart') ?? 'Body Chart' }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if ($bodychart->isNotEmpty())
                            <div class="d-flex flex-wrap gap-3">
                                @foreach ($bodychart as $chart)
                                    @foreach ($chart->media as $media)
                                        <div class="text-center">
                                            <img src="{{ asset($media->getUrl()) }}" alt="{{ $media->name }}"
                                                 style="width:100px;height:100px;object-fit:cover;border-radius:var(--pp-r);margin-bottom:0.5rem">
                                            <div>
                                                <a href="{{ asset($media->getUrl()) }}" download
                                                   class="pp-btn pp-btn-outline" style="font-size:0.72rem;padding:0.25rem 0.625rem">
                                                    <i class="ph ph-download-simple" aria-hidden="true"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-cancelled)">
                                {{ __('messages.no_data_found') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- Prescriptions --}}
                <div class="pp-encounter-section mb-3">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('frontend.prescription') }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if($prescriptions->isNotEmpty())
                            @foreach($prescriptions as $prescription)
                                <div class="pp-rx-item">
                                    <p class="pp-rx-name">{{ $prescription->name }}</p>
                                    @if($prescription->instruction)
                                        <p class="mb-2" style="font-size:0.82rem;color:var(--pp-muted)">
                                            {{ $prescription->instruction }}
                                        </p>
                                    @endif
                                    <div class="d-flex gap-2 flex-wrap mt-2">
                                        <span class="pp-rx-chip">
                                            <i class="ph ph-clock" aria-hidden="true"></i>
                                            {{ $prescription->frequency }}
                                        </span>
                                        <span class="pp-rx-chip">
                                            <i class="ph ph-calendar-blank" aria-hidden="true"></i>
                                            {{ $prescription->duration }} {{ __('frontend.days') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-cancelled)">
                                {{ __('frontend.no_prescription_found') }}
                            </p>
                        @endif
                    </div>
                </div>

                {{-- SOAP --}}
                <div class="pp-encounter-section">
                    <button type="button"
                            class="pp-encounter-toggle d-flex justify-content-between align-items-center w-100"
                            onclick="window.toggleEncSection && window.toggleEncSection(this)"
                            aria-expanded="false">
                        <span>{{ __('frontend.soap') }}</span>
                        <i class="ph ph-caret-down" aria-hidden="true"></i>
                    </button>
                    <div class="pp-encounter-body" hidden>
                        @if($soap)
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <span class="pp-detail-label">{{ __('frontend.subjective') }}</span>
                                    <p class="mb-0" style="font-size:0.85rem;color:var(--pp-navy)">{{ $soap->subjective }}</p>
                                </div>
                                <div class="col-md-6">
                                    <span class="pp-detail-label">{{ __('frontend.objective') }}</span>
                                    <p class="mb-0" style="font-size:0.85rem;color:var(--pp-navy)">{{ $soap->objective }}</p>
                                </div>
                                <div class="col-md-6">
                                    <span class="pp-detail-label">{{ __('frontend.assessment') }}</span>
                                    <p class="mb-0" style="font-size:0.85rem;color:var(--pp-navy)">{{ $soap->assessment }}</p>
                                </div>
                                <div class="col-md-6">
                                    <span class="pp-detail-label">{{ __('frontend.plan') }}</span>
                                    <p class="mb-0" style="font-size:0.85rem;color:var(--pp-navy)">{{ $soap->plan }}</p>
                                </div>
                            </div>
                        @else
                            <p class="mb-0 text-center" style="font-size:0.82rem;color:var(--pp-cancelled)">
                                {{ __('frontend.no_soap_found') }}
                            </p>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     TAX DETAILS MODAL
     ══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal" id="taxDetailsModal" tabindex="-1"
     aria-labelledby="taxDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taxDetailsModalLabel">
                    <i class="ph ph-receipt me-2" aria-hidden="true"></i>
                    {{ __('frontend.tax_detail') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body" style="padding:1.25rem">
                <ul class="list-unstyled mb-3">
                    @foreach($tax_percentage as $tax)
                        @php
                            $tax_amount = ($tax['type'] == 'percent' && $sub_total > 0)
                                ? ($sub_total * $tax['value']) / 100
                                : $tax['value'];
                        @endphp
                        <li class="d-flex justify-content-between gap-3 mb-2">
                            <span style="font-size:0.85rem;color:var(--pp-navy);font-weight:600">
                                {{ $tax['title'] }}
                                @if($tax['type'] == 'percent')({{ $tax['value'] }}%)@endif
                            </span>
                            <span style="font-size:0.85rem;color:var(--pp-muted)" id="{{ strtolower(str_replace(' ', '', $tax['title'])) }}">
                                {{ Currency::format($tax_amount) ?? '--' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                <div class="border-top pt-3 d-flex justify-content-between">
                    <span class="fw-bold" style="color:var(--pp-navy)">{{ __('frontend.total_tax') }}</span>
                    <span class="fw-bold" style="color:var(--pp-teal)" id="totalTaxAmount">
                        {{ Currency::format($total_tax) ?? '--' }}
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">
                    {{ __('frontend.close') ?? 'Close' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     PAYMENT METHOD MODAL
     ══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal" id="paymentModal" tabindex="-1"
     aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">
                    <i class="ph ph-credit-card me-2" aria-hidden="true"></i>
                    {{ __('frontend.payment_method') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body" style="padding:1.25rem">
                <div style="border:1px solid var(--pp-border);border-radius:var(--pp-r-lg);overflow:hidden">
                    @foreach ($paymentMethods as $method)
                        <label class="d-flex justify-content-between align-items-center gap-3 p-3 cursor-pointer"
                               style="border-bottom:1px solid var(--pp-border);margin:0;cursor:pointer;transition:background 0.15s"
                               for="method-{{ $method }}"
                               onmouseover="this.style.background='var(--pp-navy-50)'"
                               onmouseout="this.style.background=''">
                            <div class="d-flex gap-2 align-items-center">
                                <img src="{{ asset('dummy-images/payment_icons/' . strtolower($method) . '.svg') }}"
                                     alt="{{ $method }}"
                                     style="width:20px;height:20px">
                                <span class="fw-semibold" style="font-size:0.9rem;color:var(--pp-navy);font-family:var(--pp-font)">
                                    {{ $method }}
                                </span>
                            </div>
                            <input class="form-check-input" type="radio" name="payment_method"
                                   value="{{ $method }}" id="method-{{ $method }}"
                                   style="accent-color:var(--pp-teal)"
                                   @if ($method === 'cash') checked @endif>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">
                    {{ __('frontend.cancel') ?? 'Cancel' }}
                </button>
                <button class="pp-btn pp-btn-teal" id="pay_now" data-bs-dismiss="modal">
                    {{ __('frontend.submit') }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    'use strict';

    /* ── Encounter accordion (defined with guard so encounters.blade.php can share it) ── */
    window.toggleEncSection = window.toggleEncSection || function (btn) {
        var body    = btn.nextElementSibling;
        var icon    = btn.querySelector('.ph');
        var isOpen  = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', String(!isOpen));
        if (isOpen) {
            body.hidden = true;
            icon.classList.replace('ph-caret-up', 'ph-caret-down');
        } else {
            body.hidden = false;
            icon.classList.replace('ph-caret-down', 'ph-caret-up');
        }
    };

    /* ── Delete rating ── */
    document.querySelectorAll('.delete-rating-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var reviewId = btn.dataset.reviewId;
            Swal.fire({
                title: '{{ __("frontend.delete_review_confirm_title") ?? "Remove Review?" }}',
                text: '{{ __("frontend.delete_review_confirm_msg") ?? "Once deleted, your review cannot be recovered." }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: 'var(--pp-cancelled)',
                cancelButtonColor: 'var(--pp-muted)',
                confirmButtonText: '{{ __("frontend.delete") ?? "Delete" }}',
                cancelButtonText: '{{ __("frontend.cancel") ?? "Cancel" }}'
            }).then(function (result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('delete-rating') }}',
                        method: 'POST',
                        data: { _token: '{{ csrf_token() }}', id: reviewId },
                        success: function (data) {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __("messages.deleted") ?? "Deleted!" }}',
                                text: data.message,
                                confirmButtonColor: 'var(--pp-teal)'
                            }).then(function () { location.reload(); });
                        },
                        error: function () {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __("messages.error") ?? "Error" }}',
                                text: '{{ __("messages.something_went_wrong") ?? "Something went wrong. Please try again." }}',
                                confirmButtonColor: 'var(--pp-teal)'
                            });
                        }
                    });
                }
            });
        });
    });

    /* ── Payment success flash (from session) ── */
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('paymentDetails'))
            var pd = @json(session('paymentDetails'));
            Swal.fire({
                title: '{{ __("frontend.payment_success") ?? "Payment Successful" }}',
                html: '<p>Your appointment with <strong>Dr. ' + pd.doctorName + '</strong> at '
                    + '<strong>' + pd.clinicName + '</strong> has been confirmed on '
                    + '<strong>' + new Date(pd.appointmentDate).toLocaleDateString() + '</strong> at '
                    + '<strong>' + new Date('1970-01-01T' + pd.appointmentTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + '</strong>.</p>'
                    + '<p><strong>Booking ID:</strong> #' + pd.bookingId + '</p>'
                    + '<p><strong>Payment via:</strong> ' + pd.paymentVia + '</p>'
                    + '<p><strong>Total:</strong> ' + pd.currency + ' ' + pd.totalAmount + '</p>',
                icon: 'success',
                confirmButtonText: '{{ __("messages.close") ?? "Close" }}',
                confirmButtonColor: 'var(--pp-teal)',
                allowOutsideClick: false
            }).then(function (result) {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('appointment-list') }}';
                }
            });
        @endif
    });

    /* ── Pay now ── */
    document.querySelector('#pay_now').addEventListener('click', async function () {
        var appointmentId        = '{{ $appointment->id }}';
        var selectedPaymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        var baseUrl              = '{{ url('/') }}';
        var totalAmount          = parseFloat('{{ $appointment->total_amount }}');
        var advancePaymentAmount = parseFloat('{{ $appointment->advance_payment_amount }}');
        var advancePaymentStatus = parseInt('{{ $appointment->advance_payment_status }}');

        if (selectedPaymentMethod === 'Wallet') {
            try {
                var walletRes = await fetch('{{ route('check.wallet.balance') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        totalAmount: advancePaymentStatus === 1 ? advancePaymentAmount : totalAmount
                    })
                });
                var walletData = await walletRes.json();
                var required   = advancePaymentStatus === 1 ? advancePaymentAmount : totalAmount;
                if (!walletData.success || walletData.balance < required) {
                    successSnackbar('{{ __("messages.insufficient_wallet_balance") ?? "Insufficient balance. Please add funds." }}');
                    return;
                }
            } catch (err) {
                console.error('Wallet check error:', err);
                return;
            }
        }

        fetch(baseUrl + '/pay-now', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                appointment_id:   appointmentId,
                transaction_type: selectedPaymentMethod
            })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.redirect) {
                window.location.href = data.redirect;
            } else if (data.status) {
                if (selectedPaymentMethod === 'Wallet') {
                    var pd = {
                        doctorName:      '{{ optional($appointment->doctor)->first_name }} {{ optional($appointment->doctor)->last_name }}',
                        clinicName:      '{{ optional($appointment->cliniccenter)->name }}',
                        appointmentDate: '{{ $appointment->appointment_date }}',
                        appointmentTime: '{{ $appointment->appointment_time }}',
                        bookingId:       appointmentId,
                        paymentVia:      selectedPaymentMethod,
                        currency:        '{{ $appointment->currency_symbol }}',
                        totalAmount:     (advancePaymentStatus === 1 ? advancePaymentAmount : totalAmount).toFixed(2)
                    };
                    Swal.fire({
                        title: '{{ __("frontend.payment_success") ?? "Payment Successful" }}',
                        html: '<p>Your appointment with <strong>Dr. ' + pd.doctorName + '</strong> at '
                            + '<strong>' + pd.clinicName + '</strong> confirmed on '
                            + '<strong>' + new Date(pd.appointmentDate).toLocaleDateString() + '</strong> at '
                            + '<strong>' + new Date('1970-01-01T' + pd.appointmentTime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + '</strong>.</p>'
                            + '<p><strong>Booking ID:</strong> #' + pd.bookingId + '</p>'
                            + '<p><strong>Payment via:</strong> ' + pd.paymentVia + '</p>'
                            + '<p><strong>Total:</strong> ' + pd.currency + ' ' + pd.totalAmount + '</p>',
                        icon: 'success',
                        confirmButtonText: '{{ __("messages.close") ?? "Close" }}',
                        confirmButtonColor: 'var(--pp-teal)',
                        allowOutsideClick: false
                    }).then(function () {
                        window.location.href = baseUrl + '/appointment-list';
                    });
                } else {
                    window.location.href = baseUrl + '/appointment-list';
                }
            } else {
                alert(data.message || '{{ __("messages.payment_failed") ?? "Payment failed." }}');
            }
        })
        .catch(function (err) {
            console.error('Payment error:', err);
            alert('{{ __("messages.something_went_wrong") ?? "An error occurred during payment processing." }}');
        });
    });
}());
</script>
@endpush
