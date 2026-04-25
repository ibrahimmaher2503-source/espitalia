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
        <div class="d-flex align-items-center gap-1">
            <label class="dp-filter-label">{{ __('messages.lbl_day') ?? 'Day' }}:</label>
            <select id="dp-filter-day" class="dp-filter-select">
                <option value="">{{ __('messages.all') }}</option>
                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $d)
                <option value="{{ $d }}">{{ ucfirst($d) }}</option>
                @endforeach
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
                <a href="{{ route('backend.doctor-call-session.create') }}" class="btn btn-primary">
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
                    <th>{{ __('messages.lbl_day') }}</th>
                    <th>{{ __('messages.lbl_start_time') }}</th>
                    <th>{{ __('messages.lbl_end_time') }}</th>
                    <th>{{ __('messages.lbl_holiday') }}</th>
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

    /* Format "HH:MM:SS" or "HH:MM" → "9:00 AM" */
    function fmtTime(val) {
        if (!val) return '<span style="color:#9CA3AF">—</span>';
        var parts = val.split(':');
        var h = parseInt(parts[0], 10);
        var m = parseInt(parts[1] || 0, 10);
        var suffix = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return '<span class="dp-time">' + h + (m ? ':' + String(m).padStart(2, '0') : '') + '\u202f' + suffix + '</span>';
    }

    var dataTable = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        language: {
            emptyTable:  '<div style="padding:2rem;text-align:center"><i class="ph ph-calendar-blank" style="font-size:2rem;color:#9CA3AF;display:block;margin-bottom:6px"></i><span style="font-size:0.82rem;color:#6B7280">No sessions configured</span></div>',
            zeroRecords: '<div style="padding:2rem;text-align:center"><i class="ph ph-magnifying-glass" style="font-size:2rem;color:#9CA3AF;display:block;margin-bottom:6px"></i><span style="font-size:0.82rem;color:#6B7280">No matching records</span></div>'
        },
        ajax: {
            url: '{{ route("backend.doctor-call-session.index_data") }}',
            data: function (d) {
                d.status = $('#dp-filter-status').val();
                d.day    = $('#dp-filter-day').val();
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
            {
                data: 'day',
                render: function (data) {
                    if (!data) return '<span style="color:#9CA3AF">—</span>';
                    return '<span class="dp-day-pill">' + (data + '').charAt(0).toUpperCase() + (data + '').slice(1) + '</span>';
                }
            },
            { data: 'start_time', render: function (data) { return fmtTime(data); } },
            { data: 'end_time',   render: function (data) { return fmtTime(data); } },
            {
                data: 'is_holiday',
                render: function (data) {
                    return parseInt(data) === 1
                        ? '<span class="dp-status dp-holiday-yes"><i class="ph ph-sun me-1"></i>Holiday</span>'
                        : '<span class="dp-status dp-holiday-no">Working</span>';
                }
            },
            { data: 'status' },
            { data: 'action', orderable: false, searchable: false }
        ]
    });

    $('#dp-filter-status, #dp-filter-day').on('change', function () { dataTable.ajax.reload(); });
    $('#dp-filter-reset').on('click', function () {
        $('#dp-filter-status, #dp-filter-day').val('');
        dataTable.ajax.reload();
    });
});
</script>
@endpush
