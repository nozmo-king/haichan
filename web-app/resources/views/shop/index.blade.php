@extends('layout')

@section('title', 'Point Shop - Haichan PoW Imageboard')

@section('content')
<div style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
    <!-- Header -->
    <div style="background: #F5F5DC; padding: 30px; border-radius: 12px; border: 2px solid #708B75; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1); text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #3D315B; margin: 0 0 10px 0;">
            🛒 Point Shop
        </h1>
        <p style="color: #6B7A6B; font-size: 14px; margin: 0 0 15px 0;">
            Spend your hard-earned PoW points on boosts and cosmetics
        </p>
        <div style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #F5F5DC; padding: 10px 20px; border-radius: 8px; display: inline-block; font-weight: bold; font-size: 18px;">
            ⚡ {{ number_format($user->total_pow_points) }} Points Available
        </div>
    </div>
    
    <!-- Shop Categories -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
        
        <!-- Utility Items -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; overflow: hidden;">
            <div style="background: linear-gradient(135deg, #708B75, #5a7860); color: #F5F5DC; padding: 15px 20px;">
                <h3 style="margin: 0; font-family: 'Nova Cut', serif; font-size: 18px;">🔧 Utility</h3>
            </div>
            <div style="padding: 20px;">
                @foreach($shopItems as $itemId => $item)
                    @if($item['category'] === 'utility')
                        <div class="shop-item" data-item-id="{{ $itemId }}" style="background: #FFFACD; border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <span style="font-size: 24px;">{{ $item['icon'] }}</span>
                                <div>
                                    <h4 style="margin: 0; color: #3D315B; font-size: 16px;">{{ $item['name'] }}</h4>
                                    <p style="margin: 0; color: #6B7A6B; font-size: 12px;">{{ $item['description'] }}</p>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: between; align-items: center;">
                                <span style="background: #708B75; color: #F5F5DC; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                    {{ number_format($item['cost']) }} pts
                                </span>
                                <button onclick="purchaseItem('{{ $itemId }}')" style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: auto;">
                                    Buy
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Cosmetic Items -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; overflow: hidden;">
            <div style="background: linear-gradient(135deg, #9AB87A, #B87333); color: #F5F5DC; padding: 15px 20px;">
                <h3 style="margin: 0; font-family: 'Nova Cut', serif; font-size: 18px;">🎨 Cosmetic</h3>
            </div>
            <div style="padding: 20px;">
                @foreach($shopItems as $itemId => $item)
                    @if($item['category'] === 'cosmetic')
                        <div class="shop-item" data-item-id="{{ $itemId }}" style="background: #FFFACD; border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <span style="font-size: 24px;">{{ $item['icon'] }}</span>
                                <div>
                                    <h4 style="margin: 0; color: #3D315B; font-size: 16px;">{{ $item['name'] }}</h4>
                                    <p style="margin: 0; color: #6B7A6B; font-size: 12px;">{{ $item['description'] }}</p>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: between; align-items: center;">
                                <span style="background: #708B75; color: #F5F5DC; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                    {{ number_format($item['cost']) }} pts
                                </span>
                                <button onclick="purchaseItem('{{ $itemId }}')" style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: auto;">
                                    Buy
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        
        <!-- Boost Items -->
        <div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; overflow: hidden;">
            <div style="background: linear-gradient(135deg, #CD5C5C, #B87333); color: #F5F5DC; padding: 15px 20px;">
                <h3 style="margin: 0; font-family: 'Nova Cut', serif; font-size: 18px;">⚡ Boosts</h3>
            </div>
            <div style="padding: 20px;">
                @foreach($shopItems as $itemId => $item)
                    @if($item['category'] === 'boost')
                        <div class="shop-item" data-item-id="{{ $itemId }}" style="background: #FFFACD; border: 2px solid #e0e0e0; border-radius: 8px; padding: 15px; margin-bottom: 15px; cursor: pointer; transition: all 0.3s ease;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <span style="font-size: 24px;">{{ $item['icon'] }}</span>
                                <div>
                                    <h4 style="margin: 0; color: #3D315B; font-size: 16px;">{{ $item['name'] }}</h4>
                                    <p style="margin: 0; color: #6B7A6B; font-size: 12px;">{{ $item['description'] }}</p>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: between; align-items: center;">
                                <span style="background: #708B75; color: #F5F5DC; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">
                                    {{ number_format($item['cost']) }} pts
                                </span>
                                <button onclick="purchaseItem('{{ $itemId }}')" style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; border: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: auto;">
                                    Buy
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="/" style="color: #708B75; text-decoration: none; font-weight: 600; padding: 10px 20px; border: 2px solid #708B75; border-radius: 6px; transition: all 0.3s ease; margin: 0 10px;"
           onmouseover="this.style.background='#708B75'; this.style.color='#F5F5DC';"
           onmouseout="this.style.background='transparent'; this.style.color='#708B75';">
            ← Back to Boards
        </a>
    </div>
</div>

<style>
.shop-item:hover {
    border-color: #708B75 !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(112, 139, 117, 0.2);
}

.shop-item button:hover {
    background: linear-gradient(135deg, #708B75, #5a7860) !important;
    transform: scale(1.05);
}
</style>

<script>
let currentPoints = {{ $user->total_pow_points }};

async function purchaseItem(itemId) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Buying...';
    button.style.opacity = '0.6';
    
    try {
        const response = await fetch('/shop/purchase', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                item_id: itemId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update points display
            currentPoints = data.remaining_points;
            document.querySelector('.point-display').textContent = `⚡ ${currentPoints.toLocaleString()} Points Available`;
            
            // Show success message
            showNotification('✅ ' + data.message, 'success');
            
            // Animate the purchase
            button.textContent = '✓ Bought';
            button.style.background = '#28a745';
            
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                button.style.opacity = '1';
                button.style.background = '';
            }, 3000);
            
        } else {
            showNotification('❌ ' + data.error, 'error');
            button.textContent = originalText;
            button.disabled = false;
            button.style.opacity = '1';
        }
        
    } catch (error) {
        showNotification('❌ Purchase failed. Please try again.', 'error');
        button.textContent = originalText;
        button.disabled = false;
        button.style.opacity = '1';
    }
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : '#dc3545'};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        font-weight: bold;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        max-width: 400px;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Update points display selector
document.addEventListener('DOMContentLoaded', function() {
    const pointsDisplay = document.querySelector('[style*="⚡"]');
    if (pointsDisplay) {
        pointsDisplay.classList.add('point-display');
    }
});
</script>
@endsection