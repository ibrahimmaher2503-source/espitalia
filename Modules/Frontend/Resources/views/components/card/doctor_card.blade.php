@props(['doctor'])

@php
    $rating = $doctor->average_rating ?? 0;
    $ratingInt = (int) round($rating);
@endphp

<a href="{{ route('doctor-details', ['id' => $doctor->id]) }}" class="pp-doctor-card">
    <img src="{{ optional($doctor->user)->profile_image }}"
         alt="{{ getDisplayName($doctor->user) }}"
         class="pp-doctor-avatar">

    <div style="width:100%">
        <h5 class="pp-doctor-name">{{ getDisplayName($doctor->user) }}</h5>
        <p class="pp-doctor-specialty">{{ optional(optional($doctor->user)->profile)->expert ?? '' }}</p>
    </div>

    <div class="pp-stars" aria-label="{{ $rating }} {{ __('frontend.stars') }}">
        @for ($i = 1; $i <= 5; $i++)
            <i class="{{ $i <= $ratingInt ? 'ph-fill ph-star' : 'ph ph-star inactive' }}"
               aria-hidden="true"></i>
        @endfor
        <small style="font-size:0.72rem;font-weight:700;color:var(--pp-checkout);margin-left:4px;font-family:var(--pp-font)">
            {{ number_format($rating, 1) }}
        </small>
    </div>

    <div class="pp-doctor-stats">
        <span>
            <i class="ph ph-users" aria-hidden="true"></i>
            {{ $doctor->total_appointment }}
            {{ __('frontend.patient_served') }}
        </span>
    </div>
</a>
