@extends('backend.layouts.app')

@section('title') {{ __($module_title) }} @endsection

@push('after-styles')
<link rel="stylesheet" href="{{ asset('css/doctor-portal.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatable/datatables.min.css') }}">
@endpush

@section('content')
<div class="dp-page">

    {{-- Breadcrumb --}}
    <div class="dp-breadcrumb mb-3">
        <span>{{ __('menu.main') }}</span>
        <i class="ph ph-caret-right"></i>
        <span class="dp-bc-active">{{ __($module_title) }}</span>
    </div>

    {{-- Filter Bar --}}
    <div class="dp-filter-bar">
        <label class="dp-filter-label"><i class="ph ph-funnel me-1"></i>{{ __('messages.filter') ?? 'Filter' }}</label>
        <div class="d-flex align-items-center gap-1">
            <label class="dp-filter-label">{{ __('messages.lbl_status') }}:</label>
            <select id="dp-filter-status" class="dp-filter-select">
                <option value="">{{ __('messages.all') }}</option>
                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
            </select>
        </div>
        <button type="button" class="dp-filter-reset" id="dp-filter-reset">
            <i class="ph ph-x me-1"></i>{{ __('messages.reset') ?? 'Reset' }}
        </button>
    </div>

    {{-- Table --}}
    <div class="table-content dp-has-filter mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between w-100">
                <x-backend.quick-action url='{{ route("backend.$module_name.bulk_action") }}'>
                    <div class="select">
                        <select name="action_type" class="form-control select2 col-12" id="quick-action-type">
                            <option value="">{{ __('messages.no_action') }}</option>
                            <option value="change-status">{{ __('messages.lbl_status') }}</option>
                            <option value="delete">{{ __('messages.delete') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
                <a href="{{ route('backend.doctor-independent-service.create') }}" class="btn btn-primary">
                    <i class="ph ph-plus-circle me-1"></i> {{ __('messages.new') }}
                </a>
            </div>
        </x-backend.section-header>

        <table id="datatable" class="table table-responsive">
            <thead>
                <tr>
                    <th><input type="checkbox" class="form-check-input" id="datatable_checkbox"></th>
                    @if(auth()->user()->hasAnyRole(['admin', 'demo_admin']))
                    <th>{{ __('clinic.lbl_doctor') }}</th>
                    @endif
                    <th>{{ __('messages.lbl_name') }}</th>
                    <th>{{ __('messages.lbl_duration') }}</th>
                    <th>{{ __('messages.lbl_charges') }}</th>
                    <th>{{ __('messages.lbl_discount') }}</th>
                    <th>{{ __('messages.lbl_status') }}</th>
                    <th>{{ __('messages.lbl_action') }}</th>
                </tr>
            </thead>
        </table>
    </div>

</div>
@endsection

@push('after-scripts')
<script type="text/javascript" src="{{ asset('vendor/datatable/datatables.min.js') }}"></script>
<script>
$(document).ready(function () {

    var dataTable = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        language: {
            emptyTable:  '<div style="padding:2rem;text-align:center"><i class="ph ph-list-dashes" style="font-size:2rem;color:#9CA3AF;display:block;margin-bottom:6px"></i><span style="font-size:0.82rem;color:#6B7280">No independent services found</span></div>',
            zeroRecords: '<div style="padding:2rem;text-align:center"><i class="ph ph-magnifying-glass" style="font-size:2rem;color:#9CA3AF;display:block;margin-bottom:6px"></i><span style="font-size:0.82rem;color:#6B7280">No matching records</span></div>'
        },
        ajax: {
            url: '{{ route("backend.doctor-independent-service.index_data") }}',
            data: function (d) {
                d.status = $('#dp-filter-status').val();
            }
        },
        columns: [
            {
                data: 'id', orderable: false, searchable: false,
                render: function (data) {
                    return '<input type="checkbox" class="form-check-input select-table-row"' +
                        ' id="datatable-row-' + data + '"' +
                        ' name="ids[]" value="' + data + '"' +
                        ' onclick="dataTableRowCheck(' + data + ')">';
                }
            },
            @if(auth()->user()->hasAnyRole(['admin', 'demo_admin']))
            { data: 'doctor_name', orderable: false },
            @endif
            { data: 'name' },
            {
                data: 'duration_min',
                render: function (data) {
                    return '<span class="dp-duration">' + (data || 0) + ' min</span>';
                }
            },
            { data: 'charges' },
            {
                data: 'discount',
                render: function (data) {
                    return parseInt(data) === 1
                        ? '<span class="dp-yn-yes"><i class="ph ph-check me-1"></i>Yes</span>'
                        : '<span class="dp-yn-no">No</span>';
                }
            },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    $('#dp-filter-status').on('change', function () { dataTable.ajax.reload(); });
    $('#dp-filter-reset').on('click', function () {
        $('#dp-filter-status').val('');
        dataTable.ajax.reload();
    });
});
</script>
@endpush
