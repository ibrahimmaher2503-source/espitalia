@extends('frontend::layouts.master')

@section('title', __('frontend.other_patient'))

@section('content')
@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- ── Page header ── --}}
        <div class="pp-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <h1 class="pp-page-title">{{ __('messages.all_patients') }}</h1>
            </div>
            <button id="addPatientBtn"
                    class="pp-btn pp-btn-teal"
                    data-bs-toggle="modal"
                    data-bs-target="#addPatientModal">
                <i class="ph ph-plus-circle" aria-hidden="true"></i>
                {{ __('messages.add_patient') }}
            </button>
        </div>

        {{-- ── Shimmer ── --}}
        <div id="shimmer-loader" class="d-flex gap-3 flex-wrap">
            @for ($i = 0; $i < 6; $i++)
                @include('frontend::components.card.shimmer_other_patient_card')
            @endfor
        </div>

        {{-- ── DataTable ── --}}
        <div id="patientsContainer">
            <table id="datatable" class="table table-responsive custom-card-table d-none"></table>
        </div>

        {{-- ── Empty state ── --}}
        <div id="emptyState" class="d-none">
            <div class="pp-card text-center" style="padding:3rem 1.5rem">
                <div class="pp-empty">
                    <i class="ph ph-users" aria-hidden="true"></i>
                    <p>{{ __('messages.no_data_found') }}</p>
                    <a href="#" class="pp-btn pp-btn-teal"
                       style="margin-top:0.75rem"
                       data-bs-toggle="modal"
                       data-bs-target="#addPatientModal">
                        <i class="ph ph-plus-circle"></i>{{ __('messages.add_patient') }}
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ── Add / Edit Patient Modal ── --}}
<div class="modal fade pp-modal" id="addPatientModal" tabindex="-1" aria-labelledby="addPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPatientModalLabel">{{ __('customer.add_new_patient') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('frontend.close') }}"></button>
            </div>
            <div class="modal-body">
                <form id="patientForm" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                    <input type="hidden" name="patient_id" id="patientId">

                    <div class="row g-4">

                        {{-- ── Avatar column ── --}}
                        <div class="col-lg-4 d-flex flex-column align-items-center justify-content-start gap-3 pt-3">
                            <img id="miniLogoViewer"
                                 src="{{ asset('img/avatar/avatar.webp') }}"
                                 class="pp-patient-avatar"
                                 style="width:110px;height:110px;border-width:3px"
                                 alt="{{ __('messages.profile_picture') }}">
                            <input type="file" class="d-none" id="mini_logo" name="profile_image" accept=".jpeg,.jpg,.png,.gif">
                            <div class="d-flex gap-2">
                                <button type="button"
                                        class="pp-btn pp-btn-outline"
                                        onclick="document.getElementById('mini_logo').click()">
                                    <i class="ph ph-upload-simple"></i>{{ __('messages.upload') }}
                                </button>
                                <button type="button"
                                        class="pp-btn pp-btn-outline d-none"
                                        id="removeMiniLogoButton"
                                        style="color:var(--pp-cancelled);border-color:var(--pp-cancelled)">
                                    <i class="ph ph-trash"></i>{{ __('messages.remove') }}
                                </button>
                            </div>
                            <small id="error_mini_logo" class="pp-field-error"></small>
                        </div>

                        {{-- ── Fields column ── --}}
                        <div class="col-lg-8">
                            <div class="row g-3">

                                {{-- First name --}}
                                <div class="col-xl-6 col-12">
                                    <div class="pp-form-group" id="fg_first_name">
                                        <label class="pp-label" for="firstName">
                                            {{ __('clinic.lbl_first_name') }}<span class="pp-required">*</span>
                                        </label>
                                        <div class="pp-input-group">
                                            <input type="text" class="pp-input" name="first_name"
                                                   id="firstName" placeholder="{{ __('clinic.lbl_first_name') }}" required>
                                            <span class="pp-input-icon"><i class="ph ph-user"></i></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Last name --}}
                                <div class="col-xl-6 col-12">
                                    <div class="pp-form-group" id="fg_last_name">
                                        <label class="pp-label" for="lastName">
                                            {{ __('clinic.lbl_last_name') }}<span class="pp-required">*</span>
                                        </label>
                                        <div class="pp-input-group">
                                            <input type="text" class="pp-input" name="last_name"
                                                   id="lastName" placeholder="{{ __('clinic.lbl_last_name') }}" required>
                                            <span class="pp-input-icon"><i class="ph ph-user"></i></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Phone --}}
                                <div class="col-xl-6 col-12">
                                    <div class="pp-form-group" id="fg_contactNumber">
                                        <label class="pp-label" for="contactNumber">
                                            {{ __('clinic.lbl_phone_number') }}<span class="pp-required">*</span>
                                        </label>
                                        <div class="pp-input-group">
                                            <input type="tel" class="pp-input" name="contactNumber"
                                                   id="contactNumber" placeholder="{{ __('clinic.lbl_phone_number') }}" required>
                                            <span class="pp-input-icon"><i class="ph ph-phone"></i></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Date of birth --}}
                                <div class="col-xl-6 col-12">
                                    <div class="pp-form-group" id="fg_dob">
                                        <label class="pp-label" for="dob">
                                            {{ __('clinic.date_of_birth') }}<span class="pp-required">*</span>
                                        </label>
                                        <div class="pp-input-group">
                                            <input type="date" class="pp-input" name="dob"
                                                   id="dob" required>
                                            <span class="pp-input-icon"><i class="ph ph-cake"></i></span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Gender --}}
                                <div class="col-12">
                                    <div class="pp-form-group" id="fg_gender">
                                        <label class="pp-label">{{ __('clinic.lbl_gender') }}<span class="pp-required">*</span></label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="gender" value="Male" id="genderMale" required>
                                                <label class="form-check-label rounded-pill" for="genderMale">{{ __('messages.male') }}</label>
                                            </div>
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="gender" value="Female" id="genderFemale" required>
                                                <label class="form-check-label rounded-pill" for="genderFemale">{{ __('messages.female') }}</label>
                                            </div>
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="gender" value="Other" id="genderOther" required>
                                                <label class="form-check-label rounded-pill" for="genderOther">{{ __('messages.lbl_other') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Relation --}}
                                <div class="col-12">
                                    <div class="pp-form-group" id="fg_relation">
                                        <label class="pp-label">{{ __('clinic.relation') }}<span class="pp-required">*</span></label>
                                        <div class="d-flex flex-wrap gap-2">
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="relation" id="relationParent" value="Parents" required>
                                                <label class="form-check-label rounded-pill" for="relationParent">{{ __('clinic.parents') }}</label>
                                            </div>
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="relation" id="relationSibling" value="Siblings" required>
                                                <label class="form-check-label rounded-pill" for="relationSibling">{{ __('clinic.sibling') }}</label>
                                            </div>
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="relation" id="relationSpouse" value="Spouse" required>
                                                <label class="form-check-label rounded-pill" for="relationSpouse">{{ __('clinic.spouse') }}</label>
                                            </div>
                                            <div class="form-check custom-radio-btn">
                                                <input class="form-check-input" type="radio" name="relation" id="relationOther" value="Other" required>
                                                <label class="form-check-label rounded-pill" for="relationOther">{{ __('messages.lbl_other') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                <button type="button" class="pp-btn pp-btn-teal" id="savePatient">{{ __('messages.save') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- ── Delete Confirmation Modal ── --}}
<div class="modal fade pp-modal pp-modal-danger" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body" style="padding:2rem 1.5rem">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('frontend.close') }}" style="position:absolute;top:1rem;right:1rem"></button>
                <div class="text-center">
                    <div class="mb-3">
                        <i class="ph ph-trash" aria-hidden="true"
                           style="font-size:3rem;color:var(--pp-cancelled);background:var(--pp-cancelled-bg);border-radius:50%;padding:0.75rem"></i>
                    </div>
                    <h5 class="pp-page-title" style="font-size:1.05rem" id="deleteModalLabel">
                        {{ __('messages.are_you_sure_want_to_delete') }}
                    </h5>
                    <p style="color:var(--pp-muted);font-size:0.82rem;margin-bottom:1.5rem">
                        {{ __('messages.deleting_patient_will_remove_permanently') }}
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">{{ __('messages.cancel') }}</button>
                        <button type="button" id="confirmDeleteBtn"
                                class="pp-btn"
                                style="background:var(--pp-cancelled);color:#fff">
                            <i class="ph ph-trash"></i>{{ __('messages.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('after-styles')
    <link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
    <style>
        .pp-btn.btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.75;
        }
        .pp-btn.btn-loading::after {
            content: '';
            width: 0.9rem;
            height: 0.9rem;
            position: absolute;
            top: calc(50% - 0.45rem);
            right: 0.6rem;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: pp-spin 0.6s linear infinite;
        }
        @keyframes pp-spin { to { transform: rotate(1turn); } }
        /* intl-tel-input integration */
        .iti { width: 100%; }
        .iti__flag-container + .pp-input { padding-left: 52px; }
        .iti--separate-dial-code .iti__selected-flag { background: var(--pp-navy-50); border-right: 1px solid var(--pp-border-mid); border-radius: var(--pp-r-sm) 0 0 var(--pp-r-sm); }
    </style>
@endpush

@push('after-scripts')
    <script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
    <script>
    (function () {
        'use strict';

        var defaultImagePath = '{{ asset('img/avatar/avatar.webp') }}';
        var today            = new Date().toISOString().split('T')[0];

        /* ── DataTable ── */
        var shimmerLoader = document.getElementById('shimmer-loader');
        var dataTableEl   = document.getElementById('datatable');

        document.addEventListener('DOMContentLoaded', function () {
            frontInitDatatable({
                url: '{{ route('manage-profile-data') }}',
                finalColumns: [{ data: 'card', name: 'card', orderable: false, searchable: true }],
                cardColumnClass: 'row-cols-1',
                onLoadStart: function () {
                    shimmerLoader.classList.remove('d-none');
                    dataTableEl.classList.add('d-none');
                },
                onLoadComplete: function () {
                    shimmerLoader.classList.add('d-none');
                    dataTableEl.classList.remove('d-none');
                }
            });
        });

        /* ── intl-tel-input ── */
        var contactInput = document.getElementById('contactNumber');
        var iti = window.intlTelInput(contactInput, {
            initialCountry: 'in',
            separateDialCode: true,
            utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js'
        });
        contactInput.addEventListener('countrychange', function () { contactInput.value = iti.getNumber(); });
        contactInput.addEventListener('blur',          function () { contactInput.value = iti.getNumber(); });

        /* ── Flatpickr for DOB ── */
        flatpickr('#dob', { dateFormat: 'Y-m-d', maxDate: today });

        /* ── CSRF ── */
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        /* ── Avatar upload ── */
        function toggleRemoveButton(src) {
            var btn = document.getElementById('removeMiniLogoButton');
            if (src === defaultImagePath) btn.classList.add('d-none');
            else btn.classList.remove('d-none');
        }

        document.getElementById('mini_logo').addEventListener('change', function () {
            var file = this.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('miniLogoViewer').src = e.target.result;
                document.getElementById('removeMiniLogoButton').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('removeMiniLogoButton').addEventListener('click', function () {
            document.getElementById('miniLogoViewer').src = defaultImagePath;
            document.getElementById('mini_logo').value    = '';
            this.classList.add('d-none');
        });

        /* ── Add patient button ── */
        document.getElementById('addPatientBtn').addEventListener('click', function () {
            document.getElementById('patientForm').reset();
            document.getElementById('patientId').val = '';
            document.getElementById('addPatientModalLabel').textContent = '{{ __('customer.add_new_patient') }}';
            document.getElementById('miniLogoViewer').src = defaultImagePath;
            document.getElementById('removeMiniLogoButton').classList.add('d-none');
        });

        /* ── Edit ── */
        document.getElementById('patientsContainer').addEventListener('click', function (e) {
            var editBtn = e.target.closest('.editBtn');
            if (!editBtn) return;
            var patientId = editBtn.dataset.id;
            $.ajax({
                url: '{{ route('other-patients.edit', ['id' => '__id__']) }}'.replace('__id__', patientId),
                type: 'GET',
                success: function (res) {
                    if (!res.status) { window.errorSnackbar(res.message); return; }
                    var p = res.data;
                    document.getElementById('patientId').value = p.id;
                    document.getElementById('firstName').value  = p.first_name;
                    document.getElementById('lastName').value   = p.last_name;
                    document.getElementById('contactNumber').value = p.contactNumber;
                    document.getElementById('dob').value        = p.dob;
                    var gIn = document.querySelector('input[name="gender"][value="' + p.gender + '"]');
                    if (gIn) gIn.checked = true;
                    var rIn = document.querySelector('input[name="relation"][value="' + p.relation + '"]');
                    if (rIn) rIn.checked = true;
                    var img = p.profile_image || defaultImagePath;
                    document.getElementById('miniLogoViewer').src = img;
                    toggleRemoveButton(img);
                    document.getElementById('addPatientModalLabel').textContent = '{{ __('frontend.edit_patient') }}';
                    new bootstrap.Modal(document.getElementById('addPatientModal')).show();
                },
                error: function () { window.errorSnackbar('{{ __('messages.something_went_wrong') }}'); }
            });
        });

        /* ── Delete (open modal) ── */
        document.getElementById('patientsContainer').addEventListener('click', function (e) {
            var delBtn = e.target.closest('.deleteBtn');
            if (!delBtn) return;
            document.getElementById('confirmDeleteBtn').dataset.id = delBtn.dataset.id;
            new bootstrap.Modal(document.getElementById('deleteConfirmationModal')).show();
        });

        /* ── Delete (confirm) ── */
        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            var patientId = this.dataset.id;
            $.ajax({
                url: '{{ route('other-patients.destroy', ['id' => '__id__']) }}'.replace('__id__', patientId),
                type: 'DELETE',
                success: function () {
                    window.successSnackbar('{{ __('messages.patient_deleted_success') }}');
                    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmationModal')).hide();
                    if (window.renderedDataTable) window.renderedDataTable.ajax.reload(null, false);
                },
                error: function () { window.errorSnackbar('{{ __('messages.something_went_wrong') }}'); }
            });
        });

        /* ── Save (create / update) ── */
        document.getElementById('savePatient').addEventListener('click', function () {
            var $btn   = $(this);
            var origHtml = $btn.html();
            clearErrors();

            var form   = document.getElementById('patientForm');
            var fd     = new FormData(form);
            var hasErr = false;

            if (!fd.get('first_name'))   { showError('fg_first_name',     '{{ __('messages.first_name_required') }}'); hasErr = true; }
            if (!fd.get('last_name'))    { showError('fg_last_name',      '{{ __('messages.last_name_required') }}'); hasErr = true; }
            if (!fd.get('contactNumber')){ showError('fg_contactNumber',  '{{ __('messages.contact_number_required') }}'); hasErr = true; }
            if (!fd.get('dob'))          { showError('fg_dob',            '{{ __('messages.dob_required') }}'); hasErr = true; }
            else if (fd.get('dob') >= today) { showError('fg_dob', '{{ __('messages.dob_required') }}'); hasErr = true; }
            if (!fd.get('gender'))       { showError('fg_gender',         '{{ __('messages.gender_required') }}'); hasErr = true; }
            if (!fd.get('relation'))     { showError('fg_relation',       '{{ __('messages.relation_required') }}'); hasErr = true; }

            if (hasErr) return;

            $btn.addClass('btn-loading').html('{{ __('messages.saving') }}...').prop('disabled', true);

            var patientId = document.getElementById('patientId').value;
            var url = patientId
                ? '{{ route('other-patients.update', ['id' => '__id__']) }}'.replace('__id__', patientId)
                : '{{ route('other-patients.store') }}';

            $.ajax({
                url: url,
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function () {
                    window.successSnackbar('{{ __('messages.patient_saved_successfully') }}');
                    bootstrap.Modal.getInstance(document.getElementById('addPatientModal')).hide();
                    document.getElementById('patientForm').reset();
                    if (window.renderedDataTable) window.renderedDataTable.ajax.reload(null, false);
                },
                error: function () {
                    window.errorSnackbar('{{ __('messages.something_went_wrong') }}');
                },
                complete: function () {
                    $btn.removeClass('btn-loading').html(origHtml).prop('disabled', false);
                }
            });
        });

        function clearErrors() {
            document.querySelectorAll('.pp-field-error').forEach(function (el) { el.remove(); });
            document.querySelectorAll('.pp-input.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        }

        function showError(groupId, message) {
            var group = document.getElementById(groupId);
            if (!group) return;
            var inp = group.querySelector('.pp-input');
            if (inp) inp.classList.add('is-invalid');
            var err = document.createElement('small');
            err.className   = 'pp-field-error';
            err.textContent = message;
            group.appendChild(err);
        }
    }());
    </script>
@endpush
