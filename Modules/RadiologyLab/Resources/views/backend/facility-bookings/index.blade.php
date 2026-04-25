@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="text-muted small">{{ __('menu.main') }}</span>
        <i class="ph ph-caret-double-right text-muted" style="font-size: 0.7rem;"></i>
        <span class="fw-medium small">{{ __($module_title) }}</span>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1 fw-bold">{{ $stats['total'] }}</h3>
                    <small class="text-muted">{{ __('messages.total') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1 fw-bold text-warning">{{ $stats['pending'] }}</h3>
                    <small class="text-muted">{{ __('radiology_lab.status_pending') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1 fw-bold text-info">{{ $stats['confirmed'] }}</h3>
                    <small class="text-muted">{{ __('radiology_lab.status_confirmed') }}</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body text-center py-3">
                    <h3 class="mb-1 fw-bold text-success">{{ $stats['completed'] }}</h3>
                    <small class="text-muted">{{ __('radiology_lab.status_completed') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <x-backend.quick-action url="{{ route('backend.facility-bookings.bulk_action') }}">
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            <option value="change-status">{{ __('messages.lbl_status') }}</option>
                            <option value="delete">{{ __('messages.delete') }}</option>
                            <option value="restore">{{ __('messages.restore') }}</option>
                            <option value="permanently-delete">{{ __('messages.permanent_delete') }}</option>
                        </select>
                    </div>
                    <div class="select-status d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select2" id="status" style="width:100%">
                            <option value="confirmed">{{ __('radiology_lab.status_confirmed') }}</option>
                            <option value="completed">{{ __('radiology_lab.status_completed') }}</option>
                            <option value="cancelled">{{ __('radiology_lab.status_cancelled') }}</option>
                            <option value="no_show">{{ __('radiology_lab.status_no_show') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
            </div>

            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="pending" {{ isset($filter['status']) && $filter['status'] == 'pending' ? 'selected' : '' }}>{{ __('radiology_lab.status_pending') }}</option>
                            <option value="confirmed" {{ isset($filter['status']) && $filter['status'] == 'confirmed' ? 'selected' : '' }}>{{ __('radiology_lab.status_confirmed') }}</option>
                            <option value="completed" {{ isset($filter['status']) && $filter['status'] == 'completed' ? 'selected' : '' }}>{{ __('radiology_lab.status_completed') }}</option>
                            <option value="cancelled" {{ isset($filter['status']) && $filter['status'] == 'cancelled' ? 'selected' : '' }}>{{ __('radiology_lab.status_cancelled') }}</option>
                            <option value="no_show" {{ isset($filter['status']) && $filter['status'] == 'no_show' ? 'selected' : '' }}>{{ __('radiology_lab.status_no_show') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <div class="datatable-filter">
                        <select name="type" id="type_filter" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('radiology_lab.lbl_all_types') }}</option>
                            <option value="lab" {{ isset($filter['type']) && $filter['type'] == 'lab' ? 'selected' : '' }}>{{ __('radiology_lab.type_lab') }}</option>
                            <option value="radiology" {{ isset($filter['type']) && $filter['type'] == 'radiology' ? 'selected' : '' }}>{{ __('radiology_lab.type_radiology') }}</option>
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text pe-0" id="addon-wrapping"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}" aria-label="Search" aria-describedby="addon-wrapping">
                </div>
            </x-slot>
        </x-backend.section-header>

        <table id="datatable" class="table table-responsive">
        </table>
    </div>

    @if(session('success'))
        <div class="snackbar" id="snackbar">
            <div class="d-flex justify-content-around align-items-center">
                <p class="mb-0">{{ session('success') }}</p>
                <a href="#" class="dismiss-link text-decoration-none text-success" onclick="dismissSnackbar(event)">Dismiss</a>
            </div>
        </div>
    @endif
@endsection

@push('after-styles')
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
<style>
    .card-main {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        padding: 1.25rem;
    }
</style>
@endpush

@push('after-scripts')
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script type="text/javascript" defer>
    const columns = [
        {
            name: 'check',
            data: 'check',
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="facility-booking" onclick="selectAllTable(this)">',
            exportable: false,
            orderable: false,
            searchable: false,
        },
        {
            data: 'updated_at',
            name: 'updated_at',
            title: "{{ __('messages.lbl_updated_at') }}",
            orderable: true,
            visible: false,
        },
        {
            data: 'booking_number',
            name: 'booking_number',
            title: "{{ __('radiology_lab.lbl_booking_number') }}"
        },
        {
            data: 'type',
            name: 'type',
            title: "{{ __('radiology_lab.lbl_booking_type') }}"
        },
        {
            data: 'facility_name',
            name: 'facility_name',
            title: "{{ __('radiology_lab.lbl_facility_name') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'patient_display',
            name: 'patient_display',
            title: "{{ __('radiology_lab.lbl_patient') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'booking_date',
            name: 'booking_date',
            title: "{{ __('radiology_lab.lbl_booking_date') }}"
        },
        {
            data: 'booking_time',
            name: 'booking_time',
            title: "{{ __('radiology_lab.lbl_booking_time') }}"
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('radiology_lab.lbl_status') }}"
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('radiology_lab.lbl_action') }}",
            className: 'text-end'
        }
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.facility-bookings.index_data") }}',
            finalColumns: columns,
            orderColumn: [[1, "desc"]],
        });
    })

    function resetQuickAction () {
        const actionValue = $('#quick-action-type').val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');
            if (actionValue == 'change-status') {
                $('.quick-action-field').addClass('d-none');
                $('#change-status-action').removeClass('d-none');
            } else {
                $('.quick-action-field').addClass('d-none');
            }
        } else {
            $('#quick-action-apply').attr('disabled', true);
            $('.quick-action-field').addClass('d-none');
        }
    }

    $('#quick-action-type').change(function () {
        resetQuickAction()
    });

    $(document).on('update_quick_action', function() {})
</script>
@endpush
