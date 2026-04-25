@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.facility-sessions.store'))->id('session-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Session Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-calendar-blank me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.facility-sessions.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="facility_type">{{ __('radiology_lab.lbl_facility_type') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="facility_type" id="facility_type" style="width:100%" required>
                                <option value="">{{ __('radiology_lab.lbl_select_facility_type') }}</option>
                                <option value="lab" {{ old('facility_type', $data->facility_type ?? '') == 'lab' ? 'selected' : '' }}>{{ __('radiology_lab.type_lab') }}</option>
                                <option value="radiology" {{ old('facility_type', $data->facility_type ?? '') == 'radiology' ? 'selected' : '' }}>{{ __('radiology_lab.type_radiology') }}</option>
                            </select>
                            @error('facility_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="facility_id">{{ __('radiology_lab.lbl_facility_name') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="facility_id" id="facility_id" style="width:100%" required>
                                <option value="">{{ __('radiology_lab.lbl_select_facility') }}</option>
                                @if(old('facility_type', $data->facility_type ?? '') == 'lab')
                                    @foreach($labs as $lab)
                                        <option value="{{ $lab->id }}" {{ old('facility_id', $data->facility_id ?? '') == $lab->id ? 'selected' : '' }}>{{ $lab->name }}</option>
                                    @endforeach
                                @elseif(old('facility_type', $data->facility_type ?? '') == 'radiology')
                                    @foreach($radiologyCenters as $center)
                                        <option value="{{ $center->id }}" {{ old('facility_id', $data->facility_id ?? '') == $center->id ? 'selected' : '' }}>{{ $center->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('facility_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="day">{{ __('radiology_lab.lbl_day') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="day" id="day" style="width:100%" required>
                                <option value="">{{ __('radiology_lab.lbl_select_day') }}</option>
                                @foreach($days as $day)
                                    <option value="{{ $day }}" {{ old('day', $data->day ?? '') == $day ? 'selected' : '' }}>{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>
                            @error('day')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="slot_duration">{{ __('radiology_lab.lbl_slot_duration') }} (min) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('slot_duration') is-invalid @enderror" name="slot_duration" id="slot_duration" min="5" max="120" value="{{ old('slot_duration', $data->slot_duration ?? 30) }}" required>
                            @error('slot_duration')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_time">{{ __('radiology_lab.lbl_start_time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" name="start_time" id="start_time" value="{{ old('start_time', $data->start_time ?? '08:00') }}" required>
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="end_time">{{ __('radiology_lab.lbl_end_time') }} <span class="text-danger">*</span></label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" name="end_time" id="end_time" value="{{ old('end_time', $data->end_time ?? '17:00') }}" required>
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Breaks Section --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">{{ __('radiology_lab.lbl_breaks') }}</label>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-break-btn">
                                <i class="ph ph-plus"></i> {{ __('radiology_lab.lbl_add_break') }}
                            </button>
                        </div>
                        <div id="breaks-container">
                            @php $breaks = old('breaks', $data->breaks ?? []); @endphp
                            @foreach($breaks as $index => $brk)
                                <div class="row break-row mb-2" data-index="{{ $index }}">
                                    <div class="col-5">
                                        <input type="time" class="form-control" name="breaks[{{ $index }}][start_break]" value="{{ $brk['start_break'] ?? '' }}" placeholder="{{ __('radiology_lab.lbl_break_start') }}">
                                    </div>
                                    <div class="col-5">
                                        <input type="time" class="form-control" name="breaks[{{ $index }}][end_break]" value="{{ $brk['end_break'] ?? '' }}" placeholder="{{ __('radiology_lab.lbl_break_end') }}">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-break-btn w-100"><i class="ph ph-trash"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="is_holiday">{{ __('radiology_lab.lbl_holiday') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_holiday" value="0">
                                    <input type="checkbox" class="form-check-input" name="is_holiday" id="is_holiday" value="1" {{ old('is_holiday', $data->is_holiday ?? 0) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="status">{{ __('radiology_lab.lbl_status') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" class="form-check-input" name="status" id="status" value="1" {{ old('status', $data->status ?? 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.facility-sessions.index') }}" class="btn btn-outline-secondary">
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
        var breakIndex = {{ count($breaks) }};
        var labs = @json($labs->map(fn($l) => ['id' => $l->id, 'name' => $l->name]));
        var radiologyCenters = @json($radiologyCenters->map(fn($c) => ['id' => $c->id, 'name' => $c->name]));

        $('#facility_type').on('change', function() {
            var type = $(this).val();
            var $facilitySelect = $('#facility_id');
            $facilitySelect.empty().append('<option value="">{{ __("radiology_lab.lbl_select_facility") }}</option>');

            var items = type === 'lab' ? labs : (type === 'radiology' ? radiologyCenters : []);
            $.each(items, function(index, item) {
                $facilitySelect.append($('<option></option>').val(item.id).text(item.name));
            });
            $facilitySelect.trigger('change.select2');
        });

        $('#add-break-btn').on('click', function() {
            var html = '<div class="row break-row mb-2" data-index="' + breakIndex + '">' +
                '<div class="col-5"><input type="time" class="form-control" name="breaks[' + breakIndex + '][start_break]" placeholder="{{ __("radiology_lab.lbl_break_start") }}"></div>' +
                '<div class="col-5"><input type="time" class="form-control" name="breaks[' + breakIndex + '][end_break]" placeholder="{{ __("radiology_lab.lbl_break_end") }}"></div>' +
                '<div class="col-2"><button type="button" class="btn btn-outline-danger btn-sm remove-break-btn w-100"><i class="ph ph-trash"></i></button></div>' +
                '</div>';
            $('#breaks-container').append(html);
            breakIndex++;
        });

        $(document).on('click', '.remove-break-btn', function() {
            $(this).closest('.break-row').remove();
        });
    });
</script>
@endpush
