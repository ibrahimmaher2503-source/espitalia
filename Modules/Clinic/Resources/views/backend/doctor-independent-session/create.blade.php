@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', isset($data) && $data->id ? route('backend.doctor-independent-session.update', $data->id) : route('backend.doctor-independent-session.store'))->id('session-form')->open() }}
            @if(isset($data) && $data->id)
                @method('PUT')
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Session Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-clock me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.doctor-independent-session.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Doctor Selection (Admin only) --}}
                    @if(auth()->user()->hasAnyRole(['admin', 'demo_admin']))
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="doctor_id">{{ __('clinic.lbl_select_doctor') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('doctor_id') is-invalid @enderror" name="doctor_id" id="doctor_id" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($doctors as $doctor)
                                    <option value="{{ $doctor->id }}" {{ old('doctor_id', $data->doctor_id ?? '') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('doctor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endif

                    {{-- Day Selection --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="day">{{ __('clinic.lbl_week_days') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2 @error('day') is-invalid @enderror" name="day" id="day" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($weekDays as $day)
                                    <option value="{{ $day }}" {{ old('day', $data->day ?? '') == $day ? 'selected' : '' }}>
                                        {{ ucfirst($day) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Time Range --}}
                    <div id="time-range-fields">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_time">{{ __('messages.lbl_start_time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" name="start_time" id="start_time" value="{{ old('start_time', isset($data->start_time) ? \Carbon\Carbon::parse($data->start_time)->format('H:i') : '09:00') }}" required>
                            @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="end_time">{{ __('messages.lbl_end_time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" id="end_time" value="{{ old('end_time', isset($data->end_time) ? \Carbon\Carbon::parse($data->end_time)->format('H:i') : '17:00') }}" required>
                            @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    </div>

                    {{-- Is Holiday --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="is_holiday">{{ __('clinic.lbl_add_day_off') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_holiday" value="0">
                                    <input type="checkbox" class="form-check-input" name="is_holiday" id="is_holiday" value="1" {{ old('is_holiday', $data->is_holiday ?? 0) ? 'checked' : '' }}>
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
                    <div id="breaks-container">
                        @php
                            $breaks = old('breaks', isset($data->breaks) ? (is_string($data->breaks) ? json_decode($data->breaks, true) : $data->breaks) : []);
                        @endphp
                        @if(is_array($breaks) && count($breaks) > 0)
                            @foreach($breaks as $index => $break)
                            <div class="row break-row mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">{{ __('messages.lbl_start_time') }}</label>
                                    <input type="time" class="form-control" name="breaks[{{ $index }}][start_time]" value="{{ $break['start_time'] ?? '' }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">{{ __('messages.lbl_end_time') }}</label>
                                    <input type="time" class="form-control" name="breaks[{{ $index }}][end_time]" value="{{ $break['end_time'] ?? '' }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-break">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>
                    <p class="text-muted small mb-0" id="no-breaks-message" style="{{ is_array($breaks) && count($breaks) > 0 ? 'display:none;' : '' }}">
                        {{ __('messages.no_breaks_added') }}
                    </p>
                </div>
            </div>

            {{-- Status --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="status">{{ __('messages.lbl_status') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0">
                            <input type="checkbox" class="form-check-input" name="status" id="status" value="1" {{ old('status', $data->status ?? 1) ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.doctor-independent-session.index') }}" class="btn btn-outline-secondary">
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

        // Holiday toggle: hide time range and breaks when day is marked as a holiday
        function toggleHoliday() {
            var isHoliday = $('#is_holiday').is(':checked');
            $('#time-range-fields').toggle(!isHoliday);
            $('#breaks-card').toggle(!isHoliday);
            $('#start_time, #end_time').prop('required', !isHoliday);
        }
        $('#is_holiday').on('change', toggleHoliday);
        toggleHoliday();

        var breakIndex = {{ is_array($breaks) ? count($breaks) : 0 }};

        $('#add-break').on('click', function() {
            var breakHtml = `
                <div class="row break-row mb-3">
                    <div class="col-md-5">
                        <label class="form-label">{{ __('messages.lbl_start_time') }}</label>
                        <input type="time" class="form-control" name="breaks[${breakIndex}][start_time]" value="12:00">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">{{ __('messages.lbl_end_time') }}</label>
                        <input type="time" class="form-control" name="breaks[${breakIndex}][end_time]" value="13:00">
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
