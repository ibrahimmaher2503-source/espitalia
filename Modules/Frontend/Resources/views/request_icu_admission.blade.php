@extends('frontend::layouts.master')
@section('title', __('intensive_care.admission_request'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- Page header --}}
            <div class="pp-page-header">
                <h1 class="pp-page-title">{{ __('intensive_care.admission_request') }}</h1>
            </div>

            <div class="row gy-4">

                {{-- ── Form Column ── --}}
                <div class="col-lg-8">

                    {{-- Validation errors --}}
                    @if($errors->any())
                        <div class="pp-cancel-alert mb-4">
                            <p class="pp-cancel-alert-title">
                                <i class="ph ph-warning-circle" aria-hidden="true"></i>
                                {{ __('messages.please_fix_errors') ?? 'Please fix the following errors:' }}
                            </p>
                            <ul class="mb-0 ps-3" style="font-size:0.82rem;color:var(--pp-cancelled);font-family:var(--pp-font)">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('request-icu-admission.store') }}" id="icu-admission-form" enctype="multipart/form-data">
                        @csrf

                        {{-- ── Section 1: Hospital Selection ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-hospital" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.hospital_singular') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="row gx-3">
                                    <div class="col-md-6">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="hospital-select">
                                                {{ __('intensive_care.lbl_hospital_name') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <select name="hospital_id" id="hospital-select" class="pp-select select2" required>
                                                <option value="">{{ __('intensive_care.lbl_hospital_name') }}...</option>
                                                @foreach($hospitals as $hospital)
                                                    <option value="{{ $hospital->id }}"
                                                            {{ (old('hospital_id', $selectedHospitalId) == $hospital->id) ? 'selected' : '' }}>
                                                        {{ $hospital->name }} - {{ $hospital->city }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('hospital_id')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="pp-form-group mb-0">
                                            <label class="pp-label" for="department-select">
                                                {{ __('intensive_care.department_singular') }}
                                            </label>
                                            <select name="icu_department_id" id="department-select" class="pp-select select2">
                                                <option value="">{{ __('intensive_care.lbl_any_available') }}</option>
                                            </select>
                                            <div id="department-loading" style="display:none;font-size:0.78rem;color:var(--pp-muted);margin-top:0.35rem;font-family:var(--pp-font)">
                                                <span class="spinner-border spinner-border-sm me-1"></span>{{ __('intensive_care.lbl_loading_departments') }}
                                            </div>
                                            <div id="department-empty" style="display:none;font-size:0.78rem;color:var(--pp-warning,#f39c12);margin-top:0.35rem;font-family:var(--pp-font)">
                                                <i class="ph ph-warning me-1" aria-hidden="true"></i>{{ __('intensive_care.lbl_no_departments') }}
                                            </div>
                                            @error('icu_department_id')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 2: Patient Information ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-user" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.lbl_patient_info') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="row gx-3">
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="patient_name">
                                                {{ __('intensive_care.lbl_patient_name') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="text" class="pp-input" id="patient_name" name="patient_name"
                                                   value="{{ old('patient_name') }}" required maxlength="255"
                                                   placeholder="{{ __('intensive_care.lbl_patient_name') }}">
                                            @error('patient_name')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="patient_age">
                                                {{ __('intensive_care.lbl_patient_age') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="number" class="pp-input" id="patient_age" name="patient_age"
                                                   value="{{ old('patient_age') }}" required min="0" max="150"
                                                   placeholder="—">
                                            @error('patient_age')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="patient_gender">
                                                {{ __('intensive_care.lbl_patient_gender') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <select name="patient_gender" id="patient_gender" class="pp-select" required>
                                                <option value="">{{ __('intensive_care.lbl_patient_gender') }}...</option>
                                                <option value="male"   {{ old('patient_gender') === 'male'   ? 'selected' : '' }}>{{ __('intensive_care.gender_male') }}</option>
                                                <option value="female" {{ old('patient_gender') === 'female' ? 'selected' : '' }}>{{ __('intensive_care.gender_female') }}</option>
                                            </select>
                                            @error('patient_gender')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="national_id">
                                                {{ __('intensive_care.lbl_national_id') }}
                                            </label>
                                            <input type="text" class="pp-input" id="national_id" name="national_id"
                                                   value="{{ old('national_id') }}" maxlength="50"
                                                   placeholder="{{ __('intensive_care.lbl_national_id') }}">
                                            @error('national_id')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group mb-0">
                                            <label class="pp-label" for="insurance_number">
                                                {{ __('intensive_care.lbl_insurance_number') }}
                                            </label>
                                            <input type="text" class="pp-input" id="insurance_number" name="insurance_number"
                                                   value="{{ old('insurance_number') }}" maxlength="100"
                                                   placeholder="{{ __('intensive_care.lbl_insurance_number') }}">
                                            @error('insurance_number')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 3: Clinical Details ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-first-aid-kit" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.lbl_clinical_info') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="row gx-3">
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="case_type">
                                                {{ __('intensive_care.lbl_case_type') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <select name="case_type" id="case_type" class="pp-select select2" required>
                                                <option value="">{{ __('intensive_care.lbl_case_type') }}...</option>
                                                @foreach(\Modules\IntensiveCare\Models\IcuAdmissionRequest::CASE_TYPES as $type)
                                                    <option value="{{ $type }}" {{ old('case_type') === $type ? 'selected' : '' }}>
                                                        {{ __('intensive_care.case_' . $type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('case_type')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="urgency">
                                                {{ __('intensive_care.lbl_urgency') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <select name="urgency" id="urgency" class="pp-select select2" required>
                                                <option value="">{{ __('intensive_care.lbl_urgency') }}...</option>
                                                @foreach(\Modules\IntensiveCare\Models\IcuAdmissionRequest::URGENCY_LEVELS as $level)
                                                    <option value="{{ $level }}" {{ old('urgency') === $level ? 'selected' : '' }}>
                                                        {{ __('intensive_care.urgency_' . $level) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('urgency')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="current_location">
                                                {{ __('intensive_care.lbl_current_location') }}
                                            </label>
                                            <input type="text" class="pp-input" id="current_location" name="current_location"
                                                   value="{{ old('current_location') }}" maxlength="500"
                                                   placeholder="{{ __('intensive_care.lbl_current_location') }}">
                                            @error('current_location')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Equipment needs checkboxes --}}
                                <div class="d-flex flex-wrap gap-4 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="needs_ventilator" value="1" id="needs-ventilator"
                                               {{ old('needs_ventilator') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="needs-ventilator" style="font-family:var(--pp-font);font-size:0.82rem">
                                            <i class="ph ph-wind me-1" aria-hidden="true"></i>{{ __('intensive_care.lbl_needs_ventilator') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="needs_oxygen" value="1" id="needs-oxygen"
                                               {{ old('needs_oxygen') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="needs-oxygen" style="font-family:var(--pp-font);font-size:0.82rem">
                                            <i class="ph ph-drop me-1" aria-hidden="true"></i>{{ __('intensive_care.lbl_needs_oxygen') }}
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="needs_ambulance" value="1" id="needs-ambulance"
                                               {{ old('needs_ambulance') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="needs-ambulance" style="font-family:var(--pp-font);font-size:0.82rem">
                                            <i class="ph ph-ambulance me-1" aria-hidden="true"></i>{{ __('intensive_care.lbl_needs_ambulance') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 4: Medical Condition & Files ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.lbl_medical_condition') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="pp-form-group">
                                    <label class="pp-label" for="medical_condition">
                                        {{ __('intensive_care.lbl_medical_condition') }}
                                        <span class="pp-required">*</span>
                                    </label>
                                    <textarea class="pp-input" id="medical_condition" name="medical_condition"
                                              rows="4" style="height:auto" required maxlength="5000"
                                              placeholder="{{ __('intensive_care.lbl_medical_condition') }}...">{{ old('medical_condition') }}</textarea>
                                    @error('medical_condition')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="pp-form-group">
                                    <label class="pp-label" for="diagnosis">
                                        {{ __('intensive_care.lbl_diagnosis') }}
                                    </label>
                                    <input type="text" class="pp-input" id="diagnosis" name="diagnosis"
                                           value="{{ old('diagnosis') }}" maxlength="500"
                                           placeholder="{{ __('intensive_care.lbl_diagnosis') }}">
                                    @error('diagnosis')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="pp-form-group mb-0">
                                    <label class="pp-label" for="medical_reports">
                                        {{ __('intensive_care.lbl_medical_reports') }}
                                    </label>
                                    <input type="file" class="pp-input" id="medical_reports" name="medical_reports[]"
                                           multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                    <p style="font-size:0.72rem;color:var(--pp-muted);margin-top:0.35rem;font-family:var(--pp-font)">
                                        {{ __('intensive_care.lbl_file_format_hint') }}
                                    </p>
                                    @error('medical_reports.*')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 5: Contact & Payment ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-phone" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('intensive_care.lbl_contact_info') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="row gx-3">
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="contact_name">
                                                {{ __('intensive_care.lbl_contact_name') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="text" class="pp-input" id="contact_name" name="contact_name"
                                                   value="{{ old('contact_name') }}" required maxlength="255"
                                                   placeholder="{{ __('intensive_care.lbl_contact_name') }}">
                                            @error('contact_name')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="contact_phone">
                                                {{ __('intensive_care.lbl_contact_phone') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="text" class="pp-input" id="contact_phone" name="contact_phone"
                                                   value="{{ old('contact_phone') }}" required maxlength="50"
                                                   placeholder="{{ __('intensive_care.lbl_contact_phone') }}">
                                            @error('contact_phone')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="relationship_to_patient">
                                                {{ __('intensive_care.lbl_relationship') }}
                                            </label>
                                            <input type="text" class="pp-input" id="relationship_to_patient" name="relationship_to_patient"
                                                   value="{{ old('relationship_to_patient') }}" maxlength="100"
                                                   placeholder="{{ __('intensive_care.lbl_relationship') }}">
                                            @error('relationship_to_patient')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group mb-0">
                                            <label class="pp-label" for="payment-method-select">
                                                {{ __('intensive_care.lbl_payment_method') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <select name="payment_method" id="payment-method-select" class="pp-select" required>
                                                <option value="">{{ __('intensive_care.lbl_payment_method') }}...</option>
                                                <option value="insurance" {{ old('payment_method') === 'insurance' ? 'selected' : '' }}>{{ __('intensive_care.payment_insurance') }}</option>
                                                <option value="cash"      {{ old('payment_method') === 'cash'      ? 'selected' : '' }}>{{ __('intensive_care.payment_cash') }}</option>
                                            </select>
                                            @error('payment_method')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="insurance-provider-group"
                                         style="{{ old('payment_method') === 'insurance' ? '' : 'display:none;' }}">
                                        <div class="pp-form-group mb-0">
                                            <label class="pp-label" for="insurance_provider">
                                                {{ __('intensive_care.lbl_insurance_provider') }}
                                            </label>
                                            <input type="text" class="pp-input" id="insurance_provider" name="insurance_provider"
                                                   value="{{ old('insurance_provider') }}" maxlength="255"
                                                   placeholder="{{ __('intensive_care.lbl_insurance_provider') }}">
                                            @error('insurance_provider')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden submit (main CTA is in sidebar) --}}
                        <button type="submit" id="submit-btn-hidden" class="d-none"></button>

                    </form>
                </div>

                {{-- ── Sticky Summary Sidebar ── --}}
                <div class="col-lg-4">
                    <div class="pp-detail-section" style="position:sticky;top:100px;">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-bed" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('intensive_care.admission_request') }}</span>
                        </div>
                        <div class="pp-detail-section-body">

                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('intensive_care.hospital_singular') }}</span>
                                <span class="pp-summary-value" id="summary-hospital">—</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('intensive_care.lbl_case_type') }}</span>
                                <span class="pp-summary-value" id="summary-case-type">—</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('intensive_care.lbl_urgency') }}</span>
                                <span class="pp-summary-value" id="summary-urgency">—</span>
                            </div>

                            <button type="submit"
                                    form="icu-admission-form"
                                    class="pp-btn pp-btn-teal w-100 mt-4"
                                    style="justify-content:center;font-size:1rem;padding:0.75rem"
                                    id="submit-btn">
                                <i class="ph ph-paper-plane-right" aria-hidden="true"></i>
                                {{ __('intensive_care.lbl_submit') }}
                            </button>

                            <a href="{{ route('icu-hospitals') }}" class="pp-btn pp-btn-outline w-100 mt-2" style="justify-content:center">
                                <i class="ph ph-arrow-left" aria-hidden="true"></i>
                                {{ __('intensive_care.hospital_catalog') }}
                            </a>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script type="text/javascript">
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ── Select2 init ── */
        $('#hospital-select').select2({ placeholder: '{{ __('intensive_care.lbl_hospital_name') }}', allowClear: true, width: '100%' });
        $('#department-select').select2({ placeholder: '{{ __('intensive_care.lbl_any_available') }}', allowClear: true, width: '100%' });
        $('#case_type').select2({ width: '100%' });
        $('#urgency').select2({ width: '100%' });

        /* ── Hospital → Department loader ── */
        var hospitalDepartments = @json($hospitals->mapWithKeys(function($h) {
            return [$h->id => $h->icuDepartments->where('status', 1)->map(function($d) {
                return ['id' => $d->id, 'name' => $d->name . ' (' . $d->specialty_type . ')'];
            })->values()];
        }));

        $('#hospital-select').on('change', function () {
            var hospitalId  = $(this).val();
            var deptSelect  = $('#department-select');
            var loadingEl   = document.getElementById('department-loading');
            var emptyEl     = document.getElementById('department-empty');

            deptSelect.empty().append('<option value="">{{ __('intensive_care.lbl_any_available') }}</option>');
            loadingEl.style.display = 'none';
            emptyEl.style.display   = 'none';

            if (hospitalId) {
                loadingEl.style.display = '';
                var departments = hospitalDepartments[hospitalId] || [];
                setTimeout(function () {
                    loadingEl.style.display = 'none';
                    if (departments.length > 0) {
                        departments.forEach(function (dept) {
                            deptSelect.append(new Option(dept.name, dept.id, false, false));
                        });
                    } else {
                        emptyEl.style.display = '';
                    }
                    deptSelect.trigger('change');
                }, 150);
            }
            updateSummary();
        });

        /* Pre-load departments if hospital is already selected (validation repopulation) */
        @if($selectedHospitalId)
            $('#hospital-select').trigger('change');
        @endif

        /* ── Insurance provider toggle ── */
        document.getElementById('payment-method-select').addEventListener('change', function () {
            var insuranceGroup = document.getElementById('insurance-provider-group');
            insuranceGroup.style.display = this.value === 'insurance' ? '' : 'none';
        });

        /* ── Summary update ── */
        function updateSummary() {
            var hospitalText  = $('#hospital-select option:selected').text().trim();
            var caseText      = $('[name="case_type"] option:selected').text().trim();
            var urgencyText   = $('[name="urgency"] option:selected').text().trim();
            document.getElementById('summary-hospital').textContent  = hospitalText  || '—';
            document.getElementById('summary-case-type').textContent = caseText      || '—';
            document.getElementById('summary-urgency').textContent   = urgencyText   || '—';
        }

        $('[name="case_type"], [name="urgency"]').on('change', updateSummary);
        $('#hospital-select').on('change.summary', updateSummary);
        updateSummary();

        /* ── Double-submit protection ── */
        document.getElementById('icu-admission-form').addEventListener('submit', function () {
            var btn = document.getElementById('submit-btn');
            if (btn) {
                btn.disabled  = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("intensive_care.lbl_submit") }}...';
            }
        });

    });
}());
</script>
@endpush
