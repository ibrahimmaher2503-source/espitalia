@extends('backend.layouts.app')

@section('title') {{ $pageTitle }} @endsection

@php
    $statusOrder = ['pending', 'confirmed', 'completed'];
    $currentIndex = array_search($booking->status, $statusOrder);
    $isCancelled = $booking->status === 'cancelled';
    $isNoShow = $booking->status === 'no_show';
@endphp

@section('content')
    {{-- Header with Status Timeline --}}
    <div class="card rl-detail-card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                <div>
                    <h5 class="mb-1">{{ __('radiology_lab.booking_singular') }} <span class="text-muted">{{ $booking->booking_number }}</span></h5>
                    <p class="text-muted mb-0">
                        {{ __('radiology_lab.lbl_created') }}: {{ $booking->created_at ? $booking->created_at->format('d M Y, H:i') : '-' }}
                    </p>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    @php
                        $statusColors = ['pending' => 'warning', 'confirmed' => 'info', 'completed' => 'success', 'cancelled' => 'danger', 'no_show' => 'secondary'];
                        $color = $statusColors[$booking->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-soft-{{ $color }} text-{{ $color }}">{{ __('radiology_lab.status_' . $booking->status) }}</span>
                    <a href="{{ route('backend.facility-bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="ph ph-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                </div>
            </div>

            {{-- Status Timeline --}}
            @if(!$isCancelled && !$isNoShow)
            <div class="rl-timeline">
                @foreach($statusOrder as $i => $step)
                    @php
                        $isActive = $currentIndex !== false && $i <= $currentIndex;
                        $isCurrent = $currentIndex !== false && $i === $currentIndex;
                        $stepColors = ['pending' => '#f59e0b', 'confirmed' => '#06b6d4', 'completed' => '#10b981'];
                        $dotColor = $isActive ? $stepColors[$step] : '#d1d5db';
                    @endphp
                    <div class="rl-timeline-step {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
                        <div class="rl-timeline-dot" style="background: {{ $dotColor }}; {{ $isCurrent ? 'box-shadow: 0 0 0 4px ' . $dotColor . '33;' : '' }}">
                            @if($isActive && !$isCurrent)
                                <i class="ph ph-check" style="font-size:0.7rem;color:#fff;"></i>
                            @endif
                        </div>
                        <span class="rl-timeline-label" style="{{ $isActive ? 'color:' . $dotColor . '; font-weight:600;' : '' }}">
                            {{ __('radiology_lab.status_' . $step) }}
                        </span>
                    </div>
                    @if(!$loop->last)
                        <div class="rl-timeline-line" style="background: {{ $isActive && $i < $currentIndex ? $stepColors[$statusOrder[$i+1]] : '#e5e7eb' }};"></div>
                    @endif
                @endforeach
            </div>
            @elseif($isCancelled)
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" style="border-radius:10px;">
                    <i class="ph ph-x-circle fs-4"></i>
                    <div>
                        <strong>{{ __('radiology_lab.status_cancelled') }}</strong>
                        @if($booking->cancellation_reason)
                            <p class="mb-0 mt-1">{{ $booking->cancellation_reason }}</p>
                        @endif
                    </div>
                </div>
            @elseif($isNoShow)
                <div class="alert alert-secondary d-flex align-items-center gap-2 mb-0" style="border-radius:10px;">
                    <i class="ph ph-user-minus fs-4"></i>
                    <strong>{{ __('radiology_lab.status_no_show') }}</strong>
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        {{-- Booking Details --}}
        <div class="col-lg-8">
            <div class="card mb-4" style="border-radius:12px; border:1px solid rgba(0,0,0,0.08);">
                <div class="card-header bg-transparent" style="border-bottom:1px solid rgba(0,0,0,0.06); padding:1rem 1.25rem;">
                    <h6 class="mb-0" style="color:#374151; font-weight:600;"><i class="ph ph-info me-1"></i> {{ __('radiology_lab.lbl_booking_details') }}</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%;">{{ __('radiology_lab.lbl_booking_number') }}</td>
                            <td class="fw-medium">{{ $booking->booking_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_booking_type') }}</td>
                            <td>
                                @if($booking->type === 'lab')
                                    <span class="badge bg-soft-primary text-primary">{{ __('radiology_lab.type_lab') }}</span>
                                @else
                                    <span class="badge bg-soft-info text-info">{{ __('radiology_lab.type_radiology') }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_facility_name') }}</td>
                            <td>{{ $booking->facility ? $booking->facility->name : '-' }}</td>
                        </tr>
                        @if($booking->scan_type)
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_scan_type') }}</td>
                            <td>{{ $booking->scan_type }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_booking_date') }}</td>
                            <td>{{ $booking->booking_date ? $booking->booking_date->format('d M Y') : '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_booking_time') }}</td>
                            <td>{{ $booking->booking_time ?: '-' }}</td>
                        </tr>
                        @if($booking->notes)
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_notes') }}</td>
                            <td>{{ $booking->notes }}</td>
                        </tr>
                        @endif
                        @if($booking->admin_notes)
                        <tr>
                            <td class="text-muted">{{ __('radiology_lab.lbl_admin_notes') }}</td>
                            <td>{{ $booking->admin_notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Patient Info & Actions --}}
        <div class="col-lg-4">
            {{-- Patient Card --}}
            <div class="card mb-4" style="border-radius:12px; border:1px solid rgba(0,0,0,0.08);">
                <div class="card-header bg-transparent" style="border-bottom:1px solid rgba(0,0,0,0.06); padding:1rem 1.25rem;">
                    <h6 class="mb-0" style="color:#374151; font-weight:600;"><i class="ph ph-user me-1"></i> {{ __('radiology_lab.lbl_patient_info') }}</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $booking->patient_name ?: ($booking->patient ? $booking->patient->full_name : '-') }}</strong></p>
                    @if($booking->patient_phone)
                        <p class="mb-1 text-muted"><i class="ph ph-phone me-1"></i> {{ $booking->patient_phone }}</p>
                    @endif
                    @if($booking->patient && $booking->patient->email)
                        <p class="mb-0 text-muted"><i class="ph ph-envelope me-1"></i> {{ $booking->patient->email }}</p>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if(!$isCancelled && !$isNoShow && $booking->status !== 'completed')
            <div class="card mb-4" style="border-radius:12px; border:1px solid rgba(0,0,0,0.08);">
                <div class="card-header bg-transparent" style="border-bottom:1px solid rgba(0,0,0,0.06); padding:1rem 1.25rem;">
                    <h6 class="mb-0" style="color:#374151; font-weight:600;"><i class="ph ph-gear me-1"></i> {{ __('radiology_lab.lbl_actions') }}</h6>
                </div>
                <div class="card-body d-grid gap-2">
                    @if($booking->status === 'pending')
                        <button class="btn btn-info btn-sm booking-action-btn" data-url="{{ route('backend.facility-bookings.confirm', $booking->id) }}" data-status="confirmed">
                            <i class="ph ph-check-circle me-1"></i> {{ __('radiology_lab.lbl_confirm_order') }}
                        </button>
                    @endif

                    @if($booking->status === 'confirmed')
                        <button class="btn btn-success btn-sm booking-action-btn" data-url="{{ route('backend.facility-bookings.update_status', $booking->id) }}" data-status="completed">
                            <i class="ph ph-check me-1"></i> {{ __('radiology_lab.lbl_mark_complete') }}
                        </button>
                        <button class="btn btn-secondary btn-sm booking-action-btn" data-url="{{ route('backend.facility-bookings.update_status', $booking->id) }}" data-status="no_show">
                            <i class="ph ph-user-minus me-1"></i> {{ __('radiology_lab.status_no_show') }}
                        </button>
                    @endif

                    @if(in_array($booking->status, ['pending', 'confirmed']))
                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                            <i class="ph ph-x-circle me-1"></i> {{ __('radiology_lab.lbl_cancel_order') }}
                        </button>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Cancel Modal --}}
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius:12px;">
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('radiology_lab.lbl_confirm_cancel') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('radiology_lab.lbl_cancellation_reason') }}</label>
                        <textarea class="form-control" id="cancellation_reason" rows="3" placeholder="{{ __('radiology_lab.lbl_cancellation_reason_placeholder') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">{{ __('messages.close') }}</button>
                    <button type="button" class="btn btn-danger btn-sm" id="confirm-cancel-btn">{{ __('radiology_lab.lbl_confirm_cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-styles')
<style>
    .rl-detail-card { border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .rl-timeline { display: flex; align-items: center; gap: 0; padding: 0.75rem 0; overflow-x: auto; }
    .rl-timeline-step { display: flex; flex-direction: column; align-items: center; min-width: 80px; }
    .rl-timeline-dot { width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .rl-timeline-label { font-size: 0.72rem; margin-top: 6px; white-space: nowrap; color: #9ca3af; }
    .rl-timeline-line { flex: 1; height: 3px; min-width: 30px; border-radius: 2px; transition: background 0.2s; }
</style>
@endpush

@push('after-scripts')
<script>
    $(document).ready(function() {
        $('.booking-action-btn').on('click', function() {
            var url = $(this).data('url');
            var status = $(this).data('status');
            var btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', status: status },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        alert(response.message);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || '{{ __("messages.something_went_wrong") }}');
                    btn.prop('disabled', false);
                }
            });
        });

        $('#confirm-cancel-btn').on('click', function() {
            var reason = $('#cancellation_reason').val();
            var btn = $(this);
            btn.prop('disabled', true);

            $.ajax({
                url: '{{ route("backend.facility-bookings.cancel", $booking->id) }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', cancellation_reason: reason },
                success: function(response) {
                    if (response.status) {
                        location.reload();
                    } else {
                        alert(response.message);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || '{{ __("messages.something_went_wrong") }}');
                    btn.prop('disabled', false);
                }
            });
        });
    });
</script>
@endpush
