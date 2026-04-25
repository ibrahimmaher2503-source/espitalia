@extends('frontend::layouts.master')
@section('title', __('radiology_lab.order_a_test'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">

            {{-- ── Page header ── --}}
            <div class="pp-page-header mb-4">
                <h1 class="pp-page-title">{{ __('radiology_lab.order_a_test') }}</h1>
                <p style="color:var(--pp-muted);font-size:0.82rem;margin:0;font-family:var(--pp-font)">
                    {{ __('radiology_lab.lbl_add_test_item') }}
                </p>
            </div>

            <div class="row g-4">

                {{-- ── Form column (col-lg-8) ── --}}
                <div class="col-lg-8">

                    {{-- Validation errors --}}
                    @if($errors->any())
                        <div class="pp-cancel-alert mb-3" role="alert">
                            <p class="pp-cancel-alert-title">
                                <i class="ph ph-warning-circle" aria-hidden="true"></i>
                                {{ __('messages.validation_errors') ?? 'Please fix the following errors' }}
                            </p>
                            <ul class="mb-0 ps-4" style="font-size:0.82rem;color:var(--pp-text-2)">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('order-tests.store') }}" id="order-test-form">
                        @csrf

                        {{-- ── Select Tests section ── --}}
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-list-checks" aria-hidden="true"></i>
                                <span>{{ __('radiology_lab.lbl_select_test') }}</span>
                            </div>
                            <div class="pp-detail-section-body">

                                <div id="test-items-container">
                                    @foreach($tests as $test)
                                        @php
                                            $isChecked = (int) $selectedTestId === $test->id
                                                || (is_array(old('items')) && isset(old('items')[$test->id]));
                                        @endphp
                                        <div class="pp-test-item {{ $isChecked ? 'pp-test-selected' : '' }}"
                                             data-price="{{ $test->default_price }}"
                                             data-id="{{ $test->id }}"
                                             role="checkbox"
                                             aria-checked="{{ $isChecked ? 'true' : 'false' }}"
                                             tabindex="0">
                                            {{-- Hidden checkbox for form submission --}}
                                            <input type="checkbox"
                                                   class="test-checkbox visually-hidden"
                                                   name="items[{{ $test->id }}][lab_test_id]"
                                                   value="{{ $test->id }}"
                                                   id="test-{{ $test->id }}"
                                                   {{ $isChecked ? 'checked' : '' }}
                                                   tabindex="-1"
                                                   aria-hidden="true">
                                            {{-- Custom checkbox visual --}}
                                            <span class="pp-test-check" aria-hidden="true">
                                                <i class="ph-fill ph-check"></i>
                                            </span>
                                            {{-- Content --}}
                                            <div class="pp-test-item-content">
                                                <p class="pp-test-item-name">
                                                    {{ $test->name }}
                                                    <span class="pp-badge">{{ $test->code }}</span>
                                                    {!! $test->department_badge !!}
                                                </p>
                                                <div class="pp-test-item-meta">
                                                    <span><i class="ph ph-test-tube" aria-hidden="true"></i>{{ $test->sample_type_label }}</span>
                                                    @if($test->turnaround_time)
                                                        <span><i class="ph ph-clock" aria-hidden="true"></i>{{ $test->turnaround_time }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            {{-- Price --}}
                                            <span class="pp-test-item-price">{{ number_format($test->default_price, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                @error('items')
                                    <div class="pp-field-error" style="margin-top:0.5rem">{{ $message }}</div>
                                @enderror

                            </div>
                        </div>

                        {{-- ── Clinical Notes section ── --}}
                        <div class="pp-detail-section mb-3">
                            <div class="pp-detail-section-header">
                                <i class="ph ph-note" aria-hidden="true"></i>
                                <span>{{ __('radiology_lab.lbl_clinical_notes') }}</span>
                            </div>
                            <div class="pp-detail-section-body">
                                <div class="pp-form-group">
                                    <label class="pp-label" for="clinical_notes">
                                        {{ __('radiology_lab.lbl_clinical_notes') }}
                                        <span style="color:var(--pp-muted);font-weight:400;margin-left:4px">({{ __('messages.optional') ?? 'optional' }})</span>
                                    </label>
                                    <textarea class="pp-input @error('clinical_notes') is-invalid @enderror"
                                              id="clinical_notes"
                                              name="clinical_notes"
                                              rows="4"
                                              maxlength="2000"
                                              style="height:auto"
                                              placeholder="{{ __('radiology_lab.lbl_clinical_notes_placeholder') ?? __('radiology_lab.lbl_clinical_notes') . '…' }}">{{ old('clinical_notes') }}</textarea>
                                    @error('clinical_notes')
                                        <div class="pp-field-error">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Hidden submit (main CTA is in sidebar) --}}
                        <button type="submit" id="submit-order-btn-hidden" class="d-none" aria-hidden="true"></button>
                    </form>
                </div>

                {{-- ── Sticky summary sidebar (col-lg-4) ── --}}
                <div class="col-lg-4">
                    <div class="pp-detail-section" style="position:sticky;top:5rem">

                        <div class="pp-detail-section-header">
                            <i class="ph ph-receipt" aria-hidden="true"></i>
                            <span>{{ __('radiology_lab.lbl_order_summary') }}</span>
                        </div>

                        <div class="pp-detail-section-body">
                            <div id="selected-tests-summary" class="mb-3">
                                <p style="color:var(--pp-muted);font-size:0.78rem;font-family:var(--pp-font);margin:0">
                                    {{ __('radiology_lab.lbl_no_tests_selected') }}
                                </p>
                            </div>

                            <div style="border-top:1px solid var(--pp-border);padding-top:0.75rem;margin-top:0.25rem;margin-bottom:0.5rem">
                                <label style="font-size:0.78rem;font-weight:600;display:block;margin-bottom:0.3rem">{{ __('radiology_lab.lbl_coupon_code') }}</label>
                                <div style="display:flex;gap:0.4rem">
                                    <input type="text" form="order-test-form" name="offer_code" id="offer_code" class="pp-input" placeholder="{{ __('radiology_lab.lbl_enter_coupon') }}" style="flex:1;font-size:0.82rem;padding:0.4rem 0.6rem">
                                    <button type="button" id="apply-coupon-btn" class="pp-btn pp-btn-outline" style="font-size:0.78rem;padding:0.4rem 0.75rem;white-space:nowrap">{{ __('radiology_lab.lbl_apply') }}</button>
                                </div>
                                <div id="coupon-result" style="display:none;font-size:0.75rem;margin-top:0.3rem"></div>
                            </div>

                            {{-- UI-17: subtotal (pre-discount) shown separately from the final/total --}}
                            <div class="pp-summary-row" id="subtotal-row" style="margin-bottom:0.25rem">
                                <span class="pp-summary-label" style="font-size:0.82rem">{{ __('radiology_lab.lbl_subtotal') }}</span>
                                <span class="pp-summary-total" id="subtotal-amount">0.00</span>
                            </div>

                            <div class="pp-summary-row" id="discount-row" style="display:none;margin-bottom:0.25rem">
                                <span class="pp-summary-label" style="font-size:0.82rem;color:var(--pp-success,#10b981)">{{ __('radiology_lab.lbl_discount') }}</span>
                                <span class="pp-summary-total" id="discount-amount" style="color:var(--pp-success,#10b981)">-0.00</span>
                            </div>

                            <div class="pp-summary-row" style="border-top:1px solid var(--pp-border);padding-top:0.75rem;margin-top:0.25rem">
                                <span class="pp-summary-label" style="font-weight:600;font-size:0.82rem">{{ __('radiology_lab.lbl_final_amount') }}</span>
                                <span class="pp-summary-total" id="total-amount">0.00</span>
                            </div>

                            <button type="submit"
                                    form="order-test-form"
                                    class="pp-btn pp-btn-teal w-100 mt-3"
                                    id="submit-order-btn"
                                    disabled>
                                <i class="ph ph-credit-card" aria-hidden="true"></i>
                                {{ __('radiology_lab.proceed_to_payment') }}
                            </button>

                            <a href="{{ route('lab-tests') }}" class="pp-btn pp-btn-outline w-100 mt-2">
                                <i class="ph ph-arrow-left" aria-hidden="true"></i>
                                {{ __('radiology_lab.lbl_back_to_catalog') }}
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
(function () {
    'use strict';

    function esc(text) {
        if (!text && text !== 0) return '';
        var d = document.createElement('div');
        d.textContent = String(text);
        return d.innerHTML;
    }

    var submitBtn   = document.getElementById('submit-order-btn');
    var summaryDiv  = document.getElementById('selected-tests-summary');
    var totalEl     = document.getElementById('total-amount');
    var noTestsMsg  = '{{ __('radiology_lab.lbl_no_tests_selected') }}';

    function updateOrderSummary() {
        var total    = 0;
        var items    = [];
        var allItems = document.querySelectorAll('.pp-test-item');

        allItems.forEach(function (item) {
            if (item.classList.contains('pp-test-selected')) {
                var price = parseFloat(item.dataset.price) || 0;
                var nameEl = item.querySelector('.pp-test-item-name');
                var name   = nameEl ? nameEl.firstChild.textContent.trim() : '';
                total += price;
                items.push({ name: name, price: price });
            }
        });

        if (items.length === 0) {
            summaryDiv.innerHTML =
                '<p style="color:var(--pp-muted);font-size:0.78rem;font-family:var(--pp-font);margin:0">' +
                esc(noTestsMsg) + '</p>';
            submitBtn.disabled = true;
        } else {
            var html = '';
            items.forEach(function (item) {
                html += '<div class="pp-summary-item">' +
                    '<span class="pp-summary-item-name">' + esc(item.name) + '</span>' +
                    '<span class="pp-summary-item-price">' + item.price.toFixed(2) + '</span>' +
                '</div>';
            });
            summaryDiv.innerHTML = html;
            submitBtn.disabled = false;
        }

        totalEl.textContent = total.toFixed(2);
        var subtotalEl = document.getElementById('subtotal-amount');
        if (subtotalEl) subtotalEl.textContent = total.toFixed(2);
    }

    /* ── Toggle item selection ─────────────────────────────────── */
    function toggleItem(item) {
        var cb       = item.querySelector('.test-checkbox');
        var selected = item.classList.toggle('pp-test-selected');
        if (cb) cb.checked = selected;
        item.setAttribute('aria-checked', selected ? 'true' : 'false');
        updateOrderSummary();
    }

    document.addEventListener('DOMContentLoaded', function () {
        var allItems = document.querySelectorAll('.pp-test-item');

        allItems.forEach(function (item) {
            item.addEventListener('click', function () { toggleItem(item); });
            item.addEventListener('keydown', function (e) {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    toggleItem(item);
                }
            });
        });

        /* Double-submit protection */
        document.getElementById('order-test-form').addEventListener('submit', function () {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
                    '{{ __('radiology_lab.lbl_submitting') ?? 'Submitting…' }}';
            }
        });

        updateOrderSummary();

        /* UI-16: scroll the pre-selected test into view so deep-linking reveals the selection */
        var firstSelected = document.querySelector('.pp-test-selected');
        if (firstSelected) {
            firstSelected.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }

        /* Coupon apply handler */
        var couponBtn = document.getElementById('apply-coupon-btn');
        var couponInput = document.getElementById('offer_code');
        var couponResult = document.getElementById('coupon-result');
        var discountRow = document.getElementById('discount-row');
        var discountAmountEl = document.getElementById('discount-amount');
        var currentDiscount = 0;

        if (couponBtn) {
            couponBtn.addEventListener('click', function () {
                var code = couponInput.value.trim();
                if (!code) {
                    couponResult.innerHTML = '<span style="color:var(--pp-danger,#ef4444)">{{ __("radiology_lab.lbl_enter_coupon") }}</span>';
                    couponResult.style.display = 'block';
                    return;
                }

                var totalEl = document.getElementById('total-amount');
                var rawTotal = parseFloat(totalEl.textContent) + currentDiscount;

                couponBtn.disabled = true;
                couponBtn.textContent = '...';

                fetch('{{ route("order-tests.validate-coupon") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ offer_code: code, total_amount: rawTotal })
                })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.valid) {
                        currentDiscount = res.discount_amount;
                        discountAmountEl.textContent = '-' + res.discount_amount.toFixed(2);
                        discountRow.style.display = 'flex';
                        totalEl.textContent = Math.max(0, rawTotal - res.discount_amount).toFixed(2);
                        couponResult.innerHTML = '<span style="color:var(--pp-success,#10b981)"><i class="ph ph-check-circle"></i> ' + res.message + '</span>';
                    } else {
                        couponResult.innerHTML = '<span style="color:var(--pp-danger,#ef4444)"><i class="ph ph-x-circle"></i> ' + res.message + '</span>';
                    }
                    couponResult.style.display = 'block';
                })
                .catch(function () {
                    couponResult.innerHTML = '<span style="color:var(--pp-danger,#ef4444)">{{ __("messages.something_wrong") }}</span>';
                    couponResult.style.display = 'block';
                })
                .finally(function () {
                    couponBtn.disabled = false;
                    couponBtn.textContent = '{{ __("radiology_lab.lbl_apply") }}';
                });
            });
        }
    });
}());
</script>
@endpush
