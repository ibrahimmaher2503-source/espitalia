@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
    {{ html()->form('POST', route('backend.test-orders.store'))->open() }}
    @if($data->id)
        <input type="hidden" name="id" value="{{ $data->id }}">
    @endif

    <div class="row">
        {{-- Left Column: Order Summary Sidebar --}}
        <div class="col-lg-4">
            {{-- Order Summary Card --}}
            <div class="card rl-form-card mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="ph ph-clipboard-text me-1"></i> {{ __('radiology_lab.lbl_order_summary') }}</h6>
                        <a href="{{ route('backend.test-orders.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="rl-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-user text-primary"></i>
                            <span class="text-muted small">{{ __('radiology_lab.lbl_patient_name') }}</span>
                        </div>
                        <p class="mb-0 fw-medium" id="summary-patient">{{ $data->patient ? $data->patient->first_name . ' ' . $data->patient->last_name : '---' }}</p>
                    </div>
                    <div class="rl-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-stethoscope text-info"></i>
                            <span class="text-muted small">{{ __('radiology_lab.lbl_doctor') }}</span>
                        </div>
                        <p class="mb-0 fw-medium" id="summary-doctor">{{ $data->doctor ? $data->doctor->first_name . ' ' . $data->doctor->last_name : '---' }}</p>
                    </div>
                    <hr>
                    <div class="rl-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-flask text-success"></i>
                            <span class="text-muted small">{{ __('radiology_lab.lbl_tests') }}</span>
                        </div>
                        <p class="mb-0 fw-bold fs-5" id="summary-test-count">{{ $data->items ? $data->items->count() : 0 }}</p>
                    </div>
                    <div class="rl-summary-item mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-currency-dollar text-warning"></i>
                            <span class="text-muted small">{{ __('radiology_lab.lbl_total_amount') }}</span>
                        </div>
                        <p class="mb-0 fw-bold fs-5" style="color:#6366f1;" id="summary-total">{{ $data->total_amount ? number_format($data->total_amount, 2) : '—' }}</p>
                    </div>
                    <div class="rl-summary-item mb-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="ph ph-receipt text-danger"></i>
                            <span class="text-muted small">{{ __('radiology_lab.lbl_final_amount') }}</span>
                        </div>
                        <p class="mb-0 fw-bold fs-5" style="color:#10b981;" id="summary-final">{{ $data->final_amount ? number_format($data->final_amount, 2) : '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Submission Card --}}
            <div class="card rl-form-card mb-4">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="ph ph-floppy-disk me-1"></i> {{ __('messages.save') }}
                    </button>
                    <a href="{{ route('backend.test-orders.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                        {{ __('messages.close') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column: Form Sections --}}
        <div class="col-lg-8">
            {{-- Section 1: Patient & Doctor --}}
            <div class="card rl-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">
                        <span class="rl-step-badge">1</span>
                        {{ __('radiology_lab.lbl_patient_doctor') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- PF-01: AJAX-driven Select2 — only the currently-selected option is rendered --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="patient_id">{{ __('radiology_lab.lbl_patient_name') }} <span class="text-danger">*</span></label>
                            <select class="form-control patient-select @error('patient_id') is-invalid @enderror" name="patient_id" id="patient_id" required style="width:100%">
                                @if($data->patient)
                                    <option value="{{ $data->patient->id }}" selected>{{ $data->patient->first_name }} {{ $data->patient->last_name }} ({{ $data->patient->email }})</option>
                                @endif
                            </select>
                            @error('patient_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="doctor_id">{{ __('radiology_lab.lbl_doctor') }}</label>
                            <select class="form-control doctor-select" name="doctor_id" id="doctor_id" style="width:100%">
                                @if($data->doctor)
                                    <option value="{{ $data->doctor->id }}" selected>{{ $data->doctor->first_name }} {{ $data->doctor->last_name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 2: Test Items --}}
            <div class="card rl-form-card mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <span class="rl-step-badge">2</span>
                            {{ __('radiology_lab.lbl_test_items') }}
                        </h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-test-btn">
                            <i class="ph ph-plus-circle me-1"></i> {{ __('radiology_lab.lbl_add_test') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @error('items') <div class="alert alert-danger mb-3">{{ $message }}</div> @enderror

                    <div id="test-items-container">
                        @if($data->id && $data->items && $data->items->count() > 0)
                            @foreach($data->items as $index => $item)
                                <div class="test-item-row mb-3 p-3" style="background:#f9fafb; border-radius:10px; border:1px solid #e5e7eb;">
                                    <div class="row align-items-end">
                                        <div class="col-md-5 mb-2 mb-md-0">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_lab_test') }} <span class="text-danger">*</span></label>
                                            <select class="form-control test-select" name="items[{{ $index }}][lab_test_id]" required style="width:100%" data-selected-id="{{ $item->lab_test_id }}">
                                                @if($item->labTest)
                                                    <option value="{{ $item->labTest->id }}" data-price="{{ $item->labTest->default_price }}" selected>
                                                        {{ $item->labTest->name }} ({{ $item->labTest->code }})
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2 mb-md-0">
                                            <label class="form-label small">{{ __('radiology_lab.lbl_price') }}</label>
                                            <div class="input-group">
                                                <span class="input-group-text">{{ Currency::defaultSymbol() }}</span>
                                                <input type="number" step="0.01" min="0" class="form-control item-price" name="items[{{ $index }}][price]" value="{{ old('items.' . $index . '.price', $item->price) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0 d-flex align-items-end gap-2">
                                            <div class="flex-grow-1">
                                                <span class="text-muted small test-info"></span>
                                            </div>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-test-btn" title="{{ __('messages.delete') }}">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    {{-- Empty state --}}
                    <div id="no-tests-message" class="text-center py-4" style="{{ ($data->id && $data->items && $data->items->count() > 0) ? 'display:none;' : '' }}">
                        <div class="rl-empty-icon mb-2">
                            <i class="ph ph-flask"></i>
                        </div>
                        <p class="text-muted mb-0">{{ __('radiology_lab.lbl_no_tests_added') }}</p>
                        <p class="text-muted small">{{ __('radiology_lab.lbl_click_add_test') }}</p>
                    </div>

                    {{-- Totals --}}
                    <div class="border-top pt-3 mt-3">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">{{ __('radiology_lab.lbl_subtotal') }}:</span>
                                    <span class="fw-semibold" id="subtotal-display">0.00</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ __('radiology_lab.lbl_coupon_code') }}:</span>
                                    <div class="input-group" style="width: 200px;">
                                        <input type="text" class="form-control form-control-sm" name="offer_code" id="offer_code" placeholder="{{ __('radiology_lab.lbl_enter_coupon') }}" value="{{ old('offer_code', $data->offer_code ?? '') }}">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="apply-coupon-btn">{{ __('radiology_lab.lbl_apply') }}</button>
                                    </div>
                                </div>
                                <div id="coupon-result" class="mb-2" style="display:none;"></div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">{{ __('radiology_lab.lbl_discount') }}:</span>
                                    <div class="input-group" style="width: 140px;">
                                        <span class="input-group-text">{{ Currency::defaultSymbol() }}</span>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $data->discount_amount ?? 0) }}">
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">{{ __('radiology_lab.lbl_final_amount') }}:</span>
                                    <span class="fw-bold fs-5" style="color:#10b981;" id="final-total-display">0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 3: Order Details --}}
            <div class="card rl-form-card mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="mb-0">
                        <span class="rl-step-badge">3</span>
                        {{ __('radiology_lab.lbl_order_details') }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="priority">{{ __('radiology_lab.lbl_priority') }}</label>
                            <select class="form-control" name="priority" id="priority">
                                <option value="routine" {{ old('priority', $data->priority ?? 'routine') == 'routine' ? 'selected' : '' }}>{{ __('radiology_lab.priority_routine') }}</option>
                                <option value="urgent" {{ old('priority', $data->priority ?? '') == 'urgent' ? 'selected' : '' }}>{{ __('radiology_lab.priority_urgent') }}</option>
                                <option value="stat" {{ old('priority', $data->priority ?? '') == 'stat' ? 'selected' : '' }}>{{ __('radiology_lab.priority_stat') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="clinical_notes">{{ __('radiology_lab.lbl_clinical_notes') }}</label>
                            <textarea class="form-control" name="clinical_notes" id="clinical_notes" rows="3" placeholder="{{ __('radiology_lab.lbl_clinical_notes_placeholder') }}">{{ old('clinical_notes', $data->clinical_notes ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="admin_notes">{{ __('radiology_lab.lbl_admin_notes') }}</label>
                            <textarea class="form-control" name="admin_notes" id="admin_notes" rows="3" placeholder="{{ __('radiology_lab.lbl_admin_notes_placeholder') }}">{{ old('admin_notes', $data->admin_notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{ html()->form()->close() }}
@endsection

@push('after-styles')
<style>
    .rl-form-card {
        border-radius: 12px;
        border: 1px solid rgba(0,0,0,0.08);
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .rl-form-card .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.06);
        padding: 1rem 1.25rem;
    }
    .rl-form-card .card-header h6 {
        color: #374151;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .rl-step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #818cf8);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .rl-summary-item p {
        color: #1f2937;
    }
    .rl-empty-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: rgba(99,102,241,0.08);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #6366f1;
    }
    .input-group-text {
        background: transparent;
        border-right: none;
    }
    .input-group-text + .form-control {
        border-left: none;
    }
    .test-item-row {
        transition: all 0.2s ease;
    }
    .test-item-row:hover {
        border-color: #6366f1 !important;
    }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        // PF-01: Select2 AJAX for patient / doctor
        function initUserSelect($el, role, placeholder) {
            $el.select2({
                placeholder: placeholder,
                allowClear: role === 'doctor',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("backend.test-orders.users_search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term, page: params.page || 1, role: role };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return { results: data.results, pagination: data.pagination };
                    },
                    cache: true
                }
            });
        }
        initUserSelect($('#patient_id'), 'user', '{{ __("messages.select_name", ["select" => __("radiology_lab.lbl_patient_name")]) }}');
        initUserSelect($('#doctor_id'), 'doctor', '{{ __("radiology_lab.lbl_select_doctor") }}');

        var testIndex = {{ ($data->id && $data->items) ? $data->items->count() : 0 }};

        // PF-02: Select2 AJAX for lab tests (replaces @json dump)
        function initTestSelect($el) {
            $el.select2({
                placeholder: '{{ __("radiology_lab.lbl_select_test") }}',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route("backend.test-orders.lab_tests_search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term, page: params.page || 1 };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return { results: data.results, pagination: data.pagination };
                    },
                    cache: true
                }
            });
            // On selection, attach price as data attribute on the selected option
            $el.on('select2:select', function(e) {
                var priceData = e.params.data.price;
                if (priceData != null) {
                    $el.find(':selected').attr('data-price', priceData);
                }
                $el.closest('.test-item-row').find('.item-price').val(parseFloat(priceData || 0).toFixed(2));
                recalculateTotals();
            });
        }

        // Init existing test rows
        $('.test-select').each(function() { initTestSelect($(this)); });

        // Add test item row
        $('#add-test-btn').on('click', function() {
            var html = '<div class="test-item-row mb-3 p-3" style="background:#f9fafb; border-radius:10px; border:1px solid #e5e7eb;">' +
                '<div class="row align-items-end">' +
                    '<div class="col-md-5 mb-2 mb-md-0">' +
                        '<label class="form-label small">{{ __("radiology_lab.lbl_lab_test") }} <span class="text-danger">*</span></label>' +
                        '<select class="form-control test-select" name="items[' + testIndex + '][lab_test_id]" required style="width:100%"></select>' +
                    '</div>' +
                    '<div class="col-md-3 mb-2 mb-md-0">' +
                        '<label class="form-label small">{{ __("radiology_lab.lbl_price") }}</label>' +
                        '<div class="input-group">' +
                            '<span class="input-group-text">{{ Currency::defaultSymbol() }}</span>' +
                            '<input type="number" step="0.01" min="0" class="form-control item-price" name="items[' + testIndex + '][price]" value="0">' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-4 mb-2 mb-md-0 d-flex align-items-end gap-2">' +
                        '<div class="flex-grow-1">' +
                            '<span class="text-muted small test-info"></span>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm remove-test-btn" title="{{ __("messages.delete") }}">' +
                            '<i class="ph ph-trash"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>' +
            '</div>';

            var $row = $(html);
            $('#test-items-container').append($row);
            initTestSelect($row.find('.test-select'));
            $('#no-tests-message').hide();
            testIndex++;
            recalculateTotals();
        });

        // Remove test item row
        $(document).on('click', '.remove-test-btn', function() {
            $(this).closest('.test-item-row').remove();
            if ($('.test-item-row').length === 0) {
                $('#no-tests-message').show();
            }
            recalculateTotals();
        });

        // Price auto-fill is handled inside initTestSelect's select2:select handler

        // Recalculate on price change
        $(document).on('input', '.item-price', function() {
            recalculateTotals();
        });

        // Recalculate on discount change
        $('#discount_amount').on('input', function() {
            recalculateTotals();
        });

        function recalculateTotals() {
            var subtotal = 0;
            var testCount = 0;
            $('.item-price').each(function() {
                var val = parseFloat($(this).val()) || 0;
                subtotal += val;
                testCount++;
            });

            var discount = parseFloat($('#discount_amount').val()) || 0;
            var finalTotal = Math.max(0, subtotal - discount);

            $('#subtotal-display').text(subtotal.toFixed(2));
            $('#final-total-display').text(finalTotal.toFixed(2));
            $('#summary-total').text(subtotal.toFixed(2));
            $('#summary-final').text(finalTotal.toFixed(2));
            $('#summary-test-count').text(testCount);
        }

        // Live update sidebar summary
        $('#patient_id').on('change', function() {
            var text = $(this).find('option:selected').text().trim();
            $('#summary-patient').text(text || '---');
        });
        $('#doctor_id').on('change', function() {
            var text = $(this).find('option:selected').text().trim();
            $('#summary-doctor').text(text || '---');
        });

        // Coupon apply handler
        $('#apply-coupon-btn').on('click', function() {
            var code = $('#offer_code').val().trim();
            var patientId = $('#patient_id').val();
            var subtotal = 0;
            $('.item-price').each(function() {
                subtotal += parseFloat($(this).val()) || 0;
            });

            if (!code) {
                $('#coupon-result').html('<small class="text-danger">{{ __("radiology_lab.lbl_enter_coupon") }}</small>').show();
                return;
            }

            var btn = $(this);
            btn.prop('disabled', true).text('...');

            $.ajax({
                url: '{{ route("backend.test-orders.validate_coupon") }}',
                method: 'POST',
                data: { offer_code: code, patient_id: patientId, total_amount: subtotal, _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if (res.valid) {
                        $('#discount_amount').val(res.discount_amount.toFixed(2));
                        $('#coupon-result').html('<small class="text-success"><i class="ph ph-check-circle"></i> ' + res.message + '</small>').show();
                        recalculateTotals();
                    } else {
                        $('#coupon-result').html('<small class="text-danger"><i class="ph ph-x-circle"></i> ' + res.message + '</small>').show();
                    }
                },
                error: function() {
                    $('#coupon-result').html('<small class="text-danger">{{ __("messages.something_wrong") }}</small>').show();
                },
                complete: function() {
                    btn.prop('disabled', false).text('{{ __("radiology_lab.lbl_apply") }}');
                }
            });
        });

        // Initial calculation
        recalculateTotals();
    });
</script>
@endpush
