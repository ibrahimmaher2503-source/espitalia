@extends('frontend::layouts.master')

@section('title', __('frontend.edit_profile'))

@section('content')

@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- Page header --}}
        <div class="pp-page-header">
            <h1 class="pp-page-title">{{ __('frontend.edit_profile') }}</h1>
            <p class="pp-page-sub">{{ __('frontend.manage_personal_info') ?? 'Manage your personal information' }}</p>
        </div>

        <div class="row justify-content-center g-4">

            {{-- ── Avatar column ── --}}
            <div class="col-lg-3">
                <div class="pp-card text-center" style="padding:2rem 1.5rem">
                    <div class="pp-profile-avatar-wrap" style="margin:0 auto 1.25rem">
                        <img src="{{ $user->profile_image }}"
                             id="profileImage"
                             alt="{{ $user->first_name }}"
                             class="pp-profile-avatar"
                             style="width:88px;height:88px">
                        <button type="button"
                                class="pp-profile-edit-btn"
                                onclick="document.getElementById('fileInput').click()"
                                aria-label="{{ __('frontend.change_photo') ?? 'Change photo' }}">
                            <i class="ph ph-camera" aria-hidden="true"></i>
                        </button>
                        <input type="file" id="fileInput" style="display:none" accept="image/*"
                               onchange="previewImage(event)">
                    </div>
                    <p class="pp-profile-name">{{ $user->first_name }} {{ $user->last_name }}</p>
                    <p class="pp-profile-email">{{ $user->email }}</p>
                    <p style="font-size:0.72rem;color:var(--pp-xmuted);margin-top:0.75rem;font-family:var(--pp-font)">
                        {{ __('frontend.click_camera_to_change') ?? 'Click the camera icon to update your photo' }}
                    </p>
                </div>
            </div>

            {{-- ── Info column ── --}}
            <div class="col-lg-9">
                <div class="pp-card">
                    <div class="pp-card-header">
                        <h2 class="pp-card-title">
                            <i class="ph ph-user-circle" aria-hidden="true"></i>
                            {{ __('frontend.personal_information') ?? 'Personal Information' }}
                        </h2>
                        <button type="button"
                                class="pp-btn pp-btn-teal"
                                data-bs-toggle="modal"
                                data-bs-target="#edit-profile-modal">
                            <i class="ph ph-pencil-simple" aria-hidden="true"></i>
                            {{ __('messages.edit') }}
                        </button>
                    </div>
                    <div class="pp-card-body">
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_first_name') ?? 'First Name' }}</span>
                            <span class="pp-info-row-value" id="first_name_display">{{ $user->first_name }}</span>
                        </div>
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_last_name') ?? 'Last Name' }}</span>
                            <span class="pp-info-row-value" id="last_name_value">{{ $user->last_name }}</span>
                        </div>
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_email') ?? 'Email' }}</span>
                            <span class="pp-info-row-value" id="email_value">{{ $user->email }}</span>
                        </div>
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_mobile') ?? 'Phone' }}</span>
                            <span class="pp-info-row-value" id="mobile_value">{{ $user->mobile }}</span>
                        </div>
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_dob') ?? 'Date of Birth' }}</span>
                            <span class="pp-info-row-value" id="date_of_birth_value">{{ $user->date_of_birth }}</span>
                        </div>
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_gender') ?? 'Gender' }}</span>
                            <span class="pp-info-row-value" id="gender_value" style="text-transform:capitalize">
                                {{ $user->gender }}
                            </span>
                        </div>
                        <div class="pp-info-row">
                            <span class="pp-info-row-label">{{ __('messages.lbl_address') ?? 'Address' }}</span>
                            <span class="pp-info-row-value" id="address_value">{{ $user->address }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════
     EDIT PROFILE MODAL
     ═══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal"
     id="edit-profile-modal"
     tabindex="-1"
     aria-labelledby="editProfileModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">
                    <i class="ph ph-pencil-simple me-2" aria-hidden="true"></i>
                    {{ __('frontend.edit_profile') ?? 'Edit Profile' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>

            <div class="modal-body">
                <form id="edit-profile-form" method="POST" action="{{ route('update-profile') }}">
                    @csrf
                    <div class="row g-3">

                        <div class="col-md-6">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label" for="first_name">
                                    {{ __('messages.lbl_first_name') ?? 'First Name' }}
                                    <span class="pp-required">*</span>
                                </label>
                                <input type="text"
                                       id="first_name"
                                       name="first_name"
                                       class="pp-input"
                                       placeholder="{{ __('messages.lbl_first_name') ?? 'First Name' }}"
                                       value="{{ $user->first_name ?? '' }}">
                                <div class="pp-field-error" id="error-first_name"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label" for="last_name">
                                    {{ __('messages.lbl_last_name') ?? 'Last Name' }}
                                    <span class="pp-required">*</span>
                                </label>
                                <input type="text"
                                       id="last_name"
                                       name="last_name"
                                       class="pp-input"
                                       placeholder="{{ __('messages.lbl_last_name') ?? 'Last Name' }}"
                                       value="{{ $user->last_name ?? '' }}">
                                <div class="pp-field-error" id="error-last_name"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label" for="email_field">
                                    {{ __('messages.lbl_email') ?? 'Email' }}
                                </label>
                                <input type="email"
                                       id="email_field"
                                       name="email"
                                       class="pp-input"
                                       placeholder="{{ __('messages.lbl_email') ?? 'Email' }}"
                                       value="{{ $user->email ?? '' }}"
                                       readonly
                                       style="opacity:0.65;cursor:not-allowed">
                                <div class="pp-field-error" id="error-email"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label" for="mobile">
                                    {{ __('messages.lbl_mobile') ?? 'Phone Number' }}
                                    <span class="pp-required">*</span>
                                </label>
                                <input type="text"
                                       id="mobile"
                                       name="mobile"
                                       class="pp-input"
                                       placeholder="{{ __('messages.lbl_mobile') ?? 'Phone Number' }}"
                                       value="{{ $user->mobile ?? '' }}">
                                <div class="pp-field-error" id="error-mobile"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label" for="date_of_birth">
                                    {{ __('messages.lbl_dob') ?? 'Date of Birth' }}
                                    <span class="pp-required">*</span>
                                </label>
                                <input type="date"
                                       id="date_of_birth"
                                       name="date_of_birth"
                                       class="pp-input date-picker"
                                       value="{{ $user->date_of_birth ?? '' }}"
                                       max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"
                                       required>
                                <div class="pp-field-error" id="error-date_of_birth"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label" for="address_field">
                                    {{ __('messages.lbl_address') ?? 'Address' }}
                                </label>
                                <input type="text"
                                       id="address_field"
                                       name="address"
                                       class="pp-input"
                                       placeholder="{{ __('messages.lbl_address') ?? 'Address' }}"
                                       value="{{ $user->address ?? '' }}">
                                <div class="pp-field-error" id="error-address"></div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="pp-form-group mb-0">
                                <label class="pp-label">
                                    {{ __('messages.lbl_gender') ?? 'Gender' }}
                                    <span class="pp-required">*</span>
                                </label>
                                <div class="d-flex flex-wrap gap-3 mt-1" id="gender">
                                    @foreach(['male', 'female', 'other'] as $genderOpt)
                                        <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;font-family:var(--pp-font);cursor:pointer;color:var(--pp-text-2)">
                                            <input type="radio"
                                                   name="gender"
                                                   value="{{ $genderOpt }}"
                                                   required
                                                   style="accent-color:var(--pp-teal);width:16px;height:16px"
                                                   {{ (empty($user->gender) && $genderOpt === 'male') || $user->gender === $genderOpt ? 'checked' : '' }}>
                                            {{ ucfirst($genderOpt) }}
                                        </label>
                                    @endforeach
                                </div>
                                <div class="pp-field-error" id="error-gender"></div>
                            </div>
                        </div>

                    </div>{{-- /.row --}}
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">
                    {{ __('messages.close') }}
                </button>
                <button type="submit" form="edit-profile-form" class="pp-btn pp-btn-teal">
                    <i class="ph ph-floppy-disk" aria-hidden="true"></i>
                    {{ __('messages.update') ?? 'Update' }}
                </button>
            </div>

        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════
     SUCCESS MODAL
     ═══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal"
     id="update-profile"
     tabindex="-1"
     aria-labelledby="profileSuccessLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profileSuccessLabel">
                    <i class="ph ph-check-circle me-2" style="color:var(--pp-completed)"></i>
                    {{ __('messages.success') ?? 'Success' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div style="width:56px;height:56px;background:var(--pp-completed-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    <i class="ph ph-check-circle" style="font-size:1.75rem;color:var(--pp-completed)"></i>
                </div>
                <h6 style="font-weight:700;color:var(--pp-navy);margin-bottom:0.375rem;font-family:var(--pp-font)">
                    {{ __('frontend.profile_success_message') }}
                </h6>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button"
                        class="pp-btn pp-btn-teal"
                        data-bs-dismiss="modal"
                        onclick="window.location.reload()">
                    {{ __('messages.ok') ?? 'OK' }}
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-scripts')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>

<script>
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ── flatpickr for DOB ── */
        if (typeof flatpickr !== 'undefined') {
            flatpickr('.date-picker', { dateFormat: 'Y-m-d', maxDate: 'today' });
        }

        /* ── intl-tel-input for mobile ── */
        var mobileInput = document.getElementById('mobile');
        var iti = null;
        if (mobileInput && typeof window.intlTelInput !== 'undefined') {
            iti = window.intlTelInput(mobileInput, {
                initialCountry: 'auto',
                separateDialCode: true,
                utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
            });

            var editModal = document.getElementById('edit-profile-modal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function () {
                    var val = mobileInput.value;
                    if (val.startsWith('+')) {
                        iti.setNumber(val);
                    }
                });
            }

            function syncToFull() {
                var full = iti.getNumber();
                if (full) mobileInput.value = full;
            }
            mobileInput.addEventListener('countrychange', syncToFull);
            mobileInput.addEventListener('blur', syncToFull);
        }

        /* ── Form submit ── */
        document.getElementById('edit-profile-form').addEventListener('submit', function (e) {
            e.preventDefault();

            var form     = this;
            var formData = new FormData(form);
            var isValid  = true;

            document.querySelectorAll('.pp-field-error').forEach(function (el) {
                el.textContent = '';
            });

            function setError(field, msg) {
                var el = document.getElementById('error-' + field);
                if (el) el.textContent = msg;
                isValid = false;
            }

            ['first_name', 'last_name', 'mobile', 'date_of_birth', 'gender'].forEach(function (field) {
                var val = formData.get(field);
                if (!val || val.trim() === '') {
                    var label = field.replace(/_/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
                    setError(field, label + ' is required.');
                }
            });

            if (!isValid) return;

            var submitBtn = form.querySelector('[type="submit"]');
            submitBtn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                submitBtn.disabled = false;
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('edit-profile-modal')).hide();
                    new bootstrap.Modal(document.getElementById('update-profile')).show();
                } else {
                    if (typeof window.errorSnackbar !== 'undefined') {
                        window.errorSnackbar('{{ __("messages.something_went_wrong") ?? "An error occurred" }}');
                    }
                }
            })
            .catch(function () {
                submitBtn.disabled = false;
            });
        });

        /* ── Reset on modal close ── */
        document.getElementById('edit-profile-modal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('edit-profile-form').reset();
            document.querySelectorAll('.pp-field-error').forEach(function (el) {
                el.textContent = '';
            });
        });

    });

    /* ── Profile image preview + upload ── */
    window.previewImage = function (event) {
        var input  = event.target;
        var reader = new FileReader();

        reader.onload = function () {
            document.getElementById('profileImage').src = reader.result;
        };
        reader.readAsDataURL(input.files[0]);

        var formData = new FormData();
        formData.append('profile_image', input.files[0]);

        fetch('{{ route('update-profile-image') }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && typeof window.successSnackbar !== 'undefined') {
                window.successSnackbar('{{ __("frontend.profile_image_updated") ?? "Profile image updated successfully!" }}');
            }
        })
        .catch(function (err) {
            console.error('Image upload error:', err);
        });
    };

}());
</script>
@endpush
