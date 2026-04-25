@extends('frontend::layouts.master')
@section('title', __('request_nurse.request_a_nurse'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- Page header --}}
            <div class="pp-page-header">
                <h1 class="pp-page-title">{{ __('request_nurse.request_a_nurse') }}</h1>
            </div>

            <div class="row gy-4">

                {{-- ── Form Column ── --}}
                <div class="col-lg-8">
                    <form method="POST" action="{{ route('request-nurse.store') }}" id="nurse-request-form">
                        @csrf

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

                        {{-- ── Service Details ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-clipboard-text" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('request_nurse.lbl_service_details') }}</span>
                            </div>
                            <div class="pp-detail-section-body">

                                <div class="pp-form-group">
                                    <label class="pp-label" for="service_description">
                                        {{ __('request_nurse.lbl_service_description') }}
                                        <span class="pp-required">*</span>
                                    </label>
                                    <textarea class="pp-input @error('service_description') is-invalid @enderror"
                                              id="service_description"
                                              name="service_description"
                                              rows="3"
                                              style="height:auto"
                                              placeholder="{{ __('request_nurse.lbl_service_description_placeholder') }}"
                                              required>{{ old('service_description') }}</textarea>
                                    @error('service_description')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="pp-form-group">
                                    <label class="pp-label" for="nurse_id">
                                        {{ __('request_nurse.lbl_nurse_preference') }}
                                    </label>
                                    <select class="pp-select select2" name="nurse_id" id="nurse_id">
                                        <option value="">{{ __('request_nurse.lbl_any_available_nurse') }}</option>
                                        @foreach($nurses as $nurse)
                                            <option value="{{ $nurse->nurse_id }}"
                                                    data-rate="{{ $nurse->hourly_rate ?? 0 }}"
                                                    data-name="{{ trim(($nurse->user->first_name ?? '') . ' ' . ($nurse->user->last_name ?? '')) }}"
                                                    {{ ($selectedNurseId == $nurse->nurse_id || old('nurse_id') == $nurse->nurse_id) ? 'selected' : '' }}>
                                                {{ trim(($nurse->user->first_name ?? '') . ' ' . ($nurse->user->last_name ?? '')) }}
                                                @if($nurse->specialization) &mdash; {{ $nurse->specialization }} @endif
                                                ({{ number_format($nurse->hourly_rate ?? 0, 2) }}{{ __('request_nurse.lbl_per_hour') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="row gx-3">
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="preferred_date">
                                                {{ __('request_nurse.lbl_preferred_date') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="date"
                                                   class="pp-input @error('preferred_date') is-invalid @enderror"
                                                   id="preferred_date"
                                                   name="preferred_date"
                                                   value="{{ old('preferred_date') }}"
                                                   min="{{ date('Y-m-d') }}"
                                                   required>
                                            @error('preferred_date')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="preferred_time">
                                                {{ __('request_nurse.lbl_preferred_time') }}
                                            </label>
                                            <input type="time"
                                                   class="pp-input @error('preferred_time') is-invalid @enderror"
                                                   id="preferred_time"
                                                   name="preferred_time"
                                                   value="{{ old('preferred_time') }}">
                                            @error('preferred_time')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="duration_hours">
                                                {{ __('request_nurse.lbl_duration_hours') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="number" step="0.5" min="1" max="24"
                                                   class="pp-input @error('duration_hours') is-invalid @enderror"
                                                   id="duration_hours"
                                                   name="duration_hours"
                                                   value="{{ old('duration_hours', 1) }}"
                                                   required>
                                            @error('duration_hours')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- ── Location Details ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-map-pin" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('request_nurse.lbl_location_details') }}</span>
                            </div>
                            <div class="pp-detail-section-body">

                                <div class="row gx-3">
                                    <div class="col-md-6">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="address_line_1">
                                                {{ __('request_nurse.lbl_address_line_1') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="text"
                                                   class="pp-input @error('address_line_1') is-invalid @enderror"
                                                   id="address_line_1"
                                                   name="address_line_1"
                                                   value="{{ old('address_line_1') }}"
                                                   required>
                                            @error('address_line_1')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="address_line_2">
                                                {{ __('request_nurse.lbl_address_line_2') }}
                                            </label>
                                            <input type="text"
                                                   class="pp-input @error('address_line_2') is-invalid @enderror"
                                                   id="address_line_2"
                                                   name="address_line_2"
                                                   value="{{ old('address_line_2') }}">
                                            @error('address_line_2')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row gx-3">
                                    <div class="col-md-3">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="city">
                                                {{ __('request_nurse.lbl_city') }}
                                                <span class="pp-required">*</span>
                                            </label>
                                            <input type="text"
                                                   class="pp-input @error('city') is-invalid @enderror"
                                                   id="city"
                                                   name="city"
                                                   value="{{ old('city') }}"
                                                   required>
                                            @error('city')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="state">
                                                {{ __('request_nurse.lbl_state') }}
                                            </label>
                                            <input type="text"
                                                   class="pp-input @error('state') is-invalid @enderror"
                                                   id="state"
                                                   name="state"
                                                   value="{{ old('state') }}">
                                            @error('state')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="country">
                                                {{ __('request_nurse.lbl_country') }}
                                            </label>
                                            <input type="text"
                                                   class="pp-input @error('country') is-invalid @enderror"
                                                   id="country"
                                                   name="country"
                                                   value="{{ old('country') }}">
                                            @error('country')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="pp-form-group">
                                            <label class="pp-label" for="postal_code">
                                                {{ __('request_nurse.lbl_postal_code') }}
                                            </label>
                                            <input type="text"
                                                   class="pp-input @error('postal_code') is-invalid @enderror"
                                                   id="postal_code"
                                                   name="postal_code"
                                                   value="{{ old('postal_code') }}">
                                            @error('postal_code')
                                                <div class="pp-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="pp-form-group mb-0">
                                    <label class="pp-label" for="contact_number">
                                        {{ __('request_nurse.lbl_contact_number') }}
                                        <span class="pp-required">*</span>
                                    </label>
                                    <input type="text"
                                           class="pp-input @error('contact_number') is-invalid @enderror"
                                           id="contact_number"
                                           name="contact_number"
                                           value="{{ old('contact_number') }}"
                                           required>
                                    @error('contact_number')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>

                        {{-- ── Additional Notes ── --}}
                        <div class="pp-detail-section mb-4">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-note" aria-hidden="true"></i>
                                <span class="pp-section-title m-0">{{ __('request_nurse.lbl_additional_notes') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="pp-form-group mb-0">
                                    <label class="pp-label" for="patient_notes">
                                        {{ __('request_nurse.lbl_patient_notes') }}
                                    </label>
                                    <textarea class="pp-input @error('patient_notes') is-invalid @enderror"
                                              id="patient_notes"
                                              name="patient_notes"
                                              rows="3"
                                              style="height:auto"
                                              placeholder="{{ __('request_nurse.lbl_patient_notes_placeholder') }}">{{ old('patient_notes') }}</textarea>
                                    @error('patient_notes')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="pp-btn pp-btn-teal w-100" style="justify-content:center;font-size:1rem;padding:0.75rem">
                            <i class="ph ph-paper-plane-right" aria-hidden="true"></i>
                            {{ __('request_nurse.lbl_submit_request') }}
                        </button>

                    </form>
                </div>

                {{-- ── Summary Sidebar ── --}}
                <div class="col-lg-4">
                    <div class="pp-detail-section" style="position:sticky;top:100px;">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-receipt" aria-hidden="true"></i>
                            <span class="pp-section-title m-0">{{ __('request_nurse.lbl_request_summary') }}</span>
                        </div>
                        <div class="pp-detail-section-body">

                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('request_nurse.lbl_nurse_preference') }}</span>
                                <span class="pp-summary-value" id="summary-nurse">{{ __('request_nurse.lbl_any_available_nurse') }}</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('request_nurse.lbl_preferred_date') }}</span>
                                <span class="pp-summary-value" id="summary-date">—</span>
                            </div>
                            <div class="pp-summary-row">
                                <span class="pp-summary-label">{{ __('request_nurse.lbl_duration_hours') }}</span>
                                <span class="pp-summary-value" id="summary-duration">—</span>
                            </div>

                            <div id="summary-cost-row" class="pp-summary-row d-none">
                                <span class="pp-summary-label pp-summary-total">{{ __('request_nurse.lbl_total_amount') }} (est.)</span>
                                <span class="pp-summary-value pp-summary-total" id="summary-cost">—</span>
                            </div>

                            <div id="summary-cost-placeholder" class="pp-info-block mt-3">
                                <i class="ph ph-info" aria-hidden="true"></i>
                                {{ __('request_nurse.lbl_estimate_help_text') }}
                            </div>

                            <p id="summary-disclaimer" class="d-none" style="font-size:0.72rem;color:var(--pp-muted);margin-top:0.375rem;font-family:var(--pp-font)">
                                * {{ __('request_nurse.lbl_final_amount_disclaimer') }}
                            </p>

                            <div class="mt-4">
                                <a href="{{ route('available-nurses') }}" class="pp-btn pp-btn-outline w-100" style="justify-content:center">
                                    <i class="ph ph-arrow-left" aria-hidden="true"></i>
                                    {{ __('messages.back') }}
                                </a>
                            </div>

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
        var nurseSelect              = $('#nurse_id');
        var durationInput            = document.getElementById('duration_hours');
        var dateInput                = document.getElementById('preferred_date');
        var summaryNurse             = document.getElementById('summary-nurse');
        var summaryDate              = document.getElementById('summary-date');
        var summaryDuration          = document.getElementById('summary-duration');
        var summaryCost              = document.getElementById('summary-cost');
        var summaryCostRow           = document.getElementById('summary-cost-row');
        var summaryCostPlaceholder   = document.getElementById('summary-cost-placeholder');
        var summaryDisclaimer        = document.getElementById('summary-disclaimer');

        nurseSelect.select2({
            placeholder: '{{ __('request_nurse.lbl_any_available_nurse') }}',
            allowClear:  true,
            width:       '100%'
        });

        function updateSummary() {
            var selectedOption = nurseSelect.find('option:selected');
            var nurseName      = selectedOption.data('name') || '{{ __('request_nurse.lbl_any_available_nurse') }}';
            var rate           = parseFloat(selectedOption.data('rate')) || 0;
            var duration       = parseFloat(durationInput.value) || 0;
            var date           = dateInput.value || '';

            summaryNurse.textContent    = nurseName;
            summaryDate.textContent     = date || '—';
            summaryDuration.textContent = duration > 0
                ? duration + ' {{ __("request_nurse.lbl_hours_suffix") }}'
                : '—';

            if (rate > 0 && duration > 0) {
                summaryCost.textContent = (rate * duration).toFixed(2);
                summaryCostRow.classList.remove('d-none');
                summaryCostPlaceholder.classList.add('d-none');
                summaryDisclaimer.classList.remove('d-none');
            } else {
                summaryCostRow.classList.add('d-none');
                summaryCostPlaceholder.classList.remove('d-none');
                summaryDisclaimer.classList.add('d-none');
            }
        }

        nurseSelect.on('change', updateSummary);
        durationInput.addEventListener('input', updateSummary);
        dateInput.addEventListener('change', updateSummary);

        updateSummary();

        /* ── Double-submit protection ── */
        document.getElementById('nurse-request-form').addEventListener('submit', function () {
            var btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled    = true;
                btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("messages.processing") }}...';
            }
        });
    });
}());
</script>
@endpush
