@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.hospitals.store'))->acceptsFiles()->id('hospital-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            {{-- Hospital Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-buildings me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.hospitals.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Bilingual Name --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_ar">{{ __('intensive_care.lbl_name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" name="name_ar" id="name_ar" dir="rtl" value="{{ old('name_ar', $data->name_ar ?? '') }}">
                            @error('name_ar') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_en">{{ __('intensive_care.lbl_name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" id="name_en" dir="ltr" value="{{ old('name_en', $data->name_en ?? '') }}">
                            @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Bilingual Description --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="description_ar">{{ __('intensive_care.lbl_description_ar') }}</label>
                            <textarea class="form-control" name="description_ar" id="description_ar" rows="3" dir="rtl">{{ old('description_ar', $data->description_ar ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="description_en">{{ __('intensive_care.lbl_description_en') }}</label>
                            <textarea class="form-control" name="description_en" id="description_en" rows="3" dir="ltr">{{ old('description_en', $data->description_en ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Hospital Type & License --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="hospital_type">{{ __('intensive_care.lbl_hospital_type') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="hospital_type" id="hospital_type" required>
                                <option value="">{{ __('messages.select_option') }}</option>
                                @foreach($hospitalTypes as $type)
                                    <option value="{{ $type }}" {{ old('hospital_type', $data->hospital_type ?? '') == $type ? 'selected' : '' }}>
                                        {{ __('intensive_care.type_' . $type) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="license_number">{{ __('intensive_care.lbl_license_number') }}</label>
                            <input type="text" class="form-control" name="license_number" id="license_number" value="{{ old('license_number', $data->license_number ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-map-pin me-1"></i> {{ __('intensive_care.lbl_location_info') }}</h6>
                </div>
                <div class="card-body">
                    {{-- Bilingual Address --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="address_ar">{{ __('intensive_care.lbl_address_ar') }}</label>
                            <textarea class="form-control" name="address_ar" id="address_ar" rows="2" dir="rtl">{{ old('address_ar', $data->address_ar ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="address_en">{{ __('intensive_care.lbl_address_en') }}</label>
                            <textarea class="form-control" name="address_en" id="address_en" rows="2" dir="ltr">{{ old('address_en', $data->address_en ?? '') }}</textarea>
                        </div>
                    </div>

                    {{-- Governorate & City (cascading) --}}
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="governorate_id">{{ __('intensive_care.lbl_governorate') }}</label>
                            <select class="form-control select2" name="governorate_id" id="governorate_id">
                                <option value="">{{ __('intensive_care.lbl_select_governorate') }}</option>
                                @foreach($governorates ?? [] as $governorate)
                                    <option value="{{ $governorate->id }}" {{ old('governorate_id', $data->governorate_id ?? '') == $governorate->id ? 'selected' : '' }}>
                                        {{ $governorate->name_ar }} - {{ $governorate->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="city_id">{{ __('intensive_care.lbl_city') }}</label>
                            <select class="form-control select2" name="city_id" id="city_id">
                                <option value="">{{ __('intensive_care.lbl_select_city') }}</option>
                                @foreach($cities ?? [] as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id', $data->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name_ar }} - {{ $city->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Legacy location fields --}}
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="state">{{ __('intensive_care.lbl_state') }}</label>
                            <input type="text" class="form-control" name="state" id="state" value="{{ old('state', $data->state ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="country">{{ __('intensive_care.lbl_country') }}</label>
                            <input type="text" class="form-control" name="country" id="country" value="{{ old('country', $data->country ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="postal_code">{{ __('intensive_care.lbl_postal_code') }}</label>
                            <input type="text" class="form-control" name="postal_code" id="postal_code" value="{{ old('postal_code', $data->postal_code ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="latitude">{{ __('intensive_care.lbl_latitude') }}</label>
                            <input type="number" step="any" class="form-control" name="latitude" id="latitude" value="{{ old('latitude', $data->latitude ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="longitude">{{ __('intensive_care.lbl_longitude') }}</label>
                            <input type="number" step="any" class="form-control" name="longitude" id="longitude" value="{{ old('longitude', $data->longitude ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-phone me-1"></i> {{ __('intensive_care.lbl_contact_info') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="phone">{{ __('intensive_care.lbl_phone') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="phone" id="phone" value="{{ old('phone', $data->phone ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="email">{{ __('intensive_care.lbl_email') }}</label>
                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email', $data->email ?? '') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="website">{{ __('intensive_care.lbl_website') }}</label>
                            <input type="url" class="form-control" name="website" id="website" value="{{ old('website', $data->website ?? '') }}">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Commission, Insurance & Media --}}
            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-shield-check me-1"></i> {{ __('intensive_care.lbl_insurance_and_media') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="commission_id">{{ __('intensive_care.lbl_commission_template') }}</label>
                            <select class="form-control select2" name="commission_id" id="commission_id">
                                <option value="">{{ __('intensive_care.lbl_select_commission') }}</option>
                                @foreach($commissions ?? [] as $commission)
                                    <option value="{{ $commission->id }}" {{ old('commission_id', $data->commission_id ?? '') == $commission->id ? 'selected' : '' }}>
                                        {{ $commission->title }} ({{ $commission->commission_type === 'percentage' ? $commission->commission_value . '%' : $commission->commission_value }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="accepted_insurance">{{ __('intensive_care.lbl_accepted_insurance') }}</label>
                            <input type="text" class="form-control" name="accepted_insurance_text" id="accepted_insurance_text"
                                value="{{ old('accepted_insurance_text', isset($data) && $data->accepted_insurance ? implode(', ', $data->accepted_insurance) : '') }}"
                                placeholder="{{ __('intensive_care.lbl_insurance_placeholder') }}">
                            <small class="text-muted">{{ __('intensive_care.lbl_insurance_help') }}</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="logo">{{ __('intensive_care.lbl_logo') }}</label>
                            <input type="file" class="form-control" name="logo" id="logo" accept="image/*">
                            @if(isset($data) && $data->id && $data->getFirstMediaUrl('logo'))
                                <div class="mt-2">
                                    <img src="{{ $data->getFirstMediaUrl('logo') }}" alt="Logo" style="max-height: 60px; border-radius: 8px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-0">
                            <div class="d-flex justify-content-between align-items-center mt-4">
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
                <a href="{{ route('backend.hospitals.index') }}" class="btn btn-outline-secondary">
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
        // Initialize Select2
        $('.select2').select2();

        // Governorate -> City cascade
        $('#governorate_id').on('change', function() {
            var governorateId = $(this).val();
            var $citySelect = $('#city_id');
            $citySelect.empty().append('<option value="">{{ __("intensive_care.lbl_select_city") }}</option>');

            if (governorateId) {
                $.ajax({
                    url: '{{ url("/api/v1/cities") }}',
                    data: { governorate_id: governorateId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.data) {
                            $.each(response.data, function(index, city) {
                                $citySelect.append($('<option></option>')
                                    .val(city.id)
                                    .text(city.name_ar + ' - ' + city.name_en));
                            });
                        }
                        $citySelect.trigger('change.select2');
                    }
                });
            }
        });

        // Convert comma-separated insurance text to array on form submit
        $('#hospital-form').on('submit', function() {
            var insuranceText = $('#accepted_insurance_text').val();
            if (insuranceText) {
                var insurances = insuranceText.split(',').map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });
                // Remove existing hidden inputs
                $('input[name="accepted_insurance[]"]').remove();
                insurances.forEach(function(insurance) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'accepted_insurance[]',
                        value: insurance
                    }).appendTo('#hospital-form');
                });
            }
        });
    });
</script>
@endpush
