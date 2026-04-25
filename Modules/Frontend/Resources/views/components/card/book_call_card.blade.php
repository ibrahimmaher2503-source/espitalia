<div class="doctor-card rounded text-center d-block">
    <div class="doctor-inner-card d-block">
        <div class="doctor-img d-inline-block">
            <a href="{{ route('doctor-details', ['id' => $doctor->id]) }}">
                <img src="{{ optional($doctor->user)->profile_image }}" alt="image" class="img-fluid rounded-circle object-fit-cover">
            </a>
        </div>
        <div class="doctor-content mb-3 pb-1">
            <div class="d-flex align-items-center justify-content-center gap-2 mt-4 mb-1 doctor-name">
                <h5 class="text-capitalize m-0">
                    <a href="{{ route('doctor-details', ['id' => $doctor->id]) }}">{{ getDisplayName($doctor->user) }}</a>
                </h5>
                <span class="doctor-available"></span>
            </div>
            <p class="font-size-14 mb-0 fw-medium">{{ optional(optional($doctor->user)->profile)->expert ?? '' }}</p>

            <div class="d-flex align-items-center justify-content-center gap-1 mt-2">
                @if(($doctor->call_services_count ?? 0) > 0)
                    <span class="badge bg-info"><i class="ph ph-video-camera"></i></span>
                @endif
                @if(($doctor->phone_services_count ?? 0) > 0)
                    <span class="badge bg-success"><i class="ph ph-phone"></i></span>
                @endif
            </div>

            @if(isset($doctor->call_services_count) && $doctor->call_services_count > 0)
                <p class="font-size-12 text-muted mt-1 mb-0">
                    {{ $doctor->call_services_count }} {{ __('call_booking.call_services') }}
                    @if(isset($doctor->call_starting_price))
                        &middot; {{ __('call_booking.from') }} {{ number_format($doctor->call_starting_price, 2) }}
                    @endif
                </p>
            @endif
        </div>
        @php
            $rating = $doctor->average_rating ?? 0;
        @endphp

        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
            <div class="d-flex align-items-center">
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= $rating)
                        <i class="ph-fill ph-star text-warning"></i>
                    @else
                        <i class="ph ph-star text-warning"></i>
                    @endif
                @endfor
            </div>
            <small class="doctor-rate m-0 fw-bold">{{ $rating }}</small>
        </div>

        <span class="text-primary font-size-14 fw-semibold d-block mb-3">{{ $doctor->total_appointment }} {{ __('frontend.patient_served') }}</span>

        <a href="{{ route('book-call.form', $doctor->id) }}" class="pp-btn pp-btn-teal" style="justify-content:center">
            <i class="ph ph-video-camera" aria-hidden="true"></i>{{ __('call_booking.book_call') }}
        </a>
    </div>
</div>
