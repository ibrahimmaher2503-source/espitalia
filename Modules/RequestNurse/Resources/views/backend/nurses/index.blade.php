@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    {{-- Stats Overview --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nurse-stat-card" style="border-left: 4px solid #6366f1;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nurse-stat-icon" style="background: rgba(99,102,241,0.1); color: #6366f1;">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.lbl_total') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['total'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nurse-stat-card" style="border-left: 4px solid #10b981;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nurse-stat-icon" style="background: rgba(16,185,129,0.1); color: #10b981;">
                        <i class="ph ph-check-circle"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('messages.active') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['active'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nurse-stat-card" style="border-left: 4px solid #06b6d4;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nurse-stat-icon" style="background: rgba(6,182,212,0.1); color: #06b6d4;">
                        <i class="ph ph-heart-half"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.status_available') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['available'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card nurse-stat-card" style="border-left: 4px solid #f59e0b;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="nurse-stat-icon" style="background: rgba(245,158,11,0.1); color: #f59e0b;">
                        <i class="ph ph-clock-countdown"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase" style="letter-spacing:0.5px;">{{ __('request_nurse.status_busy') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['busy'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <x-backend.quick-action url="{{ route('backend.nurses.bulk_action') }}">
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
                            <option value="1" selected>{{ __('messages.active') }}</option>
                            <option value="0">{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
            </div>

            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="0" {{ isset($filter['status']) && $filter['status'] == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                            <option value="1" {{ isset($filter['status']) && $filter['status'] == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text pe-0" id="addon-wrapping"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}" aria-label="Search" aria-describedby="addon-wrapping">
                </div>

                <a href="{{ route('backend.nurses.create') }}" class="btn btn-primary d-flex align-items-center gap-1" id="add-nurse-button">
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
    .nurse-stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-top: none;
        border-right: none;
        border-bottom: none;
    }
    .nurse-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    }
    .nurse-stat-icon {
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
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="nurse" onclick="selectAllTable(this)">',
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
            data: 'nurse_name',
            name: 'nurse_name',
            title: "{{ __('request_nurse.lbl_name') }}"
        },
        {
            data: 'specialization',
            name: 'specialization',
            title: "{{ __('request_nurse.lbl_specialization') }}"
        },
        {
            data: 'experience',
            name: 'experience',
            title: "{{ __('request_nurse.lbl_experience') }}"
        },
        {
            data: 'hourly_rate',
            name: 'hourly_rate',
            title: "{{ __('request_nurse.lbl_hourly_rate') }}"
        },
        {
            data: 'availability_status',
            name: 'availability_status',
            title: "{{ __('request_nurse.lbl_availability_status') }}"
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('request_nurse.lbl_status') }}"
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
            url: '{{ route("backend.nurses.index_data") }}',
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
