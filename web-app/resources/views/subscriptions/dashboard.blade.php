@extends('layout')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > Subscription Dashboard
</div>

<h2>Subscription Dashboard</h2>
<p style="margin-bottom: 30px; color: #666;">Manage your subscription and payment history</p>

@if (session('success'))
    <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
        {{ session('error') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
    <!-- Current Subscription -->
    <div style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333;">Current Subscription</h3>
            
        @if ($currentSubscription)
            <div style="line-height: 1.6;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #666;">Plan:</span>
                    <span style="font-weight: bold;">{{ $currentSubscription->subscriptionPlan->name }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #666;">Status:</span>
                    <span style="padding: 3px 8px; font-size: 12px; font-weight: bold; border-radius: 3px;
                        @if($currentSubscription->status === 'active') background-color: #d4edda; color: #155724;
                        @elseif($currentSubscription->status === 'pending') background-color: #fff3cd; color: #856404;
                        @elseif($currentSubscription->status === 'expired') background-color: #f8d7da; color: #721c24;
                        @else background-color: #e2e3e5; color: #383d41; @endif">
                        {{ ucfirst($currentSubscription->status) }}
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #666;">Started:</span>
                    <span style="font-weight: bold;">{{ $currentSubscription->starts_at->format('M j, Y') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #666;">
                        @if($currentSubscription->subscriptionPlan->duration_months >= 999)
                            Lifetime Access:
                        @else
                            Expires:
                        @endif
                    </span>
                    <span style="font-weight: bold; {{ $currentSubscription->isExpired() ? 'color: #dd0000;' : '' }}">
                        @if($currentSubscription->subscriptionPlan->duration_months >= 999)
                            Never
                        @else
                            {{ $currentSubscription->expires_at->format('M j, Y g:i A') }}
                        @endif
                    </span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <span style="color: #666;">Auto Renew:</span>
                    <span style="font-weight: bold;">{{ $currentSubscription->auto_renew ? 'Yes' : 'No' }}</span>
                </div>
            </div>

            @if ($currentSubscription->status === 'active' && $currentSubscription->subscriptionPlan->duration_months < 999)
                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <form action="{{ route('subscription.cancel', $currentSubscription) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
                        @csrf
                        <button type="submit" style="color: #dd0000; background: none; border: none; font-weight: bold; cursor: pointer; text-decoration: underline;">
                            Cancel Subscription
                        </button>
                    </form>
                </div>
            @endif
        @else
            <div style="text-align: center; padding: 30px 0;">
                <p style="color: #666; margin-bottom: 15px;">No active subscription</p>
                <a href="{{ route('subscription.plans') }}" style="background-color: #789922; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">
                    Browse Plans
                </a>
            </div>
        @endif
        </div>

    <!-- Quick Actions -->
    <div style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 20px;">
        <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333;">Quick Actions</h3>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('subscription.plans') }}" style="display: block; width: 100%; background-color: #789922; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 3px;">
                View All Plans
            </a>
            
            <a href="{{ route('friend-codes.index') }}" style="display: block; width: 100%; background-color: #28a745; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 3px;">
                Manage Friend Codes
            </a>
            
            <a href="{{ route('forum.index') }}" style="display: block; width: 100%; background-color: #333; color: white; text-align: center; padding: 10px; text-decoration: none; border-radius: 3px;">
                Go to Forum
            </a>
        </div>
    </div>
</div>

<!-- Subscription History -->
<div style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 20px; margin-bottom: 30px;">
    <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333;">Subscription History</h3>
    
    @if ($subscriptionHistory->count() > 0)
        <table class="thread-list">
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subscriptionHistory as $subscription)
                    <tr>
                        <td style="font-weight: bold;">
                            {{ $subscription->subscriptionPlan->name }}
                        </td>
                        <td>
                            <span style="padding: 3px 8px; font-size: 12px; font-weight: bold; border-radius: 3px;
                                @if($subscription->status === 'active') background-color: #d4edda; color: #155724;
                                @elseif($subscription->status === 'pending') background-color: #fff3cd; color: #856404;
                                @elseif($subscription->status === 'expired') background-color: #f8d7da; color: #721c24;
                                @else background-color: #e2e3e5; color: #383d41; @endif">
                                {{ ucfirst($subscription->status) }}
                            </span>
                        </td>
                        <td style="font-size: 14px;">
                            {{ $subscription->starts_at->format('M j, Y') }} - 
                            @if($subscription->subscriptionPlan->duration_months >= 999)
                                Lifetime
                            @else
                                {{ $subscription->expires_at->format('M j, Y') }}
                            @endif
                        </td>
                        <td style="font-size: 12px; color: #666;">
                            {{ $subscription->created_at->format('M j, Y g:i A') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            {{ $subscriptionHistory->links() }}
        </div>
    @else
        <p style="color: #666; text-align: center; padding: 20px;">No subscription history found.</p>
    @endif
</div>

<!-- Payment History -->
<div style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 20px;">
    <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 15px; color: #333;">Payment History</h3>
    
    @if ($paymentHistory->count() > 0)
        <table class="thread-list">
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Cryptocurrency</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($paymentHistory as $payment)
                    <tr>
                        <td style="font-weight: bold;">
                            ${{ number_format($payment->amount_usd, 2) }}
                        </td>
                        <td style="font-family: 'Courier New', monospace; font-size: 12px;">
                            {{ $payment->cryptocurrency }} {{ number_format($payment->crypto_amount, 8) }}
                        </td>
                        <td>
                            <span style="padding: 3px 8px; font-size: 12px; font-weight: bold; border-radius: 3px;
                                @if($payment->status === 'confirmed') background-color: #d4edda; color: #155724;
                                @elseif($payment->status === 'pending') background-color: #fff3cd; color: #856404;
                                @elseif($payment->status === 'failed') background-color: #f8d7da; color: #721c24;
                                @else background-color: #e2e3e5; color: #383d41; @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td style="font-size: 12px; color: #666;">
                            {{ $payment->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td style="font-size: 14px;">
                            @if ($payment->status === 'pending')
                                <a href="{{ route('payment.show', $payment) }}" style="color: #34345c; text-decoration: underline;">
                                    Complete Payment
                                </a>
                            @elseif ($payment->transaction_hash)
                                <span style="color: #28a745;">
                                    Confirmed
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            {{ $paymentHistory->links() }}
        </div>
    @else
        <p style="color: #666; text-align: center; padding: 20px;">No payment history found.</p>
    @endif
</div>
@endsection