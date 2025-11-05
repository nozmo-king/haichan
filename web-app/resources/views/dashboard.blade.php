@extends('layout')

@section('title', 'Haichan - a proof-of-work imageboard')

@section('content')
<!-- Hero Section -->
<div class="hero-section" style="background: linear-gradient(135deg, #F5F5DC, #FFFACD); border: 2px solid #708B75; border-radius: 12px; padding: 40px 20px; text-align: center; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: #444B6E; margin: 0 0 15px 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">
        ⚡ HAICHAN
    </h1>
    <p style="color: #6B7A6B; font-size: 18px; max-width: 600px; margin: 0 auto 20px auto; font-weight: 500;">
        a proof-of-work imageboard. Mine, post, discuss.
    </p>
    <div class="stats-quick" style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 20px;">
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #708B75;">{{ number_format($activeSessions) }}</div>
            <div style="font-size: 12px; color: #6B7A6B;">Active Miners</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #708B75;">{{ number_format($userCount) }}</div>
            <div style="font-size: 12px; color: #6B7A6B;">Total Users</div>
        </div>
        <div style="text-align: center;">
            <div style="font-size: 24px; font-weight: bold; color: #708B75;">{{ number_format($recentProofs) }}</div>
            <div style="font-size: 12px; color: #6B7A6B;">Proofs (5min)</div>
        </div>
    </div>
</div>

<!-- Feature Grid -->
<div class="features-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px;">
    
    <!-- Boards & Discussion -->
    <div class="feature-card" style="background: #FFF; border: 2px solid #708B75; border-radius: 8px; padding: 25px; transition: all 0.3s ease; position: relative; overflow: hidden;">
        <div class="feature-header" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <span style="font-size: 32px;">💬</span>
            <div>
                <h3 style="color: #444B6E; margin: 0; font-size: 20px;">Discussion Boards</h3>
                <p style="color: #6B7A6B; margin: 5px 0 0 0; font-size: 14px;">{{ $boards->count() }} active boards</p>
            </div>
        </div>
        <p style="color: #495057; margin: 0 0 20px 0; line-height: 1.5;">
            Post threads, reply to discussions, share images. All interactions require proof-of-work mining for spam prevention.
        </p>
        <div class="board-quick-list" style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px;">
            @foreach($boards->take(6) as $board)
            <a href="{{ route('forum.board', $board->code) }}" style="background: rgba(112, 139, 117, 0.1); color: #708B75; text-decoration: none; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; border: 1px solid rgba(112, 139, 117, 0.3);">
                /{{ $board->code }}/
            </a>
            @endforeach
        </div>
        <a href="{{ route('boards.index') }}" style="background: #708B75; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; display: inline-block;">
            🗂️ Browse All Boards
        </a>
    </div>

    <!-- Mining System -->
    <div class="feature-card" style="background: #FFF; border: 2px solid #D4AF37; border-radius: 8px; padding: 25px; transition: all 0.3s ease;">
        <div class="feature-header" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <span style="font-size: 32px;">⛏️</span>
            <div>
                <h3 style="color: #444B6E; margin: 0; font-size: 20px;">21e8 Mining</h3>
                <p style="color: #6B7A6B; margin: 5px 0 0 0; font-size: 14px;">Proof-of-Work system</p>
            </div>
        </div>
        <p style="color: #495057; margin: 0 0 20px 0; line-height: 1.5;">
            Our custom 21e8 hash target system. Mine by hovering over posts, threads, and images. Earn points and climb leaderboards.
        </p>
        <div class="mining-stats" style="background: rgba(212, 175, 55, 0.1); border-radius: 6px; padding: 15px; margin-bottom: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; text-align: center;">
                <div>
                    <div style="font-weight: bold; color: #D4AF37; font-size: 18px;">{{ number_format(\App\Models\ProofOfWork::sum('points')) }}</div>
                    <div style="font-size: 11px; color: #6B7A6B;">Total Points Mined</div>
                </div>
                <div>
                    <div style="font-weight: bold; color: #D4AF37; font-size: 18px;">{{ number_format(\App\Models\ProofOfWork::count()) }}</div>
                    <div style="font-size: 11px; color: #6B7A6B;">Total Proofs</div>
                </div>
            </div>
        </div>
        <a href="{{ route('mining.dashboard') }}" style="background: #D4AF37; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; display: inline-block;">
            📊 Mining Dashboard
        </a>
    </div>

    <!-- User Profile & Shop -->
    <div class="feature-card" style="background: #FFF; border: 2px solid #6F42C1; border-radius: 8px; padding: 25px; transition: all 0.3s ease;">
        <div class="feature-header" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <span style="font-size: 32px;">👤</span>
            <div>
                <h3 style="color: #444B6E; margin: 0; font-size: 20px;">Profile & Shop</h3>
                <p style="color: #6B7A6B; margin: 5px 0 0 0; font-size: 14px;">Customize your identity</p>
            </div>
        </div>
        <p style="color: #495057; margin: 0 0 20px 0; line-height: 1.5;">
            Manage your Bitcoin keys, view mining stats, purchase cosmetics and upgrades with earned points.
        </p>
        @if(session('bitcoin_auth_user'))
        <div class="user-info" style="background: rgba(111, 66, 193, 0.1); border-radius: 6px; padding: 15px; margin-bottom: 20px;">
            <div style="font-weight: bold; color: #6F42C1; margin-bottom: 8px;">{{ session('bitcoin_auth_user')->username }}</div>
            <div style="font-size: 12px; color: #6B7A6B;">
                Points: <strong>{{ number_format(session('bitcoin_auth_user')->points ?? 0) }}</strong> | 
                Level: <strong>{{ session('bitcoin_auth_user')->mining_level ?? 1 }}</strong>
            </div>
        </div>
        @endif
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('user.profile', session('bitcoin_auth_id')) }}" style="background: #6F42C1; color: white; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 14px;">
                📊 Profile
            </a>
            <a href="/shop" style="background: transparent; color: #6F42C1; text-decoration: none; padding: 8px 16px; border-radius: 6px; font-weight: bold; font-size: 14px; border: 1px solid #6F42C1;">
                🛍️ Shop
            </a>
        </div>
    </div>

    <!-- Image Library -->
    <div class="feature-card" style="background: #FFF; border: 2px solid #17A2B8; border-radius: 8px; padding: 25px; transition: all 0.3s ease;">
        <div class="feature-header" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <span style="font-size: 32px;">🖼️</span>
            <div>
                <h3 style="color: #444B6E; margin: 0; font-size: 20px;">Image Library</h3>
                <p style="color: #6B7A6B; margin: 5px 0 0 0; font-size: 14px;">Shared content system</p>
            </div>
        </div>
        <p style="color: #495057; margin: 0 0 20px 0; line-height: 1.5;">
            Browse uploaded images by hash. Reuse content without re-uploading. Discover popular memes and art.
        </p>
        <div style="background: rgba(23, 162, 184, 0.1); border-radius: 6px; padding: 15px; margin-bottom: 20px; text-align: center;">
            <div style="font-weight: bold; color: #17A2B8; font-size: 18px;">{{ number_format(\App\Models\ImageLibrary::count()) }}</div>
            <div style="font-size: 12px; color: #6B7A6B;">Images in Library</div>
        </div>
        <a href="/images" style="background: #17A2B8; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; display: inline-block;">
            🔍 Browse Library
        </a>
    </div>

    <!-- Chat System -->
    <div class="feature-card" style="background: #FFF; border: 2px solid #28A745; border-radius: 8px; padding: 25px; transition: all 0.3s ease;">
        <div class="feature-header" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <span style="font-size: 32px;">💭</span>
            <div>
                <h3 style="color: #444B6E; margin: 0; font-size: 20px;">Live Chat</h3>
                <p style="color: #6B7A6B; margin: 5px 0 0 0; font-size: 14px;">Real-time discussion</p>
            </div>
        </div>
        <p style="color: #495057; margin: 0 0 20px 0; line-height: 1.5;">
            Join themed chat rooms for instant discussion. Mining-based moderation keeps conversations quality.
        </p>
        <div style="background: rgba(40, 167, 69, 0.1); border-radius: 6px; padding: 15px; margin-bottom: 20px; text-align: center;">
            <div style="font-weight: bold; color: #28A745; font-size: 18px;">{{ \App\Models\ChatRoom::count() }}</div>
            <div style="font-size: 12px; color: #6B7A6B;">Active Chat Rooms</div>
        </div>
        <a href="/chat" style="background: #28A745; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; display: inline-block;">
            💬 Join Chat
        </a>
    </div>

    <!-- Bug Bounty -->
    <div class="feature-card" style="background: #FFF; border: 2px solid #DC3545; border-radius: 8px; padding: 25px; transition: all 0.3s ease;">
        <div class="feature-header" style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
            <span style="font-size: 32px;">🏆</span>
            <div>
                <h3 style="color: #444B6E; margin: 0; font-size: 20px;">Bug Bounty</h3>
                <p style="color: #6B7A6B; margin: 5px 0 0 0; font-size: 14px;">Earn rewards</p>
            </div>
        </div>
        <p style="color: #495057; margin: 0 0 20px 0; line-height: 1.5;">
            Find bugs, contribute code, suggest improvements. Get paid in Bitcoin for making Haichan better.
        </p>
        <div style="background: rgba(220, 53, 69, 0.1); border-radius: 6px; padding: 15px; margin-bottom: 20px; text-align: center;">
            <div style="font-weight: bold; color: #DC3545; font-size: 18px;">{{-- Real bounty data needed --}}TBD</div>
            <div style="font-size: 12px; color: #6B7A6B;">Total Bounties Paid</div>
        </div>
        <a href="{{ route('bounty') }}" style="background: #DC3545; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; display: inline-block;">
            🎯 View Bounties
        </a>
    </div>

</div>

<!-- Recent Activity -->
<div class="recent-activity" style="background: #FFF; border: 1px solid #DEE2E6; border-radius: 8px; padding: 25px; margin-bottom: 30px;">
    <h2 style="color: #495057; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
        <span>🕐</span> Recent Activity
    </h2>
    
    <div class="activity-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
        
        <!-- Latest Threads -->
        <div class="activity-section">
            <h4 style="color: #6C757D; margin: 0 0 15px 0; font-size: 16px; border-bottom: 1px solid #DEE2E6; padding-bottom: 8px;">
                📝 Latest Threads
            </h4>
            @php
                $recentThreads = \App\Models\Thread::with('board')->orderBy('created_at', 'desc')->take(5)->get();
            @endphp
            @foreach($recentThreads as $thread)
            <div style="margin-bottom: 12px; padding: 8px; background: #F8F9FA; border-radius: 4px;">
                <a href="/{{ $thread->board->code }}/{{ $thread->id }}" style="color: #495057; text-decoration: none; font-size: 13px; font-weight: bold;">
                    {{ Str::limit($thread->title, 40) }}
                </a>
                <div style="font-size: 11px; color: #6C757D; margin-top: 4px;">
                    /{{ $thread->board->code }}/ • {{ $thread->created_at->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>

        <!-- Top Miners -->
        <div class="activity-section">
            <h4 style="color: #6C757D; margin: 0 0 15px 0; font-size: 16px; border-bottom: 1px solid #DEE2E6; padding-bottom: 8px;">
                ⛏️ Top Miners (24h)
            </h4>
            @php
                $topMiners = \App\Models\BitcoinAuth::where('points', '>', 0)
                    ->orderBy('points', 'desc')
                    ->take(5)
                    ->get();
            @endphp
            @foreach($topMiners as $index => $miner)
            <div style="margin-bottom: 12px; padding: 8px; background: #F8F9FA; border-radius: 4px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 13px; font-weight: bold; color: #495057;">
                        {{ $miner->username }}
                    </span>
                    @if($index === 0)
                        <span style="color: #FFD700; font-size: 12px;">👑</span>
                    @endif
                </div>
                <div style="font-size: 11px; color: #D4AF37; font-weight: bold;">
                    {{ number_format($miner->points) }}pts
                </div>
            </div>
            @endforeach
        </div>

        <!-- System Stats -->
        <div class="activity-section">
            <h4 style="color: #6C757D; margin: 0 0 15px 0; font-size: 16px; border-bottom: 1px solid #DEE2E6; padding-bottom: 8px;">
                📊 System Stats
            </h4>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between; padding: 6px 8px; background: #F8F9FA; border-radius: 4px;">
                    <span style="font-size: 12px; color: #6C757D;">Total Threads</span>
                    <span style="font-size: 12px; font-weight: bold; color: #495057;">{{ number_format(\App\Models\Thread::count()) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 8px; background: #F8F9FA; border-radius: 4px;">
                    <span style="font-size: 12px; color: #6C757D;">Total Posts</span>
                    <span style="font-size: 12px; font-weight: bold; color: #495057;">{{ number_format(\App\Models\Post::count()) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 8px; background: #F8F9FA; border-radius: 4px;">
                    <span style="font-size: 12px; color: #6C757D;">Images Uploaded</span>
                    <span style="font-size: 12px; font-weight: bold; color: #495057;">{{ number_format(\App\Models\ImageLibrary::count()) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 8px; background: #F8F9FA; border-radius: 4px;">
                    <span style="font-size: 12px; color: #6C757D;">Hash Rate</span>
                    <span style="font-size: 12px; font-weight: bold; color: #495057;">{{ number_format($recentProofs * 12) }} H/min</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions" style="background: linear-gradient(135deg, #708B75, #9AB87A); color: white; border-radius: 8px; padding: 25px; text-align: center;">
    <h2 style="margin: 0 0 20px 0; font-size: 24px;">🚀 Quick Actions</h2>
    <div style="display: flex; justify-content: center; gap: 15px; flex-wrap: wrap;">
        <a href="{{ route('forum.create', 'gen') }}" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            ✍️ Create Thread
        </a>
        <a href="/chat" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            💬 Join Chat
        </a>
        <a href="{{ route('mining.dashboard') }}" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            ⛏️ Start Mining
        </a>
        <a href="/images" 
           style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-weight: bold; transition: all 0.3s ease;"
           onmouseover="this.style.background='rgba(255,255,255,0.3)'"
           onmouseout="this.style.background='rgba(255,255,255,0.2)'">
            🖼️ Browse Images
        </a>
    </div>
</div>

<style>
.feature-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
    border-color: rgba(112, 139, 117, 0.8);
}

@media (max-width: 768px) {
    .features-grid {
        grid-template-columns: 1fr;
    }
    .activity-grid {
        grid-template-columns: 1fr;
    }
    .stats-quick {
        gap: 15px !important;
    }
}
</style>

@endsection