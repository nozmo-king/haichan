<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Payment;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (SignatureVerificationException $e) {
            Log::error('Invalid Stripe webhook signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        switch ($event['type']) {
            case 'checkout.session.completed':
                $this->handleCheckoutSessionCompleted($event['data']['object']);
                break;
                
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event['data']['object']);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event['data']['object']);
                break;
                
            default:
                Log::info('Received unknown Stripe event type', ['type' => $event['type']]);
        }

        return response('', 200);
    }

    private function handleCheckoutSessionCompleted($session)
    {
        $paymentId = $session['metadata']['payment_id'] ?? null;
        
        if (!$paymentId) {
            Log::error('No payment_id in checkout session metadata', ['session_id' => $session['id']]);
            return;
        }

        $payment = Payment::find($paymentId);
        
        if (!$payment) {
            Log::error('Payment not found for checkout session', ['payment_id' => $paymentId]);
            return;
        }

        if ($session['payment_status'] === 'paid') {
            $payment->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'stripe_payment_intent_id' => $session['payment_intent'],
            ]);

            if ($payment->subscription) {
                $payment->subscription->update(['status' => 'active']);
            }

            Log::info('Payment confirmed via webhook', ['payment_id' => $payment->id]);
        }
    }

    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        
        if (!$payment) {
            Log::warning('Payment not found for payment intent', ['payment_intent_id' => $paymentIntent['id']]);
            return;
        }

        if ($payment->status !== 'confirmed') {
            $payment->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            if ($payment->subscription) {
                $payment->subscription->update(['status' => 'active']);
            }

            Log::info('Payment confirmed via payment intent webhook', ['payment_id' => $payment->id]);
        }
    }

    private function handlePaymentIntentFailed($paymentIntent)
    {
        $payment = Payment::where('stripe_payment_intent_id', $paymentIntent['id'])->first();
        
        if (!$payment) {
            Log::warning('Payment not found for failed payment intent', ['payment_intent_id' => $paymentIntent['id']]);
            return;
        }

        $payment->update(['status' => 'failed']);

        if ($payment->subscription) {
            $payment->subscription->update(['status' => 'cancelled']);
        }

        Log::info('Payment failed via webhook', ['payment_id' => $payment->id]);
    }
}