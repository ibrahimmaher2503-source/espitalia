@extends('frontend::layouts.master')
@section('title', $hospital->name)

@section('content')
    @include('frontend::components.section.breadcrumb')
    <div class="list-page section-spacing px-0">
        <div class="container">
            <div class="row">

                {{-- ── Sidebar (col-lg-3) ──────────────────────────────────────── --}}
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <div class="doctor-details-box rounded section-bg">

                        {{-- Logo header --}}
                        <div class="text-center py-5 section-bg rounded-top">
                            @if($hospital->getFirstMediaUrl('logo'))
                                <img src="{{ $hospital->getFirstMediaUrl('logo') }}"
                                     alt="{{ $hospital->name }}"
                                     class="rounded"
                                     style="max-height:100px;max-width:180px;object-fit:contain;">
                            @else
                                <i class="ph ph-hospital" style="font-size:72px;color:var(--bs-primary);"></i>
                            @endif
                        </div>

                        <div class="p-3">
                            {{-- Name + badges --}}
                            <h6 class="mb-2">{{ $hospital->name }}</h6>
                            <div class="mb-3">
                                <span class="badge bg-dark">{{ __('intensive_care.type_' . $hospital->hospital_type) }}</span>
                            </div>

                            {{-- Rating --}}
                            <div class="mb-3">
                                @php $rating = floatval($hospital->rating); @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= floor($rating))
                                        <i class="ph-fill ph-star text-warning"></i>
                                    @elseif ($i - $rating < 1 && $i - $rating > 0)
                                        <i class="ph-fill ph-star-half text-warning"></i>
                                    @else
                                        <i class="ph ph-star text-muted"></i>
                                    @endif
                                @endfor
                                <span class="ms-1 text-muted font-size-14">({{ number_format($rating, 1) }})</span>
                            </div>

                            {{-- Key details list --}}
                            <div class="mt-3">
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-map-pin flex-shrink-0"></i>
                                    <span>{{ $hospital->address }}, {{ $hospital->city }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-3 other-details-list">
                                    <i class="ph ph-phone flex-shrink-0"></i>
                                    <span>{{ $hospital->phone }}</span>
                                </div>
                                @if($hospital->email)
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-envelope flex-shrink-0"></i>
                                        <span>{{ $hospital->email }}</span>
                                    </div>
                                @endif
                                @if($hospital->website)
                                    <div class="d-flex align-items-center gap-3 other-details-list">
                                        <i class="ph ph-globe flex-shrink-0"></i>
                                        <a href="{{ $hospital->website }}" target="_blank" class="text-primary">{{ $hospital->website }}</a>
                                    </div>
                                @endif
                            </div>

                            {{-- Accepted insurance --}}
                            @if($hospital->accepted_insurance && count($hospital->accepted_insurance) > 0)
                                <div class="mt-3">
                                    <p class="text-muted font-size-14 mb-2">{{ __('intensive_care.lbl_accepted_insurance') }}</p>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($hospital->accepted_insurance as $insurance)
                                            <span class="badge bg-light text-dark border">{{ $insurance }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- CTA buttons --}}
                            <div class="mt-3">
                                @auth
                                    <a href="{{ route('request-icu-admission', ['hospital_id' => $hospital->id]) }}"
                                       class="btn btn-primary w-100 mb-2">
                                        <i class="ph ph-bed me-1"></i>{{ __('intensive_care.lbl_request_admission') }}
                                    </a>
                                @else
                                    <a href="{{ route('login-page') }}"
                                       class="btn btn-outline-primary w-100 mb-2">
                                        <i class="ph ph-sign-in me-1"></i>{{ __('messages.login') }}
                                    </a>
                                @endauth

                                <a href="{{ route('icu-hospitals') }}"
                                   class="btn btn-outline-secondary w-100">
                                    <i class="ph ph-arrow-left me-1"></i>{{ __('intensive_care.hospital_catalog') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Main content (col-lg-9) ─────────────────────────────────── --}}
                <div class="col-lg-9">

                    {{-- Section 1 - Description --}}
                    <div class="section-bg rounded p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="ph ph-clipboard-text text-primary" style="font-size:20px;"></i>
                            <h5 class="m-0">{{ __('intensive_care.lbl_description') }}</h5>
                        </div>
                        <p class="text-muted mb-0">{{ $hospital->description ?? '-' }}</p>
                    </div>

                    {{-- Section 2 - ICU Departments table --}}
                    <div class="section-bg rounded p-4 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i class="ph ph-buildings text-primary" style="font-size:20px;"></i>
                            <h5 class="m-0">{{ __('intensive_care.department_plural') }}</h5>
                        </div>

                        @if($hospital->icuDepartments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('intensive_care.lbl_department_name') }}</th>
                                            <th>{{ __('intensive_care.lbl_specialty_type') }}</th>
                                            <th>{{ __('intensive_care.lbl_available_beds') }} / {{ __('intensive_care.lbl_total_beds') }}</th>
                                            <th>{{ __('intensive_care.lbl_equipment_level') }}</th>
                                            <th class="text-center">{{ __('intensive_care.lbl_needs_ventilator') }}</th>
                                            <th class="text-center">{{ __('intensive_care.lbl_needs_oxygen') }}</th>
                                            <th>{{ __('intensive_care.lbl_daily_price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hospital->icuDepartments as $dept)
                                            <tr>
                                                <td class="fw-semibold">{{ $dept->name }}</td>
                                                <td>
                                                    <span class="badge bg-info">{{ __('intensive_care.case_' . $dept->specialty_type) }}</span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold {{ $dept->available_beds > 0 ? 'text-success' : 'text-danger' }}">{{ $dept->available_beds }}</span>
                                                    / {{ $dept->total_beds }}
                                                </td>
                                                <td>
                                                    <span class="badge {{ $dept->equipment_level === 'full' ? 'bg-success' : ($dept->equipment_level === 'advanced' ? 'bg-primary' : 'bg-secondary') }}">
                                                        {{ __('intensive_care.equipment_' . $dept->equipment_level) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    @if($dept->has_ventilator)
                                                        <i class="ph-fill ph-check-circle text-success" style="font-size:18px;"></i>
                                                    @else
                                                        <i class="ph ph-x-circle text-muted" style="font-size:18px;"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($dept->has_oxygen)
                                                        <i class="ph-fill ph-check-circle text-success" style="font-size:18px;"></i>
                                                    @else
                                                        <i class="ph ph-x-circle text-muted" style="font-size:18px;"></i>
                                                    @endif
                                                </td>
                                                <td class="fw-bold text-primary">{{ number_format($dept->daily_price, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">{{ __('intensive_care.lbl_no_results') }}</p>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
