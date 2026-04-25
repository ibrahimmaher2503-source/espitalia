@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@section('content')
<div class="row">
    {{-- Request Details --}}
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="ph ph-house-line me-2"></i>{{ __('doctor_visit.singular_title') }}
                </h5>
                @php
                    $colors = ['pending'=>'warning','confirmed'=>'primary','cancelled'=>'danger','completed'=>'success'];
                @endphp
                <span class="badge bg-{{ $colors[$visitRequest->status] ?? 'secondary' }} fs-6">
                    {{ __('doctor_visit.status_' . $visitRequest->status) }}
                </span>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_reference_number') }}</dt>
                    <dd class="col-sm-8"><code>{{ $visitRequest->reference_number }}</code></dd>

                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_patient_name') }}</dt>
                    <dd class="col-sm-8">{{ optional($visitRequest->patient)->full_name ?? '-' }}</dd>

                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_visit_reason') }}</dt>
                    <dd class="col-sm-8">{{ $visitRequest->visit_reason }}</dd>

                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_preferred_date') }}</dt>
                    <dd class="col-sm-8">{{ $visitRequest->preferred_date }}</dd>

                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_contact_phone') }}</dt>
                    <dd class="col-sm-8">{{ $visitRequest->contact_phone }}</dd>

                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_preferred_doctor') }}</dt>
                    <dd class="col-sm-8">{{ optional(optional($visitRequest->preferredDoctor)->user)->full_name ?? __('messages.not_set') }}</dd>

                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_assigned_doctor') }}</dt>
                    <dd class="col-sm-8">{{ optional(optional($visitRequest->assignedDoctor)->user)->full_name ?? __('messages.not_set') }}</dd>

                    @if($visitRequest->additional_notes)
                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_additional_notes') }}</dt>
                    <dd class="col-sm-8">{{ $visitRequest->additional_notes }}</dd>
                    @endif

                    @if($visitRequest->cancellation_reason)
                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_cancellation_reason') }}</dt>
                    <dd class="col-sm-8 text-danger">{{ $visitRequest->cancellation_reason }}</dd>
                    @endif

                    @if($visitRequest->completed_at)
                    <dt class="col-sm-4">{{ __('doctor_visit.lbl_completed_at') }}</dt>
                    <dd class="col-sm-8">{{ $visitRequest->completed_at }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Status History --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-clock-countdown me-2"></i>{{ __('doctor_visit.lbl_status_history') }}</h6></div>
            <div class="card-body p-0">
                @forelse($visitRequest->statusHistories as $history)
                <div class="d-flex gap-3 p-3 border-bottom">
                    <div class="flex-shrink-0 text-muted small pt-1">{{ $history->created_at->format('d M Y H:i') }}</div>
                    <div>
                        <span class="badge bg-secondary">{{ $history->old_status ?? 'new' }}</span>
                        <i class="ph ph-arrow-right mx-1"></i>
                        <span class="badge bg-{{ $colors[$history->new_status] ?? 'secondary' }}">{{ $history->new_status }}</span>
                        <div class="small text-muted">by {{ optional($history->changedBy)->full_name ?? '—' }}</div>
                        @if($history->note)<div class="small mt-1">{{ $history->note }}</div>@endif
                    </div>
                </div>
                @empty
                <p class="p-3 text-muted mb-0">{{ __('doctor_visit.lbl_no_history') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right Sidebar --}}
    <div class="col-lg-4">
        {{-- Admin Notes --}}
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-note me-2"></i>{{ __('doctor_visit.lbl_admin_notes') }}</h6></div>
            <div class="card-body">
                <form id="notes-form">
                    @csrf
                    <textarea name="admin_notes" class="form-control mb-2" rows="4">{{ $visitRequest->admin_notes }}</textarea>
                    <button type="submit" class="btn btn-sm btn-secondary w-100">{{ __('doctor_visit.lbl_save_notes') }}</button>
                </form>
            </div>
        </div>

        {{-- Update Status --}}
        @if(count($validTransitions) > 0)
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-arrows-clockwise me-2"></i>{{ __('doctor_visit.lbl_update_status') }}</h6></div>
            <div class="card-body">
                <form id="status-form">
                    @csrf
                    <select name="status" class="form-select mb-2">
                        @foreach($validTransitions as $s)
                        <option value="{{ $s }}">{{ __('doctor_visit.status_' . $s) }}</option>
                        @endforeach
                    </select>
                    <textarea name="cancellation_reason" class="form-control mb-2 d-none" id="cancel-reason" rows="2" placeholder="{{ __('doctor_visit.lbl_cancellation_reason') }}"></textarea>
                    <button type="submit" class="btn btn-sm btn-primary w-100">{{ __('doctor_visit.lbl_update_status') }}</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Assign Doctor (admin/receptionist only) --}}
        @if(auth()->user()->hasAnyRole(['admin', 'receptionist']))
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0"><i class="ph ph-user-plus me-2"></i>{{ __('doctor_visit.lbl_assigned_doctor') }}</h6></div>
            <div class="card-body">
                <form id="assign-form">
                    @csrf
                    <select name="doctor_id" class="form-select mb-2" id="assign-doctor-select" style="width:100%">
                        <option value="">{{ __('doctor_visit.lbl_select_doctor') }}</option>
                        @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" {{ $visitRequest->assigned_doctor_id == $doctor->id ? 'selected' : '' }}>
                            {{ optional($doctor->user)->full_name ?? '-' }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">{{ __('doctor_visit.lbl_assign_doctor') }}</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('after-scripts')
<script>
const ref = '{{ $visitRequest->reference_number }}';
const token = '{{ csrf_token() }}';

$('#status-form select[name=status]').on('change', function() {
    $('#cancel-reason').toggleClass('d-none', $(this).val() !== 'cancelled');
});

$('#status-form').on('submit', function(e) {
    e.preventDefault();
    $.ajax({
        url: `/api/v1/admin/doctor-visit/requests/${ref}/status`,
        method: 'PUT',
        headers: {'X-CSRF-TOKEN': token},
        data: {
            status: $('select[name=status]').val(),
            cancellation_reason: $('textarea[name=cancellation_reason]').val()
        }
    }).then(() => location.reload()).catch(r => alert(r.responseJSON?.message ?? 'Error'));
});

$('#notes-form').on('submit', function(e) {
    e.preventDefault();
    $.post(`/api/v1/admin/doctor-visit/requests/${ref}/notes`, {
        _token: token,
        admin_notes: $('textarea[name=admin_notes]').val()
    }).then(() => alert('{{ __("doctor_visit.notes_updated_success") }}')).catch(r => alert(r.responseJSON?.message ?? 'Error'));
});

$('#assign-form').on('submit', function(e) {
    e.preventDefault();
    var doctorId = $('#assign-doctor-select').val();
    if (!doctorId) return;
    $.ajax({
        url: `/api/v1/admin/doctor-visit/requests/${ref}/assign-doctor`,
        method: 'PUT',
        headers: {'X-CSRF-TOKEN': token},
        data: { doctor_id: doctorId }
    }).then(() => location.reload()).catch(r => alert(r.responseJSON?.message ?? 'Error'));
});
</script>
@endpush
