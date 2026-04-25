@php
    $activityData = json_decode($history['activity_data'], true);
    $amount  = $activityData['credit_debit_amount'] ?? null;
    $apptId  = $activityData['appointment_id'] ?? null;
    $isDebit = in_array($history->activity_type, ['paid_for_appointment', 'wallet_deduction']);
@endphp

<li class="pp-wallet-item">
    <div class="pp-wallet-card">

        {{-- Icon --}}
        <div class="pp-wallet-icon {{ $isDebit ? 'pp-wallet-icon-debit' : 'pp-wallet-icon-credit' }}"
             aria-hidden="true">
            <i class="ph {{ $isDebit ? 'ph-arrow-up-right' : 'ph-arrow-down-left' }}"></i>
        </div>

        {{-- Body --}}
        <div class="pp-wallet-body">
            <p class="pp-wallet-type">
                {{ __(key: 'frontend.' . strtolower($history->activity_type)) }}
                @if($apptId)
                    <span class="pp-wallet-ref">#{{ $apptId }}</span>
                @endif
            </p>
            <p class="pp-wallet-date">{{ formatDate($history->datetime) }}</p>
        </div>

        {{-- Amount --}}
        <span class="pp-wallet-amount {{ $isDebit ? 'pp-wallet-debit' : 'pp-wallet-credit' }}"
              aria-label="{{ $isDebit ? __('frontend.debit') : __('frontend.credit') }} {{ Currency::format($amount, 2) }}">
            {{ $isDebit ? '−' : '+' }}{{ Currency::format($amount, 2) }}
        </span>

    </div>
</li>
