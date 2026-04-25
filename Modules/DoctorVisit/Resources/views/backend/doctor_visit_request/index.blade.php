@extends('backend.layouts.app')

@section('title') {{ __('doctor_visit.plural_title') }} @endsection

@section('content')
    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card" style="border-left:4px solid #6366f1;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(99,102,241,.1);color:#6366f1;padding:10px;border-radius:8px;">
                        <i class="ph ph-house-line fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase">{{ __('messages.total') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card" style="border-left:4px solid #f59e0b;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(245,158,11,.1);color:#f59e0b;padding:10px;border-radius:8px;">
                        <i class="ph ph-hourglass-medium fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase">{{ __('doctor_visit.status_pending') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['pending'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card" style="border-left:4px solid #3b82f6;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(59,130,246,.1);color:#3b82f6;padding:10px;border-radius:8px;">
                        <i class="ph ph-check-circle fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase">{{ __('doctor_visit.status_confirmed') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['confirmed'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 col-sm-6">
            <div class="card" style="border-left:4px solid #10b981;">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div style="background:rgba(16,185,129,.1);color:#10b981;padding:10px;border-radius:8px;">
                        <i class="ph ph-seal-check fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 small text-uppercase">{{ __('doctor_visit.status_completed') }}</p>
                        <h4 class="mb-0 fw-bold">{{ $stats['completed'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card-main mb-5">
        <x-backend.section-header>
            <div class="d-flex flex-wrap gap-3">
                <x-backend.quick-action url="{{ route('backend.doctor_visit.bulk_action') }}">
                    <div>
                        <select name="action_type" class="form-control select2 col-12" style="width:100%">
                            <option value="">{{ __('messages.no_action') }}</option>
                            <option value="delete">{{ __('messages.delete') }}</option>
                        </select>
                    </div>
                </x-backend.quick-action>
            </div>

            <x-slot name="toolbar">
                {{-- Status filter --}}
                <div class="d-flex gap-2 align-items-center">
                    <select id="status-filter" class="form-select form-select-sm" style="width:160px;">
                        <option value="">{{ __('doctor_visit.lbl_status') }}: {{ __('messages.all') }}</option>
                        <option value="pending">{{ __('doctor_visit.status_pending') }}</option>
                        <option value="confirmed">{{ __('doctor_visit.status_confirmed') }}</option>
                        <option value="cancelled">{{ __('doctor_visit.status_cancelled') }}</option>
                        <option value="completed">{{ __('doctor_visit.status_completed') }}</option>
                    </select>
                </div>
            </x-slot>

            <x-slot name="subtitle">
                <div class="d-flex flex-wrap gap-3">
                    <div>
                        <i class="{{ $module_icon }}"></i>
                        {{ __($module_title) }}
                    </div>
                </div>
            </x-slot>
        </x-backend.section-header>

        <div class="card-body">
            <table id="datatable" class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-table-row" /></th>
                        <th>{{ __('doctor_visit.lbl_reference_number') }}</th>
                        <th>{{ __('doctor_visit.lbl_patient_name') }}</th>
                        <th>{{ __('doctor_visit.lbl_preferred_date') }}</th>
                        <th>{{ __('doctor_visit.lbl_assigned_doctor') }}</th>
                        <th>{{ __('doctor_visit.lbl_status') }}</th>
                        <th>{{ __('doctor_visit.lbl_action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
$(document).ready(function () {
    var table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("backend.doctor_visit.index_data") }}',
            type: 'POST',
            data: function(d) {
                d._token = '{{ csrf_token() }}';
                d.status = $('#status-filter').val();
            }
        },
        columns: [
            { data: 'check', name: 'check', orderable: false, searchable: false },
            { data: 'reference_number', name: 'reference_number' },
            { data: 'patient_name', name: 'patient_name', orderable: false },
            { data: 'preferred_date', name: 'preferred_date' },
            { data: 'assigned_doctor_name', name: 'assigned_doctor_name', orderable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#status-filter').on('change', function() { table.ajax.reload(); });
});
</script>
@endpush
