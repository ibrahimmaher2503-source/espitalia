@extends('frontend::layouts.master')
@section('title', $test->name)

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">
            <div class="row g-4">

                {{-- ── Sidebar (col-lg-3) ── --}}
                <div class="col-lg-3">
                    <div class="pp-detail-section" style="position:sticky;top:5rem">

                        {{-- Icon header --}}
                        <div class="pp-detail-section-header" style="justify-content:center;padding:1.75rem;flex-direction:column;gap:0.75rem">
                            <div style="width:64px;height:64px;border-radius:var(--pp-r);background:var(--pp-teal-100);display:flex;align-items:center;justify-content:center;font-size:1.75rem;color:var(--pp-teal)" aria-hidden="true">
                                <i class="ph ph-test-tube"></i>
                            </div>
                        </div>

                        <div class="pp-detail-section-body">
                            {{-- Name + badges --}}
                            <p class="pp-section-title mb-1">{{ $test->name }}</p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <span class="pp-badge">{{ $test->code }}</span>
                                {!! $test->department_badge !!}
                            </div>

                            {{-- Key details --}}
                            <div class="mb-3">
                                <div class="pp-summary-row">
                                    <span class="pp-summary-label">
                                        <i class="ph ph-tag me-1" style="color:var(--pp-teal)" aria-hidden="true"></i>{{ __('radiology_lab.lbl_category') }}
                                    </span>
                                    <span class="pp-summary-value">{{ $test->category ? $test->category->name : '-' }}</span>
                                </div>
                                <div class="pp-summary-row">
                                    <span class="pp-summary-label">
                                        <i class="ph ph-test-tube me-1" style="color:var(--pp-teal)" aria-hidden="true"></i>{{ __('radiology_lab.lbl_sample_type') }}
                                    </span>
                                    <span class="pp-summary-value">{{ $test->sample_type_label }}</span>
                                </div>
                                <div class="pp-summary-row">
                                    <span class="pp-summary-label">
                                        <i class="ph ph-clock me-1" style="color:var(--pp-teal)" aria-hidden="true"></i>{{ __('radiology_lab.lbl_turnaround_time') }}
                                    </span>
                                    <span class="pp-summary-value">{{ $test->turnaround_time ?? '-' }}</span>
                                </div>
                            </div>

                            {{-- Price pill --}}
                            <div class="pp-price-pill mb-3">
                                <i class="ph ph-currency-dollar" aria-hidden="true"></i>
                                <span class="pp-price-pill-amount">{{ number_format($test->default_price, 2) }}</span>
                            </div>

                            {{-- CTA --}}
                            @auth
                                <a href="{{ route('order-tests', ['test_id' => $test->id]) }}"
                                   class="pp-btn pp-btn-teal w-100 mb-2">
                                    <i class="ph ph-shopping-cart" aria-hidden="true"></i>
                                    {{ __('radiology_lab.lbl_order_this_test') }}
                                </a>
                            @else
                                <a href="{{ route('login-page') }}"
                                   class="pp-btn pp-btn-teal w-100 mb-2">
                                    <i class="ph ph-sign-in" aria-hidden="true"></i>
                                    {{ __('messages.login') }}
                                </a>
                            @endauth
                            <a href="{{ route('lab-tests') }}" class="pp-btn pp-btn-outline w-100">
                                <i class="ph ph-arrow-left" aria-hidden="true"></i>
                                {{ __('radiology_lab.lbl_back_to_catalog') }}
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ── Main content (col-lg-9) ── --}}
                <div class="col-lg-9">

                    {{-- Description --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                            <span>{{ __('radiology_lab.lbl_description') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <p style="color:var(--pp-muted);font-size:0.875rem;margin:0;font-family:var(--pp-font);line-height:1.6">
                                {{ $test->description ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- Test information --}}
                    <div class="pp-detail-section mb-3">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-info" aria-hidden="true"></i>
                            <span>{{ __('radiology_lab.lbl_test_information') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="pp-detail-grid">
                                <div>
                                    <p class="pp-detail-label">{{ __('radiology_lab.lbl_category') }}</p>
                                    <p class="pp-detail-value">{{ $test->category ? $test->category->name : '-' }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('radiology_lab.lbl_department') }}</p>
                                    <div style="margin-top:2px">{!! $test->department_badge !!}</div>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('radiology_lab.lbl_test_code') }}</p>
                                    <span class="pp-badge">{{ $test->code }}</span>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('radiology_lab.lbl_sample_type') }}</p>
                                    <p class="pp-detail-value">{{ $test->sample_type_label }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('radiology_lab.lbl_turnaround_time') }}</p>
                                    <p class="pp-detail-value">{{ $test->turnaround_time ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="pp-detail-label">{{ __('radiology_lab.lbl_price') }}</p>
                                    <p class="pp-detail-value" style="color:var(--pp-teal);font-size:1rem">{{ number_format($test->default_price, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Preparation instructions (conditional) --}}
                    @if($test->preparation_instructions)
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                                <span>{{ __('radiology_lab.lbl_preparation_instructions') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="pp-info-block">
                                    <i class="ph ph-info" aria-hidden="true"></i>
                                    <span>{{ $test->preparation_instructions }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
