<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function show(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('payments.show', compact('payment'));
    }

    public function confirm(Request $request, Payment $payment)
    {
        $request->validate([
            'transaction_hash' => 'required|string|min:32',
        ]);

        if ($payment->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($payment->status !== 'pending') {
            return redirect()->back()->with('error', 'Payment is not in pending state.');
        }

        $payment->update([
            'transaction_hash' => $request->transaction_hash,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        if ($payment->subscription) {
            $payment->subscription->update(['status' => 'active']);
        }

        return redirect()->route('subscription.dashboard')
            ->with('success', 'Payment confirmed! Your subscription is now active.');
    }

    public function status(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json([
            'status' => $payment->status,
            'confirmed_at' => $payment->confirmed_at?->toISOString(),
            'expires_at' => $payment->expires_at->toISOString(),
            'is_expired' => $payment->isExpired(),
        ]);
    }

    public function success(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            abort(403);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $session = $stripe->checkout->sessions->retrieve($payment->stripe_checkout_session_id);

            if ($session->payment_status === 'paid') {
                $payment->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'stripe_payment_intent_id' => $session->payment_intent,
                ]);

                if ($payment->subscription) {
                    $payment->subscription->update(['status' => 'active']);
                }

                return redirect()->route('subscription.dashboard')
                    ->with('success', 'Payment successful! Your subscription is now active.');
            }

            return redirect()->route('subscription.plans')
                ->with('error', 'Payment was not completed successfully.');

        } catch (ApiErrorException $e) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, Payment $payment)
    {
        if ($payment->user_id !== $request->user()->id) {
            abort(403);
        }

        $payment->update(['status' => 'failed']);

        if ($payment->subscription) {
            $payment->subscription->update(['status' => 'cancelled']);
        }

        return redirect()->route('subscription.plans')
            ->with('error', 'Payment was cancelled. You can try again anytime.');
    }
}
