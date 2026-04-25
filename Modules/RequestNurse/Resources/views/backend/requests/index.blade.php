@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    {{-- Stats Overview --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nr-stat-card" style="border-left: 4px solid #6366f1;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nr-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        <i class="ph ph-clipboard-text"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.lbl_total') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nr-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nr-stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                        <i class="ph ph-hourglass-medium"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.status_pending') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['pending'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nr-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nr-stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="ph ph-spinner-gap"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.status_in_progress') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['in_progress'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nr-stat-card" style="border-left: 4px solid #10b981;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nr-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="ph ph-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.status_completed') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['completed'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <x-backend.quick-action url="{{ route('backend.nurse-requests.bulk_action') }}">
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            <option value="delete">{{ __('messages.delete') }}</option>
                            <option value="restore">{{ __('messages.restore') }}</option>
                            <option value="permanently-delete">{{ __('messages.permanent_delete') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
            </div>

            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="pending" {{ isset($filter['status']) && $filter['status'] == 'pending' ? 'selected' : '' }}>{{ __('request_nurse.status_pending') }}</option>
                            <option value="confirmed" {{ isset($filter['status']) && $filter['status'] == 'confirmed' ? 'selected' : '' }}>{{ __('request_nurse.status_confirmed') }}</option>
                            <option value="in_progress" {{ isset($filter['status']) && $filter['status'] == 'in_progress' ? 'selected' : '' }}>{{ __('request_nurse.status_in_progress') }}</option>
                            <option value="completed" {{ isset($filter['status']) && $filter['status'] == 'completed' ? 'selected' : '' }}>{{ __('request_nurse.status_completed') }}</option>
                            <option value="cancelled" {{ isset($filter['status']) && $filter['status'] == 'cancelled' ? 'selected' : '' }}>{{ __('request_nurse.status_cancelled') }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <div class="datatable-filter">
                        <select name="nurse_id" id="nurse_filter" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('request_nurse.lbl_filter_by_nurse') }}</option>
                            @foreach($nurses as $nurse)
                                <option value="{{ $nurse->nurse_id }}">{{ $nurse->user->first_name ?? '' }} {{ $nurse->user->last_name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text pe-0" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}" aria-label="Search" aria-describedby="addon-wrapping">
                </div>

                <a href="{{ route('backend.nurse-requests.create') }}" class="btn btn-primary d-flex align-items-center gap-1" id="add-request-button">
                    <i class="ph ph-plus-circle"></i>{{ __('messages.new') }}
                </a>
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
    .nr-stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-top: none;
        border-right: none;
        border-bottom: none;
    }
    .nr-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .nr-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
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
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="nurse-request" onclick="selectAllTable(this)">',
            exportable: false,
            orderable: false,
            searchable: false,
        },
        {
            data: 'updated_at',
            name: 'updated_at',
            title: "{{ __('request_nurse.lbl_updated_at') }}",
            orderable: true,
            visible: false,
        },
        {
            data: 'id',
            name: 'id',
            title: "{{ __('request_nurse.lbl_id') }}"
        },
        {
            data: 'patient_name',
            name: 'patient_name',
            title: "{{ __('request_nurse.lbl_patient_name') }}"
        },
        {
            data: 'nurse_name',
            name: 'nurse_name',
            title: "{{ __('request_nurse.lbl_nurse_name') }}"
        },
        {
            data: 'preferred_date',
            name: 'preferred_date',
            title: "{{ __('request_nurse.lbl_preferred_date') }}"
        },
        {
            data: 'city',
            name: 'city',
            title: "{{ __('request_nurse.lbl_city') }}"
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('request_nurse.lbl_status') }}"
        },
        {
            data: 'payment_badge',
            name: 'payment_badge',
            title: "{{ __('request_nurse.lbl_payment_status') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('request_nurse.lbl_action') }}",
            className: 'text-end'
        }
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.nurse-requests.index_data") }}',
            finalColumns: columns,
            orderColumn: [[1, "desc"]],
        });
    })

    function resetQuickAction () {
        const actionValue = $('#quick-action-type').val();
        if (actionValue != '') {
            $('#quick-action-apply').removeAttr('disabled');
            $('.quick-action-field').addClass('d-none');
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
