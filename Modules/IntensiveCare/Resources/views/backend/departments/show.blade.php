@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: #374151; font-weight: 600;">
                    <i class="ph ph-bed me-1"></i> {{ $department->name }}
                </h5>
                <div class="d-flex gap-2">
                    @can('edit_icu_departments')
                    <a href="{{ route('backend.icu-departments.create', ['id' => $department->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ph ph-pencil-simple-line"></i> {{ __('messages.edit') }}
                    </a>
                    @endcan
                    <a href="{{ route('backend.icu-departments.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_hospital_name') }}</span>
                        <span class="fw-medium">{{ $department->hospital->name ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_specialty_type') }}</span>
                        <span class="fw-medium">{{ __('intensive_care.case_' . $department->specialty_type) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_equipment_level') }}</span>
                        @php $eqColors = ['basic' => 'secondary', 'advanced' => 'primary', 'full' => 'success']; @endphp
                        <span class="badge bg-{{ $eqColors[$department->equipment_level] ?? 'secondary' }}">{{ __('intensive_care.equipment_' . $department->equipment_level) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_daily_price') }}</span>
                        <span class="fw-bold" style="color:#6366f1;">{{ number_format($department->daily_price, 2) }}</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_total_beds') }}</span>
                        <span class="fw-medium">{{ $department->total_beds }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_available_beds') }}</span>
                        @php $color = $department->available_beds > 0 ? 'success' : 'danger'; @endphp
                        <span class="badge bg-{{ $color }}">{{ $department->available_beds }} / {{ $department->total_beds }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_needs_ventilator') }}</span>
                        <span>{!! $department->has_ventilator ? '<i class="ph ph-check-circle text-success fs-5"></i>' : '<i class="ph ph-x-circle text-danger fs-5"></i>' !!}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_needs_oxygen') }}</span>
                        <span>{!! $department->has_oxygen ? '<i class="ph ph-check-circle text-success fs-5"></i>' : '<i class="ph ph-x-circle text-danger fs-5"></i>' !!}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('intensive_care.lbl_status') }}</span>
                        <span>{!! $department->status ? '<span class="badge bg-success">' . __('messages.active') . '</span>' : '<span class="badge bg-danger">' . __('messages.inactive') . '</span>' !!}</span>
                    </div>
                </div>
            </div>
            @if($department->description)
                <hr>
                <div>
                    <h6 class="mb-2">{{ __('intensive_care.lbl_description') }}</h6>
                    <p class="text-muted">{{ $department->description }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
