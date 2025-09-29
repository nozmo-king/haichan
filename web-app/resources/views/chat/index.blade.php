@extends('layout')

@section('title', 'PoW Chat Rooms - Haichan')

@section('content')
<div style="margin: 60px auto; max-width: 1200px; background: var(--primary-bg); border: 2px solid var(--border-color); box-shadow: 0 4px 16px rgba(68, 75, 110, 0.3);">
    <!-- Header -->
    <div style="background: var(--secondary-bg); padding: 20px 30px; border-bottom: 2px solid var(--border-color); text-align: center;">
        <h1 style="font-size: 28px; color: var(--text-primary); margin: 0 0 8px 0; font-weight: 300; letter-spacing: 1.5px; font-family: 'Nova Cut', serif;">
            💬 PoW Chat Rooms ⛏️
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 8px 0 0 0;">
            Proof-of-work powered chat rooms • Your PoW Points: <strong>{{ number_format($userPowPoints) }}</strong>
        </p>
    </div>

    <!-- Chat Rooms Grid -->
    <div style="padding: 30px; background: var(--content-bg);">
        @if(session('error'))
            <div style="background: #ffebee; color: #c62828; padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #c62828;">
                {{ session('error') }}
            </div>
        @endif

        @if(count($rooms) > 0)
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
                @foreach($rooms as $room)
                <div class="chat-room-card" style="
                    background: var(--ib-bg);
                    border: 2px solid var(--ib-border);
                    border-radius: 8px;
                    padding: 20px;
                    transition: all 0.3s ease;
                    position: relative;
                    cursor: pointer;
                    {{ $userPowPoints >= $room->min_pow_points ? '' : 'opacity: 0.6; filter: grayscale(50%);' }}
                " onclick="window.location.href='{{ route('chat.room', $room) }}'">
                    
                    <!-- Room Status Badge -->
                    <div style="position: absolute; top: -8px; right: -8px; background: {{ $room->active_users_count > 0 ? '#4CAF50' : '#666' }}; color: white; padding: 4px 8px; font-size: 10px; font-weight: bold; border-radius: 12px; border: 2px solid white;">
                        {{ $room->active_users_count }} online
                    </div>

                    <!-- Room Header -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <h3 style="color: var(--ib-accent); margin: 0; font-size: 18px; font-family: 'Nova Cut', serif;">
                            {{ $room->name }}
                        </h3>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            @if($room->pow_difficulty === '777')
                                <span style="color: #FFD700;">🏆</span>
                            @elseif($room->pow_difficulty === '21e')
                                <span style="color: #9370DB;">💎</span>
                            @else
                                <span style="color: #9fd971;">⚡</span>
                            @endif
                            <span style="font-size: 10px; color: var(--ib-text-muted); font-family: monospace;">
                                {{ $room->pow_difficulty }}
                            </span>
                        </div>
                    </div>

                    <!-- Room Description -->
                    <p style="color: var(--ib-text); font-size: 12px; line-height: 1.4; margin-bottom: 16px;">
                        {{ $room->description }}
                    </p>

                    <!-- Room Requirements -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: var(--ib-bg-alt); border-radius: 4px; margin-bottom: 12px;">
                        <div>
                            <div style="font-size: 10px; color: var(--ib-text-muted); margin-bottom: 2px;">Min PoW Points</div>
                            <div style="font-size: 14px; font-weight: bold; color: {{ $userPowPoints >= $room->min_pow_points ? '#4CAF50' : '#f44336' }};">
                                {{ number_format($room->min_pow_points) }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: var(--ib-text-muted); margin-bottom: 2px;">Messages</div>
                            <div style="font-size: 14px; font-weight: bold; color: var(--ib-text);">
                                {{ number_format($room->messages_count) }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 10px; color: var(--ib-text-muted); margin-bottom: 2px;">Rate Limit</div>
                            <div style="font-size: 14px; font-weight: bold; color: var(--ib-text);">
                                {{ $room->message_rate_limit }}/min
                            </div>
                        </div>
                    </div>

                    <!-- Access Status -->
                    @if($userPowPoints >= $room->min_pow_points)
                        <div style="text-align: center; padding: 8px; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            ✅ Click to Enter Room
                        </div>
                    @else
                        <div style="text-align: center; padding: 8px; background: #f44336; color: white; border-radius: 4px; font-size: 12px; font-weight: bold;">
                            🔒 Need {{ number_format($room->min_pow_points - $userPowPoints) }} more PoW points
                        </div>
                    @endif
                </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                <div style="font-size: 48px; margin-bottom: 20px;">💬</div>
                <h3 style="color: var(--text-primary); margin: 0 0 16px 0; font-weight: 300;">No Chat Rooms Available</h3>
                <p style="margin: 0 0 24px 0; font-size: 14px;">
                    Chat rooms will be created by administrators.
                </p>
            </div>
        @endif

        <!-- How It Works -->
        <div style="margin-top: 40px; padding: 20px; background: var(--ib-bg-alt); border: 1px solid var(--ib-border); border-radius: 8px;">
            <h4 style="color: var(--ib-accent); margin: 0 0 12px 0; font-size: 16px;">⚡ How PoW Chat Works</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; font-size: 12px; line-height: 1.4;">
                <div>
                    <strong style="color: var(--ib-text);">🔐 Access Control</strong><br>
                    <span style="color: var(--ib-text-muted);">Rooms require minimum PoW points to enter. Earn points by mining on posts and threads.</span>
                </div>
                <div>
                    <strong style="color: var(--ib-text);">⛏️ Message Mining</strong><br>
                    <span style="color: var(--ib-text-muted);">Each message requires proof-of-work. Higher difficulty rooms need better hash patterns.</span>
                </div>
                <div>
                    <strong style="color: var(--ib-text);">🎯 Rare Patterns</strong><br>
                    <span style="color: var(--ib-text-muted);">Find 777, deadbeef, 1337 patterns for bonus points and special announcements!</span>
                </div>
                <div>
                    <strong style="color: var(--ib-text);">👥 Community</strong><br>
                    <span style="color: var(--ib-text-muted);">Your Bitcoin credentials and username are linked. Build reputation through quality mining.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chat-room-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(68, 75, 110, 0.4);
    border-color: var(--ib-accent);
}

.chat-room-card:hover:not([style*="opacity: 0.6"]) {
    background: linear-gradient(135deg, var(--ib-bg), var(--ib-bg-alt));
}

@media (max-width: 768px) {
    .chat-room-card {
        min-width: unset !important;
    }
}
</style>
@endsection