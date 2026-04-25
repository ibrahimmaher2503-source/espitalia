@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    <div class="row">
        {{-- Left Column: Profile Image & Status --}}
        <div class="col-lg-4">
            <div class="card nurse-form-card mb-4">
                <div class="card-body text-center py-4">
                    <div class="nurse-avatar-wrapper mb-3">
                        @if($nurse->id && $nurse->user && $nurse->user->getFirstMediaUrl('profile_image'))
                            <img src="{{ $nurse->user->getFirstMediaUrl('profile_image') }}" class="nurse-avatar-img" id="profile-preview" alt="{{ $nurse->user->first_name ?? '' }}">
                        @elseif($nurse->id && $nurse->getFirstMediaUrl('profile_image'))
                            <img src="{{ $nurse->getFirstMediaUrl('profile_image') }}" class="nurse-avatar-img" id="profile-preview" alt="">
                        @else
                            <div class="nurse-avatar-placeholder" id="profile-placeholder">
                                <i class="ph ph-user"></i>
                            </div>
                            <img src="" class="nurse-avatar-img d-none" id="profile-preview" alt="">
                        @endif
                    </div>
                    @if($nurse->id && $nurse->user)
                        <h5 class="mb-1">{{ $nurse->user->first_name }} {{ $nurse->user->last_name }}</h5>
                        <p class="text-muted mb-0">{{ $nurse->specialization ?? __('request_nurse.lbl_nurse') }}</p>
                    @else
                        <h5 class="mb-1 text-muted">{{ __('request_nurse.lbl_new_nurse') }}</h5>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-top">
                    <div class="mb-3">
                        <label class="form-label small text-uppercase" style="letter-spacing:0.5px;" for="profile_image">{{ __('request_nurse.lbl_profile_image') }}</label>
                        <input type="file" class="form-control" name="profile_image" id="profile_image" accept="image/*" form="nurse-form">
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label mb-0" for="status">{{ __('request_nurse.lbl_status') }}</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="status" value="0" form="nurse-form">
                            <input type="checkbox" class="form-check-input" name="status" id="status" value="1" {{ old('status', $nurse->status ?? 1) ? 'checked' : '' }} form="nurse-form">
                        </div>
                    </div>
                </div>
            </div>

            @if($nurse->id)
            <div class="card nurse-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-info me-1"></i> {{ __('request_nurse.lbl_quick_info') }}</h6>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('request_nurse.lbl_availability_status') }}</span>
                        <span>
                            @if($nurse->availability_status === 'available')
                                <span class="badge bg-success">{{ __('request_nurse.status_available') }}</span>
                            @elseif($nurse->availability_status === 'busy')
                                <span class="badge bg-warning text-dark">{{ __('request_nurse.status_busy') }}</span>
                            @else
                                <span class="badge bg-secondary">{{ __('request_nurse.status_off_duty') }}</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">{{ __('request_nurse.lbl_hourly_rate') }}</span>
                        <span class="fw-semibold">{{ number_format($nurse->hourly_rate, 2) }}</span>
                    </div>
                    @if($nurse->experience)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">{{ __('request_nurse.lbl_experience') }}</span>
                        <span>{{ $nurse->experience }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column: Form Fields --}}
        <div class="col-lg-8">
            {{ html()->form('POST', route('backend.nurses.store'))->acceptsFiles()->id('nurse-form')->open() }}
            @if($nurse->id)
                <input type="hidden" name="id" value="{{ $nurse->id }}">
                <input type="hidden" name="user_id" value="{{ $nurse->nurse_id }}">
            @endif

            {{-- Personal Information --}}
            <div class="card nurse-form-card mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="ph ph-user me-1"></i> {{ __('request_nurse.lbl_personal_info') }}</h6>
                        <a href="{{ route('backend.nurses.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="first_name">{{ __('request_nurse.lbl_first_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" id="first_name" value="{{ old('first_name', $nurse->user->first_name ?? '') }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="last_name">{{ __('request_nurse.lbl_last_name') }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" id="last_name" value="{{ old('last_name', $nurse->user->last_name ?? '') }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="email">{{ __('request_nurse.lbl_email') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-envelope-simple"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" id="email" value="{{ old('email', $nurse->user->email ?? '') }}" required>
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="mobile">{{ __('request_nurse.lbl_mobile') }}</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-phone"></i></span>
                                <input type="text" class="form-control" name="mobile" id="mobile" value="{{ old('mobile', $nurse->user->mobile ?? '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-0">
                            <label class="form-label" for="password">{{ __('request_nurse.lbl_password') }} @if(!$nurse->id)<span class="text-danger">*</span>@endif</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-lock-simple"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" {{ !$nurse->id ? 'required' : '' }}>
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @if($nurse->id) <small class="text-muted">{{ __('messages.leave_empty_to_keep_current') }}</small> @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professional Details --}}
            <div class="card nurse-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-certificate me-1"></i> {{ __('request_nurse.lbl_professional_details') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="specialization_ids">{{ __('specialization.lbl_specializations') }}</label>
                            <select name="specialization_ids[]" id="specialization_ids" class="form-control select2" multiple="multiple" style="width:100%">
                                @foreach($specializations as $spec)
                                    <option value="{{ $spec->id }}" {{ in_array($spec->id, old('specialization_ids', $selectedSpecializationIds ?? [])) ? 'selected' : '' }}>
                                        {{ $spec->name_en }} — {{ $spec->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted" for="specialization_ar">{{ __('specialization.lbl_legacy_specialization') }} (AR)</label>
                            <input type="text" class="form-control bg-light" name="specialization_ar" id="specialization_ar" dir="rtl" value="{{ old('specialization_ar', $nurse->specialization_ar ?? '') }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted" for="specialization_en">{{ __('specialization.lbl_legacy_specialization') }} (EN)</label>
                            <input type="text" class="form-control bg-light" name="specialization_en" id="specialization_en" value="{{ old('specialization_en', $nurse->specialization_en ?? '') }}" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="experience_ar">{{ __('request_nurse.lbl_experience_ar') }}</label>
                            <input type="text" class="form-control" name="experience_ar" id="experience_ar" dir="rtl" value="{{ old('experience_ar', $nurse->experience_ar ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="experience_en">{{ __('request_nurse.lbl_experience_en') }}</label>
                            <input type="text" class="form-control" name="experience_en" id="experience_en" value="{{ old('experience_en', $nurse->experience_en ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="hourly_rate">{{ __('request_nurse.lbl_hourly_rate') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ph ph-currency-dollar"></i></span>
                                <input type="number" step="0.01" class="form-control @error('hourly_rate') is-invalid @enderror" name="hourly_rate" id="hourly_rate" value="{{ old('hourly_rate', $nurse->hourly_rate ?? 0) }}" required>
                                @error('hourly_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="availability_status">{{ __('request_nurse.lbl_availability_status') }}</label>
                            <select class="form-control" name="availability_status" id="availability_status">
                                <option value="available" {{ old('availability_status', $nurse->availability_status ?? '') == 'available' ? 'selected' : '' }}>{{ __('request_nurse.status_available') }}</option>
                                <option value="busy" {{ old('availability_status', $nurse->availability_status ?? '') == 'busy' ? 'selected' : '' }}>{{ __('request_nurse.status_busy') }}</option>
                                <option value="off_duty" {{ old('availability_status', $nurse->availability_status ?? '') == 'off_duty' ? 'selected' : '' }}>{{ __('request_nurse.status_off_duty') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="service_area_ar">{{ __('request_nurse.lbl_service_area_ar') }}</label>
                            <input type="text" class="form-control" name="service_area_ar" id="service_area_ar" dir="rtl" value="{{ old('service_area_ar', $nurse->service_area_ar ?? '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="service_area_en">{{ __('request_nurse.lbl_service_area_en') }}</label>
                            <input type="text" class="form-control" name="service_area_en" id="service_area_en" value="{{ old('service_area_en', $nurse->service_area_en ?? '') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="about_ar">{{ __('request_nurse.lbl_about_ar') }}</label>
                            <textarea class="form-control" name="about_ar" id="about_ar" rows="3" dir="rtl">{{ old('about_ar', $nurse->about_ar ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="about_en">{{ __('request_nurse.lbl_about_en') }}</label>
                            <textarea class="form-control" name="about_en" id="about_en" rows="3">{{ old('about_en', $nurse->about_en ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div class="card nurse-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0"><i class="ph ph-map-pin me-1"></i> {{ __('request_nurse.lbl_location') }}</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="governorate_id">{{ __('request_nurse.lbl_governorate') }}</label>
                            <select class="form-control select2" name="governorate_id" id="governorate_id" form="nurse-form">
                                <option value="">{{ __('request_nurse.lbl_select_governorate') }}</option>
                                @foreach($governorates ?? [] as $governorate)
                                    <option value="{{ $governorate->id }}" {{ old('governorate_id', $nurse->governorate_id ?? '') == $governorate->id ? 'selected' : '' }}>
                                        {{ $governorate->name_ar }} - {{ $governorate->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="city_id">{{ __('request_nurse.lbl_city') }}</label>
                            <select class="form-control select2" name="city_id" id="city_id" form="nurse-form">
                                <option value="">{{ __('request_nurse.lbl_select_city') }}</option>
                                @foreach($cities ?? [] as $city)
                                    <option value="{{ $city->id }}" {{ old('city_id', $nurse->city_id ?? '') == $city->id ? 'selected' : '' }}>
                                        {{ $city->name_ar }} - {{ $city->name_en }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('backend.nurses.index') }}" class="btn btn-outline-secondary">
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

@push('after-styles')
<style>
    .nurse-form-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .nurse-form-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .nurse-form-card .card-header h6 {
        color: #374151;
        font-weight: 600;
    }
    .nurse-avatar-wrapper {
        width: 120px;
        height: 120px;
        margin: 0 auto;
        position: relative;
    }
    .nurse-avatar-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(99,102,241,0.2);
    }
    .nurse-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(6,182,212,0.1));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: #6366f1;
        border: 3px dashed rgba(99,102,241,0.3);
    }
    .input-group-text {
        background: transparent;
        border-right: none;
    }
    .input-group-text + .form-control {
        border-left: none;
    }
    .input-group-text + .form-control:focus {
        border-left: none;
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        // Profile image preview
        $('#profile_image').on('change', function(e) {
            var file = e.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#profile-preview').attr('src', e.target.result).removeClass('d-none');
                    $('#profile-placeholder').addClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Initialize Select2
        $('.select2').select2();

        // Governorate → City cascade
        $('#governorate_id').on('change', function() {
            var governorateId = $(this).val();
            var $citySelect = $('#city_id');
            $citySelect.empty().append('<option value="">{{ __("request_nurse.lbl_select_city") }}</option>');

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
    });
</script>
@endpush
