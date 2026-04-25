@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    {{ html()->form('POST', route('backend.nurse-requests.store'))->open() }}
    @if($nurseRequest->id)
        <input type="hidden" name="id" value="{{ $nurseRequest->id }}">
    @endif

    <div class="row">
        {{-- Left Column: Summary Sidebar --}}
        <div class="col-lg-4">
            {{-- Request Summary Card --}}
            <div class="card nr-form-card mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="ph ph-note me-1"></i> {{ __('request_nurse.lbl_request_summary') }}</h6>
                        <a href="{{ route('backend.nurse-requests.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="nr-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-user text-primary"></i>
                            <span class="text-muted small">{{ __('request_nurse.lbl_patient_name') }}</span>
                        </div>
                        <p class="mb-0 fw-medium" id="summary-patient">{{ $nurseRequest->patient ? $nurseRequest->patient->first_name . ' ' . $nurseRequest->patient->last_name : '---' }}</p>
                    </div>
                    <div class="nr-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-first-aid text-info"></i>
                            <span class="text-muted small">{{ __('request_nurse.lbl_assigned_nurse') }}</span>
                        </div>
                        <p class="mb-0 fw-medium" id="summary-nurse">{{ __('request_nurse.lbl_any_available_nurse') }}</p>
                    </div>
                    <hr>
                    <div class="nr-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-calendar text-success"></i>
                            <span class="text-muted small">{{ __('request_nurse.lbl_preferred_date') }}</span>
                        </div>
                        <p class="mb-0 fw-medium" id="summary-date">{{ $nurseRequest->preferred_date ? $nurseRequest->preferred_date->format('d M Y') : '---' }}</p>
                    </div>
                    <div class="nr-summary-item mb-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-clock text-warning"></i>
                            <span class="text-muted small">{{ __('request_nurse.lbl_duration_hours') }}</span>
                        </div>
                        <p class="mb-0 fw-medium" id="summary-duration">{{ $nurseRequest->duration_hours ? $nurseRequest->duration_hours . ' ' . __('messages.hours') : '---' }}</p>
                    </div>
                </div>
            </div>

            {{-- Submission Card --}}
            <div class="card nr-form-card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="ph ph-floppy-disk me-1"></i> {{ __('messages.save') }}
                    </button>
                    <a href="{{ route('backend.nurse-requests.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                        {{ __('messages.close') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Form Sections --}}
        <div class="col-lg-8">
            {{-- Section 1: Service Details --}}
            <div class="card nr-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">
                        <span class="nr-step-badge">1</span>
                        {{ __('request_nurse.lbl_service_details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="patient_id">{{ __('request_nurse.lbl_patient_name') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('patient_id') is-invalid @enderror" name="patient_id" id="patient_id" required style="width:100%">
                                <option value="">{{ __('messages.select_name', ['select' => __('request_nurse.lbl_patient_name')]) }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}" {{ old('patient_id', $nurseRequest->patient_id ?? '') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->first_name }} {{ $patient->last_name }} ({{ $patient->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('patient_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="nurse_id">{{ __('request_nurse.lbl_assigned_nurse') }}</label>
                            <select class="form-control select2" name="nurse_id" id="nurse_id" style="width:100%">
                                <option value="">{{ __('request_nurse.lbl_any_available_nurse') }}</option>
                                @foreach($nurses as $nurse)
                                    <option value="{{ $nurse->nurse_id }}" {{ old('nurse_id', $nurseRequest->nurse_id ?? '') == $nurse->nurse_id ? 'selected' : '' }}>
                                        {{ $nurse->user->first_name ?? '' }} {{ $nurse->user->last_name ?? '' }} - {{ $nurse->specialization ?? 'General' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="service_description_ar">{{ __('request_nurse.lbl_service_description_ar') }}</label>
                            <textarea class="form-control @error('service_description_ar') is-invalid @enderror" name="service_description_ar" id="service_description_ar" rows="3" dir="rtl" placeholder="{{ __('request_nurse.lbl_service_description_placeholder') }}">{{ old('service_description_ar', $nurseRequest->service_description_ar ?? '') }}</textarea>
                            @error('service_description_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="service_description_en">{{ __('request_nurse.lbl_service_description_en') }}</label>
                            <textarea class="form-control @error('service_description_en') is-invalid @enderror" name="service_description_en" id="service_description_en" rows="3" placeholder="{{ __('request_nurse.lbl_service_description_placeholder') }}">{{ old('service_description_en', $nurseRequest->service_description_en ?? '') }}</textarea>
                            @error('service_description_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-0">
                            <label class="form-label" for="preferred_date">{{ __('request_nurse.lbl_preferred_date') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-calendar"></i></span>
                                <input type="date" class="form-control @error('preferred_date') is-invalid @enderror" name="preferred_date" id="preferred_date" value="{{ old('preferred_date', $nurseRequest->preferred_date ? $nurseRequest->preferred_date->format('Y-m-d') : '') }}" required>
                                @error('preferred_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="form-label" for="preferred_time">{{ __('request_nurse.lbl_preferred_time') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-clock"></i></span>
                                <input type="time" class="form-control" name="preferred_time" id="preferred_time" value="{{ old('preferred_time', $nurseRequest->preferred_time ?? '') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-0">
                            <label class="form-label" for="duration_hours">{{ __('request_nurse.lbl_duration_hours') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-timer"></i></span>
                                <input type="number" step="0.5" min="1" max="24" class="form-control @error('duration_hours') is-invalid @enderror" name="duration_hours" id="duration_hours" value="{{ old('duration_hours', $nurseRequest->duration_hours ?? 1) }}" required>
                                @error('duration_hours') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Location Details --}}
            <div class="card nr-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">
                        <span class="nr-step-badge">2</span>
                        {{ __('request_nurse.lbl_location_details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="address_line_1">{{ __('request_nurse.lbl_address_line_1') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('address_line_1') is-invalid @enderror" name="address_line_1" id="address_line_1" value="{{ old('address_line_1', $nurseRequest->address_line_1 ?? '') }}" required>
                            @error('address_line_1') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="address_line_2">{{ __('request_nurse.lbl_address_line_2') }}</label>
                            <input type="text" class="form-control" name="address_line_2" id="address_line_2" value="{{ old('address_line_2', $nurseRequest->address_line_2 ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="city">{{ __('request_nurse.lbl_city') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" id="city" value="{{ old('city', $nurseRequest->city ?? '') }}" required>
                            @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="state">{{ __('request_nurse.lbl_state') }}</label>
                            <input type="text" class="form-control" name="state" id="state" value="{{ old('state', $nurseRequest->state ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="country">{{ __('request_nurse.lbl_country') }}</label>
                            <input type="text" class="form-control" name="country" id="country" value="{{ old('country', $nurseRequest->country ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label" for="postal_code">{{ __('request_nurse.lbl_postal_code') }}</label>
                            <input type="text" class="form-control" name="postal_code" id="postal_code" value="{{ old('postal_code', $nurseRequest->postal_code ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-0">
                            <label class="form-label" for="contact_number">{{ __('request_nurse.lbl_contact_number') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                <input type="text" class="form-control @error('contact_number') is-invalid @enderror" name="contact_number" id="contact_number" value="{{ old('contact_number', $nurseRequest->contact_number ?? '') }}" required>
                                @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Additional Notes --}}
            <div class="card nr-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">
                        <span class="nr-step-badge">3</span>
                        {{ __('request_nurse.lbl_additional_notes') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="patient_notes">{{ __('request_nurse.lbl_patient_notes') }}</label>
                            <textarea class="form-control" name="patient_notes" id="patient_notes" rows="3" placeholder="{{ __('request_nurse.lbl_patient_notes_placeholder') }}">{{ old('patient_notes', $nurseRequest->patient_notes ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="admin_notes">{{ __('request_nurse.lbl_admin_notes') }}</label>
                            <textarea class="form-control" name="admin_notes" id="admin_notes" rows="3" placeholder="{{ __('request_nurse.lbl_admin_notes_placeholder') }}">{{ old('admin_notes', $nurseRequest->admin_notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{ html()->form()->close() }}
@endsection

@push('after-styles')
<style>
    .nr-form-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .nr-form-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .nr-form-card .card-header h6 {
        color: #374151;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .nr-step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #818cf8);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .nr-summary-item p {
        color: #1f2937;
    }
    .input-group-text {
        background: transparent;
        border-right: none;
    }
    .input-group-text + .form-control {
        border-left: none;
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // Live update sidebar summary
        $('#patient_id').on('change', function() {
            var text = $(this).find('option:selected').text().trim();
            $('#summary-patient').text(text || '---');
        });
        $('#nurse_id').on('change', function() {
            var text = $(this).find('option:selected').text().trim();
            $('#summary-nurse').text(text || "{{ __('request_nurse.lbl_any_available_nurse') }}");
        });
        $('#preferred_date').on('change', function() {
            var val = $(this).val();
            if (val) {
                var d = new Date(val);
                var options = { day: '2-digit', month: 'short', year: 'numeric' };
                $('#summary-date').text(d.toLocaleDateString('en-GB', options));
            } else {
                $('#summary-date').text('---');
            }
        });
        $('#duration_hours').on('input', function() {
            var val = $(this).val();
            $('#summary-duration').text(val ? val + ' {{ __("messages.hours") }}' : '---');
        });
    });
</script>
@endpush
