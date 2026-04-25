@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="text-muted small">{{ __('menu.main') }}</span>
        <i class="ph ph-caret-double-right text-muted" style="font-size: 0.7rem;"></i>
        <span class="fw-medium small">{{ __($module_title) }}</span>
    </div>
    {{-- Data Table --}}
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <x-backend.quick-action url="{{ route('backend.hospitals.bulk_action') }}">
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
                    <span class="input-group-text pe-0" id="addon-wrapping"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}" aria-label="Search" aria-describedby="addon-wrapping">
                </div>

                @can('add_hospitals')
                <a href="{{ route('backend.hospitals.create') }}" class="btn btn-primary d-flex align-items-center gap-1" id="add-hospital-button">
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
                <a href="#" class="dismiss-link text-decoration-none text-success" onclick="dismissSnackbar(event)">{{ __('messages.dismiss') }}</a>
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
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="hospital" onclick="selectAllTable(this)">',
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
            data: 'name_ar',
            name: 'name_ar',
            title: "{{ __('intensive_care.lbl_name_ar') }}"
        },
        {
            data: 'name_en',
            name: 'name_en',
            title: "{{ __('intensive_care.lbl_name_en') }}"
        },
        {
            data: 'governorate_name',
            name: 'governorate_name',
            title: "{{ __('intensive_care.lbl_governorate') }}",
            orderable: false,
        },
        {
            data: 'city_name',
            name: 'city_name',
            title: "{{ __('intensive_care.lbl_city') }}",
            orderable: false,
        },
        {
            data: 'hospital_type',
            name: 'hospital_type',
            title: "{{ __('intensive_care.lbl_hospital_type') }}"
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('intensive_care.lbl_status') }}"
        },
        {
            data: 'action',
            name: 'action',
            orderable: false,
            searchable: false,
            title: "{{ __('messages.action') }}",
            className: 'text-end'
        }
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.hospitals.index_data") }}',
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
