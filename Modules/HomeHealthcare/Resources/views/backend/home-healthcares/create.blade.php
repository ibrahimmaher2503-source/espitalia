@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8 mx-auto">
            {{ html()->form('POST', route('backend.home-healthcares.store'))->id('home-healthcare-form')->open() }}
            @if(isset($data) && $data->id)
                <input type="hidden" name="id" value="{{ $data->id }}">
            @endif

            <div class="card mb-4" style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                <div class="card-header bg-transparent" style="border-bottom: 1px solid rgba(0,0,0,0.06); padding: 1rem 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" style="color: #374151; font-weight: 600;"><i class="ph ph-house-line me-1"></i> {{ $pageTitle }}</h6>
                        <a href="{{ route('backend.home-healthcares.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_ar">{{ __('location.lbl_name_ar') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_ar') is-invalid @enderror" name="name_ar" id="name_ar" dir="rtl" value="{{ old('name_ar', $data->name_ar ?? '') }}" required>
                            @error('name_ar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name_en">{{ __('location.lbl_name_en') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" id="name_en" dir="ltr" value="{{ old('name_en', $data->name_en ?? '') }}" required>
                            @error('name_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="service_type">{{ __('location.lbl_service_type') }}</label>
                            <input type="text" class="form-control" name="service_type" id="service_type" value="{{ old('service_type', $data->service_type ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="governorate_id">{{ __('location.lbl_governorate') }}</label>
                            <select class="form-control select2" name="governorate_id" id="governorate_id" style="width:100%">
                                <option value="">{{ __('location.lbl_select_governorate') }}</option>
                                @foreach($governorates as $governorate)
                                    <option value="{{ $governorate->id }}" {{ old('governorate_id', $data->governorate_id ?? '') == $governorate->id ? 'selected' : '' }}>{{ $governorate->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="city_id">{{ __('location.lbl_city') }}</label>
                            <select class="form-control select2" name="city_id" id="city_id" style="width:100%">
                                <option value="">{{ __('location.lbl_select_city') }}</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id', $data->city_id ?? '') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label mb-0" for="status">{{ __('location.lbl_status') }}</label>
                                <div class="form-check form-switch">
                                    <input type="hidden" name="status" value="0">
                                    <input type="checkbox" class="form-check-input" name="status" id="status" value="1" {{ old('status', $data->status ?? 1) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.home-healthcares.index') }}" class="btn btn-outline-secondary">
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
        $('#governorate_id').on('change', function() {
            var governorateId = $(this).val();
            var $citySelect = $('#city_id');
            $citySelect.empty().append('<option value="">{{ __("location.lbl_select_city") }}</option>');
            if (governorateId) {
                $.ajax({
                    url: '{{ url("/api/v1/cities") }}',
                    data: { governorate_id: governorateId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.data) {
                            $.each(response.data, function(index, city) {
                                $citySelect.append($('<option></option>').val(city.id).text(city.name));
                            });
                        }
                        $citySelect.trigger('change.select2');
                    }
                });
            }
        });
    });
</script>
@endpush
