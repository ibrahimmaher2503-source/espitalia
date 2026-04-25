@extends('frontend::layouts.master')
@section('title', __('radiology_lab.lbl_payment'))

@section('content')
    <div class="pp-page list-page section-spacing px-0">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center py-5">
                    <i class="ph ph-credit-card" style="font-size:3rem;color:var(--pp-teal,#0d9488)"></i>
                    <h5 class="mt-3">{{ __('radiology_lab.lbl_processing_payment') }}</h5>
                    <p class="text-muted">{{ __('radiology_lab.lbl_razorpay_redirect') }}</p>
                    <div id="razorpay-error" style="display:none;color:var(--pp-danger,#ef4444);margin-top:1rem"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('after-scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var data = @json($data);

    var options = {
        key: data.key,
        amount: data.amount,
        currency: data.currency,
        name: '{{ config("app.name") }}',
        description: '{{ __("radiology_lab.test_order_payment") }} #' + data.order_number,
        order_id: data.razorpay_order_id,
        handler: function (response) {
            fetch('{{ route("test-order-payment.razorpay-verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.status && res.redirect) {
                    window.location.href = res.redirect;
                } else {
                    document.getElementById('razorpay-error').textContent = res.message || '{{ __("messages.something_wrong") }}';
                    document.getElementById('razorpay-error').style.display = 'block';
                }
            });
        },
        modal: {
            ondismiss: function () {
                window.location.href = '{{ route("test-order-payment.show", $data["test_order_id"]) }}';
            }
        },
        prefill: {
            name: '{{ auth()->user()->first_name ?? "" }} {{ auth()->user()->last_name ?? "" }}',
            email: '{{ auth()->user()->email ?? "" }}'
        },
        theme: {
            color: '#0d9488'
        }
    };

    var rzp = new Razorpay(options);
    rzp.open();
}());
</script>
@endpush
