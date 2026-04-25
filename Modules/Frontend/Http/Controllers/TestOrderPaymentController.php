<?php

namespace Modules\Frontend\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RadiologyLab\Models\TestOrder;
use Modules\RadiologyLab\Models\TestOrderTransaction;
use Modules\Wallet\Models\Wallet;
use Modules\Wallet\Models\WalletHistory;
use Stripe\StripeClient;

class TestOrderPaymentController extends Controller
{
    public function showPayment($orderId)
    {
        $order = TestOrder::with(['items.labTest', 'patient'])
            ->where('patient_id', auth()->id())
            ->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            return redirect()->route('my-test-orders.detail', $order->id)
                ->withSuccess(__('radiology_lab.already_paid'));
        }

        $paymentMethodsList = [
            'cash' => 'cash_payment_method',
            'Wallet' => 'wallet_payment_method',
            'Stripe' => 'str_payment_method',
            'Razor' => 'razor_payment_method',
        ];

        $paymentMethods = [];
        foreach ($paymentMethodsList as $displayName => $settingKey) {
            if (setting($settingKey)) {
                $paymentMethods[] = $displayName;
            }
        }

        $wallet = Wallet::where('user_id', auth()->id())->first();
        $walletBalance = $wallet ? $wallet->amount : 0;

        $pageTitle = __('radiology_lab.lbl_payment');

        return view('frontend::order_test_payment', compact(
            'order', 'paymentMethods', 'walletBalance', 'pageTitle'
        ));
    }

    public function processPayment(Request $request, $orderId)
    {
        $request->validate([
            'transaction_type' => 'required|string|in:cash,Wallet,Stripe,Razor',
        ]);

        $order = TestOrder::where('patient_id', auth()->id())->findOrFail($orderId);

        if ($order->payment_status === 'paid') {
            return response()->json(['status' => false, 'message' => __('radiology_lab.already_paid')]);
        }

        $price = (float) $order->final_amount;
        $transactionType = $request->transaction_type;

        $paymentData = [
            'test_order_id' => $order->id,
            'total_amount' => $price,
            'discount_amount' => $order->discount_amount ?? 0,
            'currency' => GetcurrentCurrency(),
        ];

        return match ($transactionType) {
            'cash' => $this->cashPayment($paymentData, $order),
            'Wallet' => $this->walletPayment($paymentData, $order),
            'Stripe' => $this->stripePayment($paymentData, $order, $price),
            'Razor' => $this->razorpayPayment($paymentData, $order, $price),
            default => response()->json(['status' => false, 'message' => __('messages.something_wrong')]),
        };
    }

    protected function cashPayment(array $paymentData, TestOrder $order)
    {
        $this->saveTransaction($paymentData, 'cash', null, false);

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.order_save_success'),
            'redirect' => route('my-test-orders.detail', $order->id),
        ]);
    }

    protected function walletPayment(array $paymentData, TestOrder $order)
    {
        $wallet = Wallet::where('user_id', auth()->id())->first();
        $price = $paymentData['total_amount'];

        if (! $wallet || $wallet->amount < $price) {
            return response()->json([
                'status' => false,
                'message' => __('messages.wallet_balance_error'),
            ]);
        }

        $wallet->amount -= $price;
        $wallet->save();

        WalletHistory::create([
            'datetime' => now(),
            'user_id' => auth()->id(),
            'activity_type' => 'debit',
            'activity_message' => 'Payment for test order #' . $order->order_number,
            'activity_data' => json_encode([
                'test_order_id' => $order->id,
                'amount' => $price,
            ]),
        ]);

        $this->saveTransaction($paymentData, 'Wallet', null, true);

        $order->update(['payment_status' => 'paid']);

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.payment_success'),
            'redirect' => route('my-test-orders.detail', $order->id),
        ]);
    }

    protected function stripePayment(array $paymentData, TestOrder $order, float $price)
    {
        $stripeSecret = GetpaymentMethod('stripe_secretkey');
        $currency = GetcurrentCurrency();

        $stripe = new StripeClient($stripeSecret);
        $priceInCents = (int) round($price * 100);

        $checkoutSession = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => $currency,
                    'product_data' => [
                        'name' => __('radiology_lab.test_order_payment') . ' #' . $order->order_number,
                    ],
                    'unit_amount' => $priceInCents,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'metadata' => [
                'test_order_id' => $order->id,
                'user_id' => auth()->id(),
                'type' => 'test_order',
            ],
            'success_url' => route('test-order-payment.stripe-success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('test-order-payment.stripe-cancel', $order->id),
        ]);

        session(['test_order_stripe_data' => [
            'test_order_id' => $order->id,
            'total_amount' => $price,
            'discount_amount' => $paymentData['discount_amount'],
            'currency' => $currency,
        ]]);

        return response()->json([
            'status' => true,
            'payment_method' => 'stripe',
            'redirect' => $checkoutSession->url,
        ]);
    }

    protected function razorpayPayment(array $paymentData, TestOrder $order, float $price)
    {
        $razorpayKey = GetpaymentMethod('razorpay_publickey');
        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');
        $currency = strtoupper(GetcurrentCurrency());

        $priceInPaise = (int) round($price * 100);

        try {
            $api = new \Razorpay\Api\Api($razorpayKey, $razorpaySecret);
            $razorpayOrder = $api->order->create([
                'receipt' => 'test_order_' . $order->id . '_' . time(),
                'amount' => $priceInPaise,
                'currency' => $currency,
                'payment_capture' => 1,
                'notes' => [
                    'test_order_id' => $order->id,
                    'type' => 'test_order',
                ],
            ]);

            session(['test_order_razorpay_data' => [
                'razorpay_order_id' => $razorpayOrder['id'],
                'test_order_id' => $order->id,
                'amount' => $priceInPaise,
                'key' => $razorpayKey,
                'currency' => $currency,
                'total_amount' => $price,
                'discount_amount' => $paymentData['discount_amount'],
                'order_number' => $order->order_number,
            ]]);

            return response()->json([
                'status' => true,
                'payment_method' => 'razorpay',
                'redirect' => route('test-order-payment.razorpay-page', $order->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->session_id;
        $sessionData = session('test_order_stripe_data');

        if (! $sessionId || ! $sessionData) {
            return redirect()->route('my-test-orders')->withErrors(__('messages.something_wrong'));
        }

        $stripeSecret = GetpaymentMethod('stripe_secretkey');
        $stripe = new StripeClient($stripeSecret);

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $order = TestOrder::findOrFail($sessionData['test_order_id']);

                $this->saveTransaction([
                    'test_order_id' => $order->id,
                    'total_amount' => $sessionData['total_amount'],
                    'discount_amount' => $sessionData['discount_amount'],
                    'currency' => $sessionData['currency'],
                ], 'Stripe', $session->payment_intent, true);

                $order->update(['payment_status' => 'paid']);

                session()->forget('test_order_stripe_data');

                return redirect()->route('my-test-orders.detail', $order->id)
                    ->withSuccess(__('radiology_lab.payment_success'));
            }
        } catch (\Exception $e) {
            // Fall through to error
        }

        return redirect()->route('my-test-orders')->withErrors(__('messages.something_wrong'));
    }

    public function stripeCancel($orderId)
    {
        session()->forget('test_order_stripe_data');
        return redirect()->route('test-order-payment.show', $orderId)
            ->withErrors(__('radiology_lab.payment_cancelled'));
    }

    public function razorpayPage($orderId)
    {
        $data = session('test_order_razorpay_data');

        if (! $data || $data['test_order_id'] != $orderId) {
            return redirect()->route('my-test-orders')->withErrors(__('messages.something_wrong'));
        }

        $pageTitle = __('radiology_lab.lbl_payment');

        return view('frontend::order_test_razorpay', compact('data', 'pageTitle'));
    }

    public function razorpayVerify(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $sessionData = session('test_order_razorpay_data');
        if (! $sessionData) {
            return response()->json(['status' => false, 'message' => __('messages.something_wrong')]);
        }

        $razorpaySecret = GetpaymentMethod('razorpay_secretkey');

        $expectedSignature = hash_hmac('sha256', $request->razorpay_order_id . '|' . $request->razorpay_payment_id, $razorpaySecret);

        if ($expectedSignature !== $request->razorpay_signature) {
            return response()->json(['status' => false, 'message' => __('radiology_lab.payment_verification_failed')]);
        }

        $order = TestOrder::findOrFail($sessionData['test_order_id']);

        $this->saveTransaction([
            'test_order_id' => $order->id,
            'total_amount' => $sessionData['total_amount'],
            'discount_amount' => $sessionData['discount_amount'],
            'currency' => $sessionData['currency'],
        ], 'Razor', $request->razorpay_payment_id, true);

        $order->update(['payment_status' => 'paid']);

        session()->forget('test_order_razorpay_data');

        return response()->json([
            'status' => true,
            'message' => __('radiology_lab.payment_success'),
            'redirect' => route('my-test-orders.detail', $order->id),
        ]);
    }

    protected function saveTransaction(array $data, string $type, ?string $externalId, bool $paid): void
    {
        TestOrderTransaction::create([
            'test_order_id' => $data['test_order_id'],
            'transaction_type' => $type,
            'payment_method' => $type,
            'payment_gateway' => $type,
            'total_amount' => $data['total_amount'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'currency' => $data['currency'] ?? 'SAR',
            'external_transaction_id' => $externalId,
            'payment_status' => $paid ? 1 : 0,
            'paid_at' => $paid ? now() : null,
        ]);
    }
}
