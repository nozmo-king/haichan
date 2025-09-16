@extends('layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment Details</h1>
            <p class="text-gray-600">
                @if($payment->payment_gateway === 'stripe')
                    Your payment is being processed through Stripe
                @else
                    Send the exact amount to the wallet address below to activate your subscription
                @endif
            </p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Payment Status -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">Payment Status</h2>
                <span class="inline-flex px-3 py-1 text-sm font-medium rounded-full
                    @if($payment->status === 'confirmed') bg-green-100 text-green-800
                    @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($payment->status === 'failed') bg-red-100 text-red-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($payment->status) }}
                </span>
            </div>

            @if ($payment->status === 'pending')
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="font-medium text-yellow-800">Payment Required</h3>
                            <p class="text-sm text-yellow-700 mt-1">
                                Send the exact amount to the wallet address below. Your subscription will be activated once the payment is confirmed.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="bg-red-50 border border-red-200 rounded p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="font-medium text-red-800">Payment Expires In:</h3>
                            <p class="text-lg font-mono text-red-700" id="countdown">
                                {{ $payment->expires_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif ($payment->status === 'confirmed')
                <div class="bg-green-50 border border-green-200 rounded p-4 mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <div>
                            <h3 class="font-medium text-green-800">Payment Confirmed!</h3>
                            <p class="text-sm text-green-700 mt-1">
                                Your payment has been confirmed and your subscription is now active.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Payment Details -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Payment Details</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subscription Plan:</span>
                    <span class="font-medium">{{ $payment->subscription->subscriptionPlan->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Amount (USD):</span>
                    <span class="font-medium">${{ number_format($payment->amount_usd, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Payment Method:</span>
                    <span class="font-medium">
                        @if($payment->payment_gateway === 'stripe')
                            Credit Card (Stripe)
                        @else
                            {{ $payment->cryptocurrency }}
                        @endif
                    </span>
                </div>
                @if($payment->payment_gateway !== 'stripe' && $payment->cryptocurrency)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Amount to Send:</span>
                        <span class="font-bold text-lg">{{ number_format($payment->crypto_amount, 8) }} {{ $payment->cryptocurrency }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if ($payment->status === 'pending' && $payment->payment_gateway !== 'stripe')
            <!-- Wallet Address -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Send Payment To:</h2>
                
                <div class="bg-gray-50 rounded p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Wallet Address:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               id="walletAddress" 
                               value="{{ $payment->wallet_address }}" 
                               readonly 
                               class="flex-1 px-3 py-2 bg-white border rounded font-mono text-sm">
                        <button onclick="copyAddress()" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                            Copy
                        </button>
                    </div>
                </div>

                <div class="bg-gray-50 rounded p-4 mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount to Send:</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" 
                               id="cryptoAmount" 
                               value="{{ number_format($payment->crypto_amount, 8) }}" 
                               readonly 
                               class="flex-1 px-3 py-2 bg-white border rounded font-mono text-lg font-bold">
                        <span class="text-lg font-bold">{{ $payment->cryptocurrency }}</span>
                        <button onclick="copyAmount()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                            Copy
                        </button>
                    </div>
                </div>

                <div class="bg-red-50 border border-red-200 rounded p-4">
                    <h3 class="font-medium text-red-800 mb-2">Important:</h3>
                    <ul class="text-sm text-red-700 space-y-1">
                        <li>• Send the <strong>exact amount</strong> shown above</li>
                        <li>• Send to the <strong>exact wallet address</strong> (double-check!)</li>
                        <li>• Use the <strong>{{ $payment->cryptocurrency }} network</strong> only</li>
                        <li>• Payment expires in {{ $payment->expires_at->diffForHumans() }}</li>
                    </ul>
                </div>
            </div>

            <!-- Confirm Payment -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Confirm Payment</h2>
                
                <p class="text-gray-600 mb-4">
                    After sending the payment, enter your transaction hash below to confirm the payment.
                </p>

                <form action="{{ route('payment.confirm', $payment) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="transaction_hash" class="block text-sm font-medium text-gray-700 mb-2">
                            Transaction Hash *
                        </label>
                        <input type="text" 
                               id="transaction_hash" 
                               name="transaction_hash" 
                               placeholder="Enter the transaction hash from your wallet"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-sm text-gray-500">
                            Copy the transaction hash from your cryptocurrency wallet after sending the payment.
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Confirm Payment
                    </button>
                </form>
            </div>
        @elseif ($payment->status === 'pending' && $payment->payment_gateway === 'stripe')
            <!-- Stripe Payment Pending -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-4">Processing Payment</h2>
                
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                    <p class="text-gray-600 mb-4">
                        Your payment is being processed. This page will automatically update when your payment is confirmed.
                    </p>
                    <p class="text-sm text-gray-500">
                        If you completed the payment but this page hasn't updated, please refresh the page.
                    </p>
                    <div class="mt-6">
                        <button onclick="window.location.reload()" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-indigo-700">
                            Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Payment Confirmed -->
            <div class="bg-white rounded-lg shadow-lg p-6 text-center">
                @if ($payment->transaction_hash || $payment->stripe_payment_intent_id)
                    <div class="mb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">
                            @if($payment->payment_gateway === 'stripe')
                                Payment Intent ID:
                            @else
                                Transaction Hash:
                            @endif
                        </h3>
                        <code class="bg-gray-100 px-3 py-2 rounded text-sm font-mono break-all">
                            {{ $payment->stripe_payment_intent_id ?? $payment->transaction_hash }}
                        </code>
                    </div>
                @endif

                <div class="space-y-4">
                    <a href="{{ route('subscription.dashboard') }}" class="inline-block bg-indigo-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-indigo-700">
                        Go to Dashboard
                    </a>
                    <br>
                    <a href="{{ route('forum.index') }}" class="inline-block bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700">
                        Access Forum
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function copyAddress() {
    const address = document.getElementById('walletAddress').value;
    navigator.clipboard.writeText(address).then(function() {
        showNotification('Wallet address copied to clipboard!', 'success');
    }, function(err) {
        showNotification('Failed to copy address', 'error');
    });
}

function copyAmount() {
    const amount = document.getElementById('cryptoAmount').value;
    navigator.clipboard.writeText(amount).then(function() {
        showNotification('Amount copied to clipboard!', 'success');
    }, function(err) {
        showNotification('Failed to copy amount', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-4 py-3 rounded shadow-lg z-50 ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
        'bg-red-100 border border-red-400 text-red-700'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Update countdown timer
@if ($payment->status === 'pending')
function updateCountdown() {
    const expiresAt = new Date('{{ $payment->expires_at->toISOString() }}');
    const now = new Date();
    const timeDiff = expiresAt - now;
    
    if (timeDiff <= 0) {
        document.getElementById('countdown').textContent = 'EXPIRED';
        document.getElementById('countdown').className += ' text-red-900 font-bold';
        return;
    }
    
    const hours = Math.floor(timeDiff / (1000 * 60 * 60));
    const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
    
    document.getElementById('countdown').textContent = 
        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

setInterval(updateCountdown, 1000);
updateCountdown();
@endif
</script>
@endsection