@extends('frontend::layouts.master')

@section('title', __('frontend.my_appointments'))

@push('after-styles')
<link rel="stylesheet" href="{{ asset('css/patient-portal.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@section('content')
@include('frontend::components.section.breadcrumb')

<div class="pp-page list-page section-spacing px-0">
    <div class="container">

        {{-- ── Page header + filters ── --}}
        <div class="pp-page-header">
            <h1 class="pp-page-title">{{ __('frontend.my_appointments') }}</h1>
        </div>

        {{-- ── Tab nav + Doctor filter ── --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">

            {{-- Tabs --}}
            <ul class="pp-tab-nav" id="appointments-tabs" role="tablist">
                <li role="presentation">
                    <a class="pp-tab-link active"
                       id="tab-all"
                       href="#tab-all-pane"
                       data-bs-toggle="pill"
                       data-bs-target="#tab-all-pane"
                       data-tab="all-appointments"
                       role="tab"
                       aria-controls="tab-all-pane"
                       aria-selected="true">
                        <i class="ph ph-squares-four"></i>
                        {{ __('frontend.all') }}
                    </a>
                </li>
                <li role="presentation">
                    <a class="pp-tab-link"
                       id="tab-upcoming"
                       href="#tab-upcoming-pane"
                       data-bs-toggle="pill"
                       data-bs-target="#tab-upcoming-pane"
                       data-tab="upcoming-appointments"
                       role="tab"
                       aria-controls="tab-upcoming-pane"
                       aria-selected="false">
                        <i class="ph ph-calendar-plus"></i>
                        {{ __('frontend.upcomming') }}
                    </a>
                </li>
                <li role="presentation">
                    <a class="pp-tab-link"
                       id="tab-completed"
                       href="#tab-completed-pane"
                       data-bs-toggle="pill"
                       data-bs-target="#tab-completed-pane"
                       data-tab="completed-appointments"
                       role="tab"
                       aria-controls="tab-completed-pane"
                       aria-selected="false">
                        <i class="ph ph-check-circle"></i>
                        {{ __('frontend.completed') }}
                    </a>
                </li>
            </ul>

            {{-- Doctor filter --}}
            <div class="pp-filter-bar">
                <label class="pp-filter-label" for="doctor">{{ __('frontend.filter_by') }}:</label>
                <select name="doctor" id="doctor" class="pp-filter-select select2" data-filter="select" aria-label="{{ __('clinic.lbl_select_doctor') }}">
                    <option value="">{{ __('clinic.lbl_select_doctor') }}</option>
                    @foreach ($doctors as $doctor)
                        <option value="{{ optional($doctor->user)->id }}">{{ getDisplayName($doctor->user) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- ── Tab content ── --}}
        <div class="tab-content">

            {{-- All appointments --}}
            <div class="tab-pane fade show active" id="tab-all-pane" role="tabpanel" aria-labelledby="tab-all">
                <ul class="list-inline m-0" id="all-appointments-list">
                    <div id="shimmer-loader" class="shimmer-loader">
                        @for ($i = 0; $i < 4; $i++)
                        <div class="pp-appt-card-skeleton mb-3">
                            <div class="pp-sk-header">
                                <div class="pp-sk pp-sk-h16 pp-sk-w40"></div>
                                <div class="pp-sk pp-sk-h16 pp-sk-w30"></div>
                            </div>
                            <div class="pp-sk pp-sk-h16 pp-sk-w60 mb-2"></div>
                            <div class="pp-sk-grid">
                                <div class="pp-sk pp-sk-h10 pp-sk-w100"></div>
                                <div class="pp-sk pp-sk-h10 pp-sk-w80"></div>
                                <div class="pp-sk pp-sk-h10 pp-sk-w100"></div>
                            </div>
                        </div>
                        @endfor
                    </div>
                    <table id="datatable" class="table table-responsive custom-card-table d-none"></table>
                </ul>
            </div>

            {{-- Upcoming appointments --}}
            <div class="tab-pane fade" id="tab-upcoming-pane" role="tabpanel" aria-labelledby="tab-upcoming">
                <ul class="list-inline m-0">
                    <table id="datatable-upcoming" class="table table-responsive custom-card-table"></table>
                </ul>
            </div>

            {{-- Completed appointments --}}
            <div class="tab-pane fade" id="tab-completed-pane" role="tabpanel" aria-labelledby="tab-completed">
                <ul class="list-inline m-0">
                    <table id="datatable-completed" class="table table-responsive custom-card-table"></table>
                </ul>
            </div>

        </div>{{-- /.tab-content --}}

    </div>{{-- /.container --}}
</div>{{-- /.pp-page --}}


{{-- ═══════════════════════════════════════════════════
     CANCEL APPOINTMENT MODAL
     ═══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal pp-modal-danger"
     id="cancel-appointment"
     tabindex="-1"
     aria-labelledby="cancelModalTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalTitle">
                    <i class="ph ph-x-circle me-2"></i>{{ __('messages.cancel_appointment') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" style="font-size:0.82rem">
                    {{ __('messages.do_you_want_to_cancel_this_appointment') }}
                </p>

                {{-- Cancellation fee --}}
                <div id="cancel_charge_info"
                     class="d-flex justify-content-between align-items-center px-3 py-2 rounded mb-3"
                     style="background:var(--pp-cancelled-bg);font-size:0.8rem">
                    {{-- JS fills this in --}}
                </div>

                {{-- Reason --}}
                <div class="pp-form-group mb-0">
                    <label class="pp-label" for="cancel_reason">
                        {{ __('messages.lbl_reason') }}
                        <span class="pp-required">*</span>
                    </label>
                    <input type="text"
                           class="pp-input"
                           id="cancel_reason"
                           placeholder="{{ __('messages.lbl_emergency') }}"
                           autocomplete="off">
                    <div id="cancel_reason_error" class="pp-field-error d-none">
                        {{ __('messages.please_enter_reason') }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="pp-btn pp-btn-outline" data-bs-dismiss="modal">
                    {{ __('messages.go_back') }}
                </button>
                <button id="confirm_btn" type="button" class="pp-btn pp-btn-danger" onclick="cancelAppointment()">
                    {{ __('messages.cancel_appointment') }}
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ═══════════════════════════════════════════════════
     CANCEL SUCCESS MODAL
     ═══════════════════════════════════════════════════ --}}
<div class="modal fade pp-modal"
     id="success-confirmation-modal"
     tabindex="-1"
     aria-labelledby="successModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">
                    <i class="ph ph-check-circle me-2" style="color:var(--pp-completed)"></i>{{ __('frontend.appointment_cancelled') ?? 'Appointment Cancelled' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.close') }}"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div style="width:60px;height:60px;background:var(--pp-cancelled-bg);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                    <i class="ph ph-x-circle" style="font-size:1.75rem;color:var(--pp-cancelled)"></i>
                </div>
                <h6 style="font-weight:700;color:var(--pp-navy);margin-bottom:0.5rem">
                    {{ __('frontend.appointment_cancelled') ?? 'Your Appointment has been Cancelled' }}
                </h6>
                <p class="text-muted mb-3" style="font-size:0.82rem">
                    {{ __('frontend.cancel_refund_note') ?? 'Your booking has been cancelled. Any applicable refund will be processed within 24 hours.' }}
                </p>
                <p style="font-size:0.75rem;color:var(--pp-muted);font-style:italic">
                    {{ __('frontend.check_history_refund') ?? '* Check your booking history for refund details.' }}
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="pp-btn pp-btn-teal" data-bs-dismiss="modal">
                    {{ __('messages.ok') ?? 'OK' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Rate modal --}}
<x-frontend::section.review />

@endsection

@push('after-scripts')
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script type="text/javascript">
(function () {
    'use strict';

    var finalColumns = [{
        data:       'card',
        name:       'card',
        orderable:  false,
        searchable: true
    }];

    var appointmentIdToCancel = null;
    var charge                = null;
    var cancellation_charge   = @json(setting('cancellation_charge'));
    var cancelltion_Type      = @json(setting('cancellation_type'));

    /* ── DataTable loader helper ── */
    function loadTable(tableId, activeTab, shimmerEl) {
        if ($.fn.dataTable.isDataTable('#' + tableId)) {
            $('#' + tableId).DataTable().clear().destroy();
        }

        var dataEl = document.getElementById(tableId);

        frontInitDatatable({
            url: '{{ route("appointment.index_data") }}',
            finalColumns: finalColumns,
            cardColumnClass: 'row-cols-1',
            advanceFilter: function () {
                return {
                    activeTab: activeTab,
                    doctor_id: $('#doctor').val()
                };
            },
            onLoadStart: function () {
                if (shimmerEl) shimmerEl.classList.remove('d-none');
                if (dataEl) dataEl.classList.add('d-none');
            },
            onLoadComplete: function () {
                if (shimmerEl) shimmerEl.classList.add('d-none');
                if (dataEl) dataEl.classList.remove('d-none');
            }
        });

        /* Re-assign the global DataTable reference used by doctor filter */
        window.renderedDataTable = $('#' + tableId).DataTable();
    }

    /* ── Boot ── */
    document.addEventListener('DOMContentLoaded', function () {

        /* Select2 doctor filter */
        $('#doctor').select2({
            placeholder:  '{{ __("clinic.lbl_select_doctor") }}',
            allowClear:   true,
            width:        '100%'
        });
        $('#doctor').on('change', function () {
            if (window.renderedDataTable) {
                window.renderedDataTable.draw();
            }
        });

        /* Initial load — "All" tab */
        var shimmer = document.getElementById('shimmer-loader');
        loadTable('datatable', 'all-appointments', shimmer);

        /* Tab switch */
        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function (e) {
                var activeTab  = e.target.getAttribute('data-tab');
                var targetId   = e.target.getAttribute('data-bs-target').replace('#', '');
                var tableMap   = {
                    'tab-all-pane':       'datatable',
                    'tab-upcoming-pane':  'datatable-upcoming',
                    'tab-completed-pane': 'datatable-completed'
                };
                var tableId = tableMap[targetId];
                if (tableId) {
                    loadTable(tableId, activeTab, null);
                }
            });
        });

        /* Cancel modal — populate charge info */
        document.getElementById('cancel-appointment').addEventListener('show.bs.modal', function (event) {
            var btn    = event.relatedTarget;
            var apptId = btn ? btn.getAttribute('data-appointment-id') : null;
            var fee    = btn ? parseFloat(btn.getAttribute('data-charge')) || 0 : 0;

            appointmentIdToCancel = apptId;
            charge                = fee;

            var feeHtml = '<span style="color:var(--pp-muted)">{{ __("messages.cancellation_fee") }}</span>' +
                          '<strong style="color:var(--pp-cancelled)">' + currencyFormat(fee) + '</strong>';
            document.getElementById('cancel_charge_info').innerHTML = feeHtml;

            /* Clear previous reason */
            document.getElementById('cancel_reason').value = '';
            document.getElementById('cancel_reason').classList.remove('is-invalid');
            document.getElementById('cancel_reason_error').classList.add('d-none');
        });
    });

    /* ── Cancellation ── */
    window.cancelAppointment = function () {
        var reasonEl = document.getElementById('cancel_reason');
        var reason   = reasonEl.value.trim();

        if (!reason) {
            reasonEl.classList.add('is-invalid');
            document.getElementById('cancel_reason_error').classList.remove('d-none');
            return;
        }
        reasonEl.classList.remove('is-invalid');
        document.getElementById('cancel_reason_error').classList.add('d-none');

        var btn = document.getElementById('confirm_btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ __("messages.loading") ?? "Loading..." }}';

        $.ajax({
            url:  '{{ route("cancel-appointment", "") }}/' + appointmentIdToCancel,
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                status:                    'cancelled',
                reason:                    reason,
                cancellation_charge_amount: charge,
                cancellation_type:          cancelltion_Type,
                cancellation_charge:        cancellation_charge
            },
            success: function (res) {
                btn.disabled = false;
                btn.innerHTML = '{{ __("messages.cancel_appointment") }}';

                if (res.status) {
                    bootstrap.Modal.getInstance(document.getElementById('cancel-appointment')).hide();
                    document.getElementById('cancel_reason').value = '';

                    if ($.fn.dataTable.isDataTable('#datatable')) {
                        $('#datatable').DataTable().ajax.reload();
                    }
                    new bootstrap.Modal(document.getElementById('success-confirmation-modal')).show();
                } else {
                    console.error('Cancel error:', res.message);
                }
            },
            error: function () {
                btn.disabled = false;
                btn.innerHTML = '{{ __("messages.cancel_appointment") }}';
            }
        });
    };

    /* ── Toastr config ── */
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            closeButton:   true,
            progressBar:   true,
            positionClass: 'toast-top-right',
            timeOut:       '3000'
        };
    }

    /* ── Encounter accordion toggle ── */
    window.toggleEncSection = function (bodyId, wrapId) {
        var body = document.getElementById(bodyId);
        var wrap = document.getElementById(wrapId);
        if (!body) return;
        var isOpen = body.style.display !== 'none';
        body.style.display = isOpen ? 'none' : 'block';
        if (wrap) wrap.classList.toggle('open', !isOpen);
        var toggle = wrap ? wrap.querySelector('[aria-expanded]') : null;
        if (toggle) toggle.setAttribute('aria-expanded', String(!isOpen));
    };

}());
</script>
@endpush
