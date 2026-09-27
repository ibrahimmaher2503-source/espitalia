@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.doctor-session.store'))->id('session-form')->open() }}

            {{-- Session Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-clock me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.doctor-session.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">

                    {{-- Doctor & Clinic Selection (Admin only) --}}
                    @if(auth()->user()->hasAnyRole(['admin', 'demo_admin']))
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="doctor_id">{{ __('clinic.lbl_select_doctor') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('doctor_id') is-invalid @enderror" name="doctor_id" id="doctor_id" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="clinic_id">{{ __('clinic.lbl_select_clinic') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('clinic_id') is-invalid @enderror" name="clinic_id" id="clinic_id" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($clinics as $clinic)
                                    <option value="{{ $clinic->id }}" {{ old('clinic_id') == $clinic->id ? 'selected' : '' }}>
                                        {{ $clinic->clinic_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('clinic_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endif

                    {{-- Day Selection --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="day">{{ __('clinic.lbl_week_days') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('weekdays') is-invalid @enderror" name="weekdays[0][day]" id="day" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($weekDays as $day)
                                    <option value="{{ $day }}" {{ old('weekdays.0.day') == $day ? 'selected' : '' }}>
                                        {{ ucfirst($day) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('weekdays') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Time Range --}}
                    <div id="time-range-fields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="start_time">{{ __('messages.lbl_start_time') }} <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('weekdays.0.start_time') is-invalid @enderror"
                                    name="weekdays[0][start_time]" id="start_time"
                                    value="{{ old('weekdays.0.start_time', '09:00') }}" required>
                                @error('weekdays.0.start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="end_time">{{ __('messages.lbl_end_time') }} <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('weekdays.0.end_time') is-invalid @enderror"
                                    name="weekdays[0][end_time]" id="end_time"
                                    value="{{ old('weekdays.0.end_time', '17:00') }}" required>
                                @error('weekdays.0.end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Is Holiday --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="is_holiday">{{ __('clinic.lbl_add_day_off') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="weekdays[0][is_holiday]" value="0">
                                    <input type="checkbox" class="form-check-input" name="weekdays[0][is_holiday]" id="is_holiday"
                                        value="1" {{ old('weekdays.0.is_holiday') ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Breaks Section --}}
            <div id="breaks-card" class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-coffee me-1"></i> {{ __('clinic.lbl_breaks') }}</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-break">
                            <i class="ph ph-plus"></i> {{ __('clinic.lbl_add_break') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="breaks-container"></div>
                    <p class="text-muted small mb-0" id="no-breaks-message">{{ __('messages.no_breaks_added') }}</p>
                </div>
            </div>

            {{-- Status --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="status">{{ __('messages.lbl_status') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="weekdays[0][status]" value="0">
                            <input type="checkbox" class="form-check-input" name="weekdays[0][status]" id="status"
                                value="1" {{ old('weekdays.0.status', 1) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.doctor-session.index') }}" class="btn btn-outline-secondary">
                    {{ __('messages.close') }}
                </a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="ph ph-floppy-disk me-1"></i> {{ __('messages.save') }}
                </button>
            </div>

            {{ html()->form()->close() }}
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        function toggleHoliday() {
            var isHoliday = $('#is_holiday').is(':checked');
            $('#time-range-fields').toggle(!isHoliday);
            $('#breaks-card').toggle(!isHoliday);
            $('#start_time, #end_time').prop('required', !isHoliday);
        }
        $('#is_holiday').on('change', toggleHoliday);
        toggleHoliday();

        var breakIndex = 0;

        $('#add-break').on('click', function() {
            var breakHtml = `
                <div class="row break-row mb-3">
                    <div class="col-md-5">
                        <label class="form-label">{{ __('messages.lbl_start_time') }}</label>
                        <input type="time" class="form-control" name="weekdays[0][breaks][${breakIndex}][start_time]" value="12:00">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">{{ __('messages.lbl_end_time') }}</label>
                        <input type="time" class="form-control" name="weekdays[0][breaks][${breakIndex}][end_time]" value="13:00">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-break">
                            <i class="ph ph-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#breaks-container').append(breakHtml);
            $('#no-breaks-message').hide();
            breakIndex++;
        });

        $(document).on('click', '.remove-break', function() {
            $(this).closest('.break-row').remove();
            if ($('.break-row').length === 0) {
                $('#no-breaks-message').show();
            }
        });
    });
</script>
@endpush
