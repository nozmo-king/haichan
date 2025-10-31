@extends('layout')

@section('title', 'Shop - Haichan')

@section('content')

<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="background: var(--content-bg); border: 3px solid var(--accent-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--accent-color); margin: 0 0 10px 0;">
            🛒 HAICHAN SHOP
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">
            Spend your mining points on exclusive perks and features
        </p>
    </div>

    @if($user)
        <!-- User Balance -->
        <div style="background: var(--content-bg); border: 2px solid var(--accent-color); border-radius: 8px; padding: 20px; margin-bottom: 25px; text-align: center;">
            <div style="font-size: 14px; color: var(--text-secondary); margin-bottom: 5px;">Your Balance</div>
            <div style="font-size: 32px; color: var(--accent-color); font-weight: bold;" id="user-balance">
                {{ number_format($user->total_pow_points) }} points
            </div>
            <div style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">
                Level {{ $user->level }} • {{ $user->mining_power }}x Mining Power
            </div>
        </div>

        <!-- Shop Items Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            @foreach($items as $item)
                <div class="shop-item" style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px; transition: all 0.3s;">
                    <div style="display: flex; align-items: start; gap: 15px; margin-bottom: 15px;">
                        <div style="font-size: 48px;">{{ $item->icon }}</div>
                        <div style="flex: 1;">
                            <h3 style="color: var(--accent-color); margin: 0 0 8px 0; font-size: 18px;">
                                {{ $item->name }}
                            </h3>
                            <p style="color: var(--text-primary); margin: 0; font-size: 13px; line-height: 1.5;">
                                {{ $item->description }}
                            </p>
                        </div>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <div style="font-size: 24px; font-weight: bold; color: var(--accent-color);">
                                {{ number_format($item->price) }}
                            </div>
                            <div style="font-size: 11px; color: var(--text-secondary);">
                                Requires Level {{ $item->level_required }}
                            </div>
                            @if($item->stock !== null)
                                <div style="font-size: 11px; color: var(--text-secondary);">
                                    Stock: {{ $item->stock }}
                                </div>
                            @endif
                        </div>

                        @php
                            $canPurchase = $item->canBePurchasedBy($user);
                            $alreadyOwned = in_array($item->id, $userPurchases);
                        @endphp

                        @if($alreadyOwned)
                            <button disabled style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 6px; cursor: not-allowed; font-weight: bold;">
                                Owned ✓
                            </button>
                        @elseif($canPurchase)
                            <button 
                                onclick="purchaseItem({{ $item->id }}, '{{ $item->name }}', {{ $item->price }})"
                                style="padding: 10px 20px; background: var(--accent-color); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; transition: all 0.3s;"
                                onmouseover="this.style.transform='translateY(-2px)'"
                                onmouseout="this.style.transform='translateY(0)'">
                                Buy Now
                            </button>
                        @else
                            <button disabled style="padding: 10px 20px; background: #999; color: white; border: none; border-radius: 6px; cursor: not-allowed; font-weight: bold;">
                                @if($user->level < $item->level_required)
                                    Level {{ $item->level_required }} Required
                                @elseif($user->total_pow_points < $item->price)
                                    Not Enough Points
                                @else
                                    Unavailable
                                @endif
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <!-- Not Logged In -->
        <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 40px; text-align: center;">
            <div style="font-size: 80px; margin-bottom: 20px;">🔒</div>
            <h2 style="color: var(--accent-color); margin: 0 0 15px 0;">Login Required</h2>
            <p style="color: var(--text-secondary); font-size: 14px; margin: 0 0 20px 0;">
                You need to be logged in to access the shop.
            </p>
            <a href="/login" style="display: inline-block; padding: 12px 24px; background: var(--accent-color); color: white; text-decoration: none; border-radius: 6px; font-weight: bold;">
                Login / Register
            </a>
        </div>
    @endif

</div>

@if($user)
<script nonce="{{ app('csp_nonce') }}">
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function purchaseItem(itemId, itemName, price) {
    if (!confirm(`Purchase "${itemName}" for ${price.toLocaleString()} points?`)) {
        return;
    }

    const button = event.target;
    button.disabled = true;
    button.textContent = 'Processing...';

    try {
        const response = await fetch(`/shop/purchase/${itemId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        const data = await response.json();

        if (data.success) {
            // Update balance
            document.getElementById('user-balance').textContent = data.remaining_points.toLocaleString() + ' points';
            
            // Update button
            button.textContent = 'Owned ✓';
            button.style.background = '#999';
            button.style.cursor = 'not-allowed';
            
            // Show success message
            showMessage(data.message, 'success');
        } else {
            button.disabled = false;
            button.textContent = 'Buy Now';
            showMessage(data.message, 'error');
        }
    } catch (error) {
        button.disabled = false;
        button.textContent = 'Buy Now';
        showMessage('Purchase failed. Please try again.', 'error');
        console.error('Purchase error:', error);
    }
}

function showMessage(message, type) {
    const messageDiv = document.createElement('div');
    messageDiv.textContent = message;
    messageDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        border-radius: 8px;
        font-weight: bold;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(messageDiv);
    
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        messageDiv.style.transition = 'opacity 0.3s';
        setTimeout(() => messageDiv.remove(), 300);
    }, 3000);
}
</script>

<style>
.shop-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.2);
}
</style>
@endif

@endsection
