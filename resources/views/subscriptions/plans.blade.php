@extends('layout')

@section('content')
<div class="breadcrumb">
    <a href="{{ route('forum.index') }}">Forum</a> > Subscription Plans
</div>

<div style="text-align: center; margin-bottom: 30px;">
    <h2>Choose Your Subscription Plan</h2>
    <p style="color: #666; font-size: 16px;">Select a plan to get access to our exclusive forum community</p>
</div>

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

@if (session('info'))
    <div style="background-color: #cce7ff; border: 1px solid #9fc3ff; color: #0056b3; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
        <div style="display: flex; align-items: flex-start;">
            <div style="margin-right: 10px; margin-top: 2px;">ℹ️</div>
            <div>
                <p>{{ session('info') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('registered_public_key'))
    <div style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
        <div style="display: flex; align-items: flex-start;">
            <div style="margin-right: 10px; margin-top: 2px;">✅</div>
            <div>
                <h3 style="font-weight: bold; margin-bottom: 8px;">Welcome to the community!</h3>
                <p style="font-size: 14px; margin-bottom: 8px;">
                    Your account has been created and you're now logged in. 
                    <strong>Choose a subscription plan below to access the exclusive forum.</strong>
                </p>
                <p style="font-size: 12px; font-family: 'Courier New', monospace; background-color: #f0f8f0; padding: 8px; border-radius: 3px; border: 1px solid #c3e6cb;">
                    Your public key: {{ substr(session('registered_public_key'), 0, 12) }}...{{ substr(session('registered_public_key'), -12) }}
                </p>
            </div>
        </div>
    </div>
@endif

@if ($currentSubscription)
    <div style="background-color: #cce7ff; border: 1px solid #9fc3ff; color: #0056b3; padding: 15px; margin-bottom: 20px; border-radius: 3px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Current Plan:</strong> {{ $currentSubscription->subscriptionPlan->name }}
                <br>
                <strong>Expires:</strong> {{ $currentSubscription->expires_at->format('M j, Y g:i A') }}
            </div>
            <a href="{{ route('subscription.dashboard') }}" style="background-color: #789922; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">
                Manage Subscription
            </a>
        </div>
    </div>
@endif

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
    @foreach ($plans as $plan)
        <div style="background-color: #f9f9f9; border: 1px solid #ccc; padding: 20px; {{ $plan->name === 'Yearly' ? 'border: 2px solid #789922; position: relative;' : '' }}">
            @if ($plan->name === 'Yearly')
                <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background-color: #789922; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold;">
                    Most Popular
                </div>
            @endif
            
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 15px; color: #333;">{{ $plan->name }}</h3>
                <div style="margin-bottom: 20px;">
                    <span style="font-size: 36px; font-weight: bold; color: #333;">${{ number_format($plan->price_usd, 2) }}</span>
                    @if ($plan->duration_months < 999)
                        <span style="color: #666; font-size: 14px;">/ {{ $plan->duration_months }} month{{ $plan->duration_months > 1 ? 's' : '' }}</span>
                    @endif
                </div>
                
                @if ($plan->name === 'Yearly')
                    <div style="color: #28a745; font-weight: bold; margin-bottom: 15px;">
                        Save ${{ number_format((9.99 * 12) - $plan->price_usd, 2) }} per year!
                    </div>
                @endif
            </div>

            <ul style="margin-bottom: 25px; line-height: 1.6;">
                @foreach ($plan->features as $feature)
                    <li style="display: flex; align-items: center; margin-bottom: 8px;">
                        <span style="color: #28a745; margin-right: 10px; font-weight: bold;">✓</span>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>

            @auth
                @if ($currentSubscription && $currentSubscription->isActive())
                    <button disabled style="width: 100%; background-color: #ccc; color: #666; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: not-allowed;">
                        Current Plan
                    </button>
                @else
                    <form action="{{ route('subscription.subscribe', $plan) }}" method="POST" class="subscribe-form">
                        @csrf
                        <button type="submit" style="width: 100%; background-color: #789922; color: white; padding: 12px; border: none; border-radius: 3px; font-weight: bold; cursor: pointer; font-family: 'Courier New', monospace;">
                            Subscribe with Stripe
                        </button>
                    </form>
                @endif
            @else
                <div style="text-align: center;">
                    <p style="color: #666; margin-bottom: 10px;">Need an account?</p>
                    <p style="font-size: 12px; color: #888;">You'll need a friend code to register</p>
                </div>
            @endauth
        </div>
    @endforeach
</div>

@auth
    <div style="margin-top: 40px; text-align: center;">
        <p style="color: #666;">
            Questions about pricing? 
            <a href="#" style="color: #34345c; text-decoration: underline;">Contact support</a>
        </p>
    </div>
@endauth
@endsection