@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    {{-- Stats Overview --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card rl-stat-card" style="border-left: 4px solid #6366f1;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rl-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        <i class="ph ph-clipboard-text"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('radiology_lab.lbl_total') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card rl-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rl-stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                        <i class="ph ph-hourglass-medium"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('radiology_lab.status_pending') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['pending'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card rl-stat-card" style="border-left: 4px solid #3b82f6;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rl-stat-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;">
                        <i class="ph ph-flask"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('radiology_lab.status_processing') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['processing'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card rl-stat-card" style="border-left: 4px solid #10b981;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="rl-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="ph ph-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('radiology_lab.status_completed') }}</p>
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
                <x-backend.quick-action url="{{ route('backend.test-orders.bulk_action') }}">
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            <option value="change-status">{{ __('radiology_lab.lbl_status') }}</option>
                            <option value="delete">{{ __('messages.delete') }}</option>
                            <option value="restore">{{ __('messages.restore') }}</option>
                            <option value="permanently-delete">{{ __('messages.permanent_delete') }}</option>
                        </select>
                    </div>
                    <div class="select-status d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select2" id="status" style="width:100%">
                            @foreach($statuses as $status)
                                @if($status !== 'cancelled')
                                    <option value="{{ $status }}">{{ __('radiology_lab.status_' . $status) }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </x-backend.quick-action>
            </div>

            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('messages.all') }} {{ __('radiology_lab.lbl_status') }}</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ isset($filter['status']) && $filter['status'] == $status ? 'selected' : '' }}>
                                    {{ __('radiology_lab.status_' . $status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <div class="datatable-filter">
                        <select name="priority" id="priority_filter" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('messages.all') }} {{ __('radiology_lab.lbl_priority') }}</option>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority }}" {{ isset($filter['priority']) && $filter['priority'] == $priority ? 'selected' : '' }}>
                                    {{ __('radiology_lab.priority_' . $priority) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text pe-0" id="addon-wrapping"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}" aria-label="Search" aria-describedby="addon-wrapping">
                </div>

                @can('add_test_orders')
                <a href="{{ route('backend.test-orders.create') }}" class="btn btn-primary d-flex align-items-center gap-1" id="add-order-button">
                    <i class="ph ph-plus-circle"></i>{{ __('messages.new') }}
                </a>
                @endcan
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
    .rl-stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-top: none;
        border-right: none;
        border-bottom: none;
    }
    .rl-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .rl-stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
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
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="test-order" onclick="selectAllTable(this)">',
            exportable: false,
            orderable: false,
            searchable: false,
        },
        {
            data: 'updated_at',
            name: 'updated_at',
            title: "{{ __('radiology_lab.lbl_updated_at') }}",
            orderable: true,
            visible: false,
        },
        {
            data: 'order_number',
            name: 'order_number',
            title: "{{ __('radiology_lab.lbl_order_number') }}"
        },
        {
            data: 'patient_name',
            name: 'patient_name',
            title: "{{ __('radiology_lab.lbl_patient_name') }}"
        },
        {
            data: 'technician_name',
            name: 'technician_name',
            title: "{{ __('radiology_lab.lbl_technician') }}"
        },
        {
            data: 'order_date',
            name: 'order_date',
            title: "{{ __('radiology_lab.lbl_order_date') }}"
        },
        {
            data: 'items_count',
            name: 'items_count',
            title: "{{ __('radiology_lab.lbl_tests') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'priority_badge',
            name: 'priority_badge',
            title: "{{ __('radiology_lab.lbl_priority') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('radiology_lab.lbl_status') }}"
        },
        {
            data: 'payment_badge',
            name: 'payment_badge',
            title: "{{ __('radiology_lab.lbl_payment') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'final_amount',
            name: 'final_amount',
            title: "{{ __('radiology_lab.lbl_amount') }}"
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
            url: '{{ route("backend.test-orders.index_data") }}',
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
