@php
    $statusColors = [
        'pending'   => 'warning',
        'confirmed' => 'primary',
        'cancelled' => 'danger',
        'completed' => 'success',
    ];
    $color = $statusColors[$row->status] ?? 'secondary';
    $label = __('doctor_visit.status_' . $row->status);
@endphp

<span class="badge bg-{{ $color }}">{{ $label }}</span>
