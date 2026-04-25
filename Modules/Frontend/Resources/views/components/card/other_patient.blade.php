<div class="pp-patient-card">
    <div class="d-flex align-items-start gap-3">

        {{-- Avatar --}}
        <img src="{{ $patients->profile_image ?? asset('img/avatar/avatar.webp') }}"
             alt="{{ $patients->first_name }} {{ $patients->last_name }}"
             class="pp-patient-avatar">

        {{-- Info --}}
        <div class="flex-grow-1" style="min-width:0">
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <p class="pp-patient-name">{{ $patients->first_name }} {{ $patients->last_name }}</p>
                    @if($patients->relation)
                        <span class="pp-badge">{{ $patients->relation }}</span>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-1">
                    <button type="button"
                            class="pp-icon-btn editBtn"
                            data-id="{{ $patients->id }}"
                            title="{{ __('messages.edit') }}"
                            aria-label="{{ __('messages.edit') }}">
                        <i class="ph ph-pencil-simple" aria-hidden="true"></i>
                    </button>
                    <button type="button"
                            class="pp-icon-btn pp-icon-btn-danger deleteBtn"
                            data-id="{{ $patients->id }}"
                            title="{{ __('messages.delete') }}"
                            aria-label="{{ __('messages.delete') }}">
                        <i class="ph ph-trash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <div class="pp-patient-meta">
                @if($patients->gender)
                    <span><i class="ph ph-gender-intersex"></i>{{ ucfirst($patients->gender) }}</span>
                @endif
                @if($patients->contactNumber)
                    <span><i class="ph ph-phone"></i>{{ $patients->contactNumber }}</span>
                @endif
                @if($patients->dob)
                    <span><i class="ph ph-cake"></i>{{ $patients->dob }}</span>
                @endif
            </div>
        </div>

    </div>
</div>
