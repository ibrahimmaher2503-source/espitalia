@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.icu-departments.store'))->id('department-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Department Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-bed me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.icu-departments.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="hospital_id">{{ __('intensive_care.lbl_hospital_name') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="hospital_id" id="hospital_id" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($hospitals as $hospital)
                                    <option value="{{ $hospital->id }}" {{ old('hospital_id', $data->hospital_id ?? '') == $hospital->id ? 'selected' : '' }}>
                                        {{ $hospital->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_ar">{{ __('intensive_care.lbl_name_ar') }}</label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" name="name_ar" id="name_ar" value="{{ old('name_ar', $data->name_ar ?? '') }}" dir="rtl">
                            @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_en">{{ __('intensive_care.lbl_name_en') }}</label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" id="name_en" value="{{ old('name_en', $data->name_en ?? '') }}" dir="ltr">
                            @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="description_ar">{{ __('intensive_care.lbl_description_ar') }}</label>
                            <textarea class="form-control @error('description_ar') is-invalid @enderror" name="description_ar" id="description_ar" rows="3" dir="rtl">{{ old('description_ar', $data->description_ar ?? '') }}</textarea>
                            @error('description_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="description_en">{{ __('intensive_care.lbl_description_en') }}</label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" name="description_en" id="description_en" rows="3" dir="ltr">{{ old('description_en', $data->description_en ?? '') }}</textarea>
                            @error('description_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="specialty_type">{{ __('intensive_care.lbl_specialty_type') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="specialty_type" id="specialty_type" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($specialtyTypes as $type)
                                    <option value="{{ $type }}" {{ old('specialty_type', $data->specialty_type ?? '') == $type ? 'selected' : '' }}>
                                        {{ __('intensive_care.case_' . $type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="equipment_level">{{ __('intensive_care.lbl_equipment_level') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="equipment_level" id="equipment_level" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($equipmentLevels as $level)
                                    <option value="{{ $level }}" {{ old('equipment_level', $data->equipment_level ?? '') == $level ? 'selected' : '' }}>
                                        {{ __('intensive_care.equipment_' . $level) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Beds & Equipment --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-bed me-1"></i> {{ __('intensive_care.lbl_beds_and_equipment') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="total_beds">{{ __('intensive_care.lbl_total_beds') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="total_beds" id="total_beds" value="{{ old('total_beds', $data->total_beds ?? 0) }}" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="available_beds">{{ __('intensive_care.lbl_available_beds') }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="available_beds" id="available_beds" value="{{ old('available_beds', $data->available_beds ?? 0) }}" min="0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="daily_price">{{ __('intensive_care.lbl_daily_price') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="daily_price" id="daily_price" value="{{ old('daily_price', $data->daily_price ?? '') }}" min="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="has_ventilator" value="0">
                                <input type="checkbox" class="form-check-input" name="has_ventilator" id="has_ventilator" value="1" {{ old('has_ventilator', $data->has_ventilator ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_ventilator">{{ __('intensive_care.lbl_needs_ventilator') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="has_oxygen" value="0">
                                <input type="checkbox" class="form-check-input" name="has_oxygen" id="has_oxygen" value="1" {{ old('has_oxygen', $data->has_oxygen ?? 0) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_oxygen">{{ __('intensive_care.lbl_needs_oxygen') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4 mb-0">
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <label class="form-label mb-0" for="status">{{ __('intensive_care.lbl_status') }}</label>
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
                <a href="{{ route('backend.icu-departments.index') }}" class="btn btn-outline-secondary">
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
