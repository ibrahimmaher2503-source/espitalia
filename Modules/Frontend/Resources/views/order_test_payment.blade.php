@extends('frontend::layouts.master')
@section('title', __('radiology_lab.lbl_payment'))

@section('content')
    @include('frontend::components.section.breadcrumb')

    <div class="pp-page list-page section-spacing px-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="pp-detail-section mb-4">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-receipt" aria-hidden="true"></i>
                            <span>{{ __('radiology_lab.lbl_order_summary') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            <div class="mb-3">
                                <strong>{{ __('radiology_lab.lbl_order_number') }}:</strong> {{ $order->order_number }}
                            </div>

                            <table style="width:100%;font-size:0.85rem;border-collapse:collapse">
                                <thead>
                                    <tr style="border-bottom:2px solid var(--pp-border)">
                                        <th style="text-align:left;padding:0.5rem 0">{{ __('radiology_lab.lbl_test_name') }}</th>
                                        <th style="text-align:right;padding:0.5rem 0">{{ __('radiology_lab.lbl_price') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr style="border-bottom:1px solid var(--pp-border)">
                                            <td style="padding:0.4rem 0">{{ $item->labTest->name ?? 'N/A' }}</td>
                                            <td style="text-align:right;padding:0.4rem 0">{{ number_format($item->price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div style="margin-top:0.75rem;font-size:0.85rem">
                                <div style="display:flex;justify-content:space-between;padding:0.3rem 0">
                                    <span>{{ __('radiology_lab.lbl_subtotal') }}</span>
                                    <span>{{ number_format($order->total_amount, 2) }}</span>
                                </div>
                                @if($order->discount_amount > 0)
                                    <div style="display:flex;justify-content:space-between;padding:0.3rem 0;color:var(--pp-success,#10b981)">
                                        <span>{{ __('radiology_lab.lbl_discount') }}</span>
                                        <span>-{{ number_format($order->discount_amount, 2) }}</span>
                                    </div>
                                @endif
                                <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-top:2px solid var(--pp-border);font-weight:700;font-size:1rem">
                                    <span>{{ __('radiology_lab.lbl_total_amount') }}</span>
                                    <span>{{ number_format($order->final_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pp-detail-section">
                        <div class="pp-detail-section-header">
                            <i class="ph ph-credit-card" aria-hidden="true"></i>
                            <span>{{ __('radiology_lab.lbl_select_payment_method') }}</span>
                        </div>
                        <div class="pp-detail-section-body">
                            @if(count($paymentMethods) === 0)
                                <p class="text-muted">{{ __('radiology_lab.no_payment_methods') }}</p>
                            @else
                                <div id="payment-methods">
                                    @foreach($paymentMethods as $method)
                                        <label style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;border:1px solid var(--pp-border);border-radius:0.5rem;margin-bottom:0.5rem;cursor:pointer;transition:border-color 0.2s"
                                               class="payment-method-label">
                                            <input type="radio" name="transaction_type" value="{{ $method }}" style="accent-color:var(--pp-teal,#0d9488)">
                                            <span style="display:flex;align-items:center;gap:0.5rem">
                                                @if($method === 'cash')
                                                    <i class="ph ph-money" style="font-size:1.2rem"></i> {{ __('radiology_lab.payment_cash') }}
                                                @elseif($method === 'Wallet')
                                                    <i class="ph ph-wallet" style="font-size:1.2rem"></i>
                                                    {{ __('radiology_lab.payment_wallet') }}
                                                    <small style="color:var(--pp-muted)">({{ __('radiology_lab.lbl_balance') }}: {{ number_format($walletBalance, 2) }})</small>
                                                @elseif($method === 'Stripe')
                                                    <i class="ph ph-credit-card" style="font-size:1.2rem"></i> {{ __('radiology_lab.payment_stripe') }}
                                                @elseif($method === 'Razor')
                                                    <i class="ph ph-credit-card" style="font-size:1.2rem"></i> {{ __('radiology_lab.payment_razorpay') }}
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                <div id="payment-error" style="display:none;color:var(--pp-danger,#ef4444);font-size:0.82rem;margin-top:0.5rem"></div>

                                <button type="button" id="pay-now-btn" class="pp-btn pp-btn-teal w-100 mt-3" disabled>
                                    <i class="ph ph-lock-simple" aria-hidden="true"></i>
                                    {{ __('radiology_lab.lbl_pay_now') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script>
(function () {
    var radios = document.querySelectorAll('input[name="transaction_type"]');
    var payBtn = document.getElementById('pay-now-btn');
    var errorEl = document.getElementById('payment-error');

    radios.forEach(function (r) {
        r.addEventListener('change', function () {
            payBtn.disabled = false;
            document.querySelectorAll('.payment-method-label').forEach(function (l) {
                l.style.borderColor = 'var(--pp-border)';
            });
            r.closest('.payment-method-label').style.borderColor = 'var(--pp-teal,#0d9488)';
        });
    });

    if (payBtn) {
        payBtn.addEventListener('click', function () {
            var selected = document.querySelector('input[name="transaction_type"]:checked');
            if (!selected) return;

            payBtn.disabled = true;
            payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> {{ __("radiology_lab.lbl_processing") }}';
            errorEl.style.display = 'none';

            fetch('{{ route("test-order-payment.process", $order->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ transaction_type: selected.value })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.redirect) {
                    window.location.href = res.redirect;
                } else if (!res.status) {
                    errorEl.textContent = res.message;
                    errorEl.style.display = 'block';
                    payBtn.disabled = false;
                    payBtn.innerHTML = '<i class="ph ph-lock-simple"></i> {{ __("radiology_lab.lbl_pay_now") }}';
                }
            })
            .catch(function () {
                errorEl.textContent = '{{ __("messages.something_wrong") }}';
                errorEl.style.display = 'block';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="ph ph-lock-simple"></i> {{ __("radiology_lab.lbl_pay_now") }}';
            });
        });
    }
}());
</script>
@endpush
