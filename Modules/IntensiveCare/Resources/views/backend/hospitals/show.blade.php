@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="color: #374151; font-weight: 600;">
                    <i class="ph ph-buildings me-1"></i> {{ $hospital->name }}
                </h5>
                <div class="d-flex gap-2">
                    @can('edit_hospitals')
                    <a href="{{ route('backend.hospitals.create', ['id' => $hospital->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ph ph-pencil-simple-line"></i> {{ __('messages.edit') }}
                    </a>
                    @endcan
                    <a href="{{ route('backend.hospitals.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4">
                    {{-- Logo --}}
                    @if($hospital->getFirstMediaUrl('logo'))
                        <div class="text-center mb-3">
                            <img src="{{ $hospital->getFirstMediaUrl('logo') }}" alt="{{ $hospital->name }}" class="img-fluid" style="max-height: 120px; border-radius: 12px;">
                        </div>
                    @endif

                    {{-- Details --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('intensive_care.lbl_hospital_type') }}</span>
                            <span class="fw-medium">{{ __('intensive_care.type_' . $hospital->hospital_type) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('intensive_care.lbl_status') }}</span>
                            <span>{!! $hospital->status ? '<span class="badge bg-success">' . __('messages.active') . '</span>' : '<span class="badge bg-danger">' . __('messages.inactive') . '</span>' !!}</span>
                        </div>
                        @if($hospital->license_number)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('intensive_care.lbl_license_number') }}</span>
                            <span class="fw-medium">{{ $hospital->license_number }}</span>
                        </div>
                        @endif
                        @if($hospital->rating)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">{{ __('intensive_care.lbl_rating') }}</span>
                            <span class="fw-medium">{{ $hospital->rating }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3"><i class="ph ph-map-pin me-1"></i> {{ __('intensive_care.lbl_location_info') }}</h6>
                    <p class="mb-1">{{ $hospital->address }}</p>
                    <p class="mb-1">{{ $hospital->city && is_numeric($hospital->city) ? optional(\Modules\World\Models\City::find($hospital->city))->name ?? $hospital->city : $hospital->city }}{{ $hospital->state ? ', ' . ($hospital->state && is_numeric($hospital->state) ? optional(\Modules\World\Models\State::find($hospital->state))->name ?? $hospital->state : $hospital->state) : '' }}</p>
                    <p class="mb-1">{{ $hospital->country && is_numeric($hospital->country) ? optional(\Modules\World\Models\Country::find($hospital->country))->name ?? $hospital->country : $hospital->country }}{{ $hospital->postal_code ? ' - ' . $hospital->postal_code : '' }}</p>
                </div>
                <div class="col-lg-4">
                    <h6 class="mb-3"><i class="ph ph-phone me-1"></i> {{ __('intensive_care.lbl_contact_info') }}</h6>
                    <p class="mb-1"><i class="ph ph-phone me-1 text-muted"></i> {{ $hospital->phone }}</p>
                    @if($hospital->email)
                    <p class="mb-1"><i class="ph ph-envelope-simple me-1 text-muted"></i> {{ $hospital->email }}</p>
                    @endif
                    @if($hospital->website)
                    <p class="mb-1"><i class="ph ph-globe me-1 text-muted"></i> <a href="{{ $hospital->website }}" target="_blank">{{ $hospital->website }}</a></p>
                    @endif
                    @if($hospital->accepted_insurance && count($hospital->accepted_insurance) > 0)
                        <h6 class="mt-3 mb-2">{{ __('intensive_care.lbl_accepted_insurance') }}</h6>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($hospital->accepted_insurance as $insurance)
                                <span class="badge bg-info">{{ $insurance }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ICU Departments --}}
    @if($hospital->icuDepartments->count() > 0)
    <div class="card" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
            <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-bed me-1"></i> {{ __('intensive_care.department_plural') }}</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('intensive_care.lbl_department_name') }}</th>
                            <th>{{ __('intensive_care.lbl_specialty_type') }}</th>
                            <th>{{ __('intensive_care.lbl_available_beds') }}</th>
                            <th>{{ __('intensive_care.lbl_equipment_level') }}</th>
                            <th>{{ __('intensive_care.lbl_daily_price') }}</th>
                            <th>{{ __('intensive_care.lbl_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hospital->icuDepartments as $dept)
                        <tr>
                            <td class="fw-medium">{{ $dept->name }}</td>
                            <td>{{ __('intensive_care.case_' . $dept->specialty_type) }}</td>
                            <td>
                                @php $color = $dept->available_beds > 0 ? 'success' : 'danger'; @endphp
                                <span class="badge bg-{{ $color }}">{{ $dept->available_beds }} / {{ $dept->total_beds }}</span>
                            </td>
                            <td>
                                @php $eqColors = ['basic' => 'secondary', 'advanced' => 'primary', 'full' => 'success']; @endphp
                                <span class="badge bg-{{ $eqColors[$dept->equipment_level] ?? 'secondary' }}">{{ __('intensive_care.equipment_' . $dept->equipment_level) }}</span>
                            </td>
                            <td>{{ number_format($dept->daily_price, 2) }}</td>
                            <td>
                                {!! $dept->status ? '<span class="badge bg-success">' . __('messages.active') . '</span>' : '<span class="badge bg-danger">' . __('messages.inactive') . '</span>' !!}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endsection
