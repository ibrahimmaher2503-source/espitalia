@extends('frontend::layouts.master')
@section('title', ($nurse->user->first_name ?? '') . ' ' . ($nurse->user->last_name ?? ''))

@section('content')
    @include('frontend::components.section.breadcrumb')
    <div class="list-page section-spacing px-0">
        <div class="container">
            <div class="row">
                {{-- Sidebar --}}
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <div class="doctor-details-box rounded section-bg">
                        @php
                            $profileImage = $nurse->user->profile_image ?? asset('img/default-avatar.png');
                        @endphp
                        <img src="{{ $profileImage }}" alt="{{ $nurse->user->first_name ?? '' }} {{ $nurse->user->last_name ?? '' }}"
                            class="w-100 doctor-details-img object-cover rounded-top">

                        <div class="p-3">
                            {{-- Availability status pill --}}
                            @php
                                $statusClass = 'text-success bg-success-subtle';
                                $statusIcon = 'text-success';
                                if ($nurse->availability_status === 'busy') {
                                    $statusClass = 'text-warning bg-warning-subtle';
                                    $statusIcon = 'text-warning';
                                } elseif ($nurse->availability_status === 'off_duty') {
                                    $statusClass = 'text-secondary bg-secondary-subtle';
                                    $statusIcon = 'text-secondary';
                                }
                                $statusLabel = match($nurse->availability_status) {
                                    'available' => __('request_nurse.status_available'),
                                    'busy'      => __('request_nurse.status_busy'),
                                    default     => __('request_nurse.status_off_duty'),
                                };
                            @endphp

                            <div class="d-flex align-items-center gap-3 mb-1">
                                <div class="d-flex align-items-center gap-1 rounded-pill px-3 py-1 rate-meta-box {{ $statusClass }}">
                                    <i class="ph ph-circle-fill {{ $statusIcon }}" style="font-size:8px;"></i>
                                    <small>{{ $statusLabel }}</small>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-2 mt-2 mb-3">
                                <h6 class="m-0">{{ $nurse->user->first_name ?? '' }} {{ $nurse->user->last_name ?? '' }}</h6>
                                <i class="ph-fill ph-seal-check text-success"></i>
                            </div>

                            {{-- Info rows --}}
                            <div class="mt-3">
                                @if(!empty($nurse->specialization))
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-first-aid text-primary flex-shrink-0"></i>
                                        <span class="font-size-14">{{ $nurse->specialization }}</span>
                                    </div>
                                @endif

                                @if(!empty($nurse->experience))
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-clock text-primary flex-shrink-0"></i>
                                        <span class="font-size-14">{{ $nurse->experience }}</span>
                                    </div>
                                @endif

                                @if(!empty($nurse->service_area))
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-map-pin text-primary flex-shrink-0"></i>
                                        <span class="font-size-14">{{ $nurse->service_area }}</span>
                                    </div>
                                @endif

                                @if(!empty($nurse->hourly_rate))
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-currency-dollar text-primary flex-shrink-0"></i>
                                        <span class="font-size-14 fw-bold">{{ number_format($nurse->hourly_rate, 2) }}{{ __('request_nurse.lbl_per_hour') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Action buttons --}}
                            <div class="mt-4 d-grid gap-2">
                                @if($nurse->availability_status === 'available')
                                    <a href="{{ route('request-nurse', ['nurse_id' => $nurse->nurse_id]) }}"
                                        class="btn btn-primary w-100">
                                        <i class="ph ph-first-aid me-1"></i>
                                        {{ __('request_nurse.lbl_request_this_nurse') }}
                                    </a>
                                @endif
                                <a href="{{ route('available-nurses') }}" class="btn btn-outline-secondary w-100">
                                    <i class="ph ph-arrow-left me-1"></i>
                                    {{ __('messages.back') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="col-lg-9">
                    {{-- Unavailability alert --}}
                    @if($nurse->availability_status !== 'available')
                        <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                            <i class="ph ph-warning-circle flex-shrink-0 fs-5"></i>
                            <div>
                                @if($nurse->availability_status === 'busy')
                                    {{ __('request_nurse.status_busy') }} &mdash; {{ __('request_nurse.nurse_busy_alert') }}
                                @else
                                    {{ __('request_nurse.status_off_duty') }} &mdash; {{ __('request_nurse.nurse_off_duty_alert') }}
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- About --}}
                    <div class="section-bg rounded p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="ph ph-user-circle text-primary fs-5"></i>
                            <h5 class="m-0">{{ __('request_nurse.lbl_about') }}</h5>
                        </div>
                        <p class="text-muted mb-0">{{ $nurse->about ?? '-' }}</p>
                    </div>

                    {{-- Key Info Grid --}}
                    <div class="section-bg rounded p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="ph ph-info text-primary fs-5"></i>
                            <h5 class="m-0">{{ __('request_nurse.lbl_quick_info') }}</h5>
                        </div>
                        <div class="row gy-3">
                            <div class="col-sm-6 col-lg-3">
                                <div class="text-muted font-size-14 mb-1">{{ __('request_nurse.lbl_specialization') }}</div>
                                <div class="fw-semibold">{{ $nurse->specialization ?? '-' }}</div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="text-muted font-size-14 mb-1">{{ __('request_nurse.lbl_experience') }}</div>
                                <div class="fw-semibold">{{ $nurse->experience ?? '-' }}</div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="text-muted font-size-14 mb-1">{{ __('request_nurse.lbl_hourly_rate') }}</div>
                                <div class="fw-bold text-primary">
                                    @if(!empty($nurse->hourly_rate))
                                        {{ number_format($nurse->hourly_rate, 2) }}{{ __('request_nurse.lbl_per_hour') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="text-muted font-size-14 mb-1">{{ __('request_nurse.lbl_service_area') }}</div>
                                <div class="fw-semibold">{{ $nurse->service_area ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Request CTA for available nurse (mobile / below-fold) --}}
                    @if($nurse->availability_status === 'available')
                        <div class="section-bg rounded p-4 d-lg-none">
                            <a href="{{ route('request-nurse', ['nurse_id' => $nurse->nurse_id]) }}"
                                class="btn btn-primary btn-lg w-100">
                                <i class="ph ph-first-aid me-1"></i>
                                {{ __('request_nurse.lbl_request_this_nurse') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
