@extends('backend.layouts.app')

@section('title') {{ __($module_action) }} {{ __($module_title) }} @endsection

@push('after-styles')
<style>
    .card-main {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        padding: 1.25rem;
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-3">
    <span class="text-muted small">{{ __('menu.main') }}</span>
    <i class="ph ph-caret-double-right text-muted" style="font-size: 0.7rem;"></i>
    <span class="fw-medium small">{{ __('specialization.lbl_specializations') }}</span>
</div>
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                @if(auth()->user()->can('edit_specialization') || auth()->user()->can('delete_specialization'))
                <x-backend.quick-action url="{{ route('backend.' . $module_name . '.bulk_action') }}">
                    <div class="">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            @can('edit_specialization')
                            <option value="change-status">{{ __('messages.lbl_status') }}</option>
                            @endcan
                            @can('delete_specialization')
                            <option value="delete">{{ __('messages.delete') }}</option>
                            <option value="restore">{{ __('messages.restore') }}</option>
                            <option value="permanently-delete">{{ __('messages.permanent_delete') }}</option>
                            @endcan
                        </select>
                    </div>
                    <div class="select-status d-none quick-action-field" id="change-status-action">
                        <select name="status" class="form-control select2" id="status" style="width:100%">
                            <option value="1" selected>{{ __('messages.active') }}</option>
                            <option value="0">{{ __('messages.inactive') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
                @endif
            </div>

            <x-slot name="toolbar">
                <div>
                    <div class="datatable-filter">
                        <select name="column_status" id="column_status" class="select2 form-control" data-filter="select" style="width: 100%">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="0" {{ ($filter['status'] ?? '') == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                            <option value="1" {{ ($filter['status'] ?? '') == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                        </select>
                    </div>
                </div>
                <div class="input-group flex-nowrap">
                    <span class="input-group-text pe-0" id="addon-wrapping"><i class="ph ph-magnifying-glass"></i></span>
                    <input type="text" class="form-control dt-search" placeholder="{{ __('messages.search') }}" aria-label="Search" aria-describedby="addon-wrapping">
                </div>

                @can('add_specialization')
                <a href="{{ route('backend.' . $module_name . '.create') }}" class="btn btn-primary d-flex align-items-center gap-1" id="add-post-button">
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
@endpush

@push('after-scripts')
<script src="{{ asset('js/form-modal/index.js') }}" defer></script>
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script type="text/javascript" defer>
    const columns = [
        {
            name: 'check',
            data: 'check',
            title: '<input type="checkbox" class="form-check-input" name="select_all_table" id="select-all-table" data-type="specializations" onclick="selectAllTable(this)">',
            width: '0%',
            exportable: false,
            orderable: false,
            searchable: false,
        },
        {
            data: 'name_en',
            name: 'name_en',
            title: "{{ __('specialization.lbl_specialization') }}"
        },
        {
            data: 'parent_name',
            name: 'parent_name',
            title: "{{ __('specialization.lbl_parent') }}",
            orderable: false,
            searchable: false,
        },
        {
            data: 'status',
            name: 'status',
            title: "{{ __('messages.lbl_status') }}",
            width: '5%',
        },
        {
            data: 'show_on_home',
            name: 'show_on_home',
            title: "{{ __('specialization.lbl_show_on_home') }}",
            width: '5%',
            orderable: false,
            searchable: false,
        },
        {
            data: 'display_order',
            name: 'display_order',
            title: "{{ __('specialization.lbl_display_order') }}",
            width: '5%',
        },
        {
            data: 'updated_at',
            name: 'updated_at',
            title: "{{ __('messages.lbl_updated_at') }}",
            width: '10%',
        },
        {
            data: 'action',
            name: 'action',
            title: "{{ __('messages.lbl_action') }}",
            width: '5%',
            orderable: false,
            searchable: false
        }
    ]

    document.addEventListener('DOMContentLoaded', (event) => {
        initDatatable({
            url: '{{ route("backend.$module_name.index_data") }}',
            finalColumns: columns,
            orderColumn: [[0, "desc"]],
            advanceFilter: () => {
                return {}
            }
        });
    })

    function resetQuickAction() {
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
</script>
@endpush
