@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', isset($data) && $data->id ? route('backend.doctor-independent-service.update', $data->id) : route('backend.doctor-independent-service.store'))->id('service-form')->open() }}
            @if(isset($data) && $data->id)
                @method('PUT')
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Service Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-first-aid-kit me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.doctor-independent-service.index') }}" class="btn btn-outline-secondary btn-sm">
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

                    {{-- Name & Description --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="name">{{ __('messages.lbl_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" id="name" value="{{ old('name', $data->name ?? '') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="description">{{ __('clinic.lbl_description') }}</label>
                            <textarea class="form-control" name="description" id="description" rows="3">{{ old('description', $data->description ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Duration & Time Slot --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="duration_min">{{ __('messages.lbl_duration') }} ({{ __('messages.minutes') }})</label>
                            <input type="number" class="form-control" name="duration_min" id="duration_min" min="1" value="{{ old('duration_min', $data->duration_min ?? 30) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="time_slot">{{ __('clinic.lbl_time_slot') }}</label>
                            <input type="number" class="form-control" name="time_slot" id="time_slot" min="5" value="{{ old('time_slot', $data->time_slot ?? 15) }}">
                        </div>
                    </div>

                    {{-- Charges --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="charges">{{ __('messages.lbl_charges') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('charges') is-invalid @enderror" name="charges" id="charges" min="0" value="{{ old('charges', $data->charges ?? 0) }}" required>
                            @error('charges') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Discount Settings --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-percent me-1"></i> {{ __('messages.lbl_discount') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="discount">{{ __('messages.lbl_enable_discount') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="discount" value="0">
                                    <input type="checkbox" class="form-check-input" name="discount" id="discount" value="1" {{ old('discount', $data->discount ?? 0) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="discount-fields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="discount_type">{{ __('messages.lbl_discount_type') }}</label>
                                <select class="form-control" name="discount_type" id="discount_type">
                                    <option value="percentage" {{ old('discount_type', $data->discount_type ?? '') == 'percentage' ? 'selected' : '' }}>{{ __('messages.percentage') }}</option>
                                    <option value="fixed" {{ old('discount_type', $data->discount_type ?? '') == 'fixed' ? 'selected' : '' }}>{{ __('messages.fixed') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="discount_value">{{ __('messages.lbl_discount_value') }}</label>
                                <input type="number" step="0.01" class="form-control" name="discount_value" id="discount_value" min="0" value="{{ old('discount_value', $data->discount_value ?? 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tax Settings --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-receipt me-1"></i> {{ __('messages.tax_settings') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="is_inclusive_tax">{{ __('messages.inclusive_tax') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="is_inclusive_tax" value="0">
                                    <input type="checkbox" class="form-check-input" name="is_inclusive_tax" id="is_inclusive_tax" value="1" {{ old('is_inclusive_tax', $data->is_inclusive_tax ?? 0) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="tax-fields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="inclusive_tax">{{ __('messages.tax_percent') }}</label>
                                <input type="number" step="0.01" class="form-control" name="inclusive_tax" id="inclusive_tax" min="0" value="{{ old('inclusive_tax', $data->inclusive_tax ?? 0) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="inclusive_tax_price">{{ __('messages.tax_amount') }}</label>
                                <input type="number" step="0.01" class="form-control" name="inclusive_tax_price" id="inclusive_tax_price" min="0" value="{{ old('inclusive_tax_price', $data->inclusive_tax_price ?? 0) }}">
                            </div>
                        </div>
                    </div>
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
                <a href="{{ route('backend.doctor-independent-service.index') }}" class="btn btn-outline-secondary">
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
    });
</script>
@endpush
