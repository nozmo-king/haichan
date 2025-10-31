@extends('layout')

@section('title', 'Statistics - Haichan PoW Imageboard')

@section('content')
<script nonce="{{ app('csp_nonce') }}" src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Header -->
<div style="background: #F5F5DC; padding: 30px; border-radius: 12px; border: 2px solid #708B75; margin-bottom: 30px; box-shadow: 0 4px 16px rgba(112, 139, 117, 0.1); text-align: center;">
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #3D315B; margin: 0 0 10px 0;">
        📊 Haichan Statistics
    </h1>
    <p style="color: #6B7A6B; font-size: 14px; margin: 0;">
        Live metrics • Updated every minute • <span class="glow-text">Proof-of-Work</span> analytics
    </p>
</div>

<!-- Real-Time Stats -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🟢 Live Statistics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #00FF00; font-family: 'Courier New', monospace;">
                    {{ $stats['users_online'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Users Online</div>
            </div>
            
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">
                    {{ $stats['proofs_today'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Proofs Mined Today</div>
            </div>
            
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">
                    {{ $stats['posts_today'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Posts Today</div>
            </div>
            
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 36px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">
                    {{ $stats['threads_today'] ?? 0 }}
                </div>
                <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">Threads Today</div>
            </div>
        </div>
        
        <!-- Mining Brain Stats -->
        <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75; margin-bottom: 20px;">
            <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px; text-align: center;">🧠 Live Mining Brain Stats</h4>
            <div id="brain-live-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 15px; text-align: center;">
                <div>
                    <div id="live-hashrate" style="font-size: 24px; font-weight: bold; color: #00FF00; font-family: 'Courier New', monospace;">--</div>
                    <div style="color: #6B7A6B; font-size: 11px;">Current H/s</div>
                </div>
                <div>
                    <div id="live-session-proofs" style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">--</div>
                    <div style="color: #6B7A6B; font-size: 11px;">Session Proofs</div>
                </div>
                <div>
                    <div id="live-session-points" style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">--</div>
                    <div style="color: #6B7A6B; font-size: 11px;">Session Points</div>
                </div>
                <div>
                    <div id="live-power-level" style="font-size: 24px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">--</div>
                    <div style="color: #6B7A6B; font-size: 11px;">Power Level</div>
                </div>
            </div>
        </div>
        
        <!-- Activity Chart -->
        <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75;">
            <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px; text-align: center;">📈 7-Day Activity Trend</h4>
            <canvas id="activityChart" width="400" height="150"></canvas>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            📚 Overall Statistics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ number_format($stats['total_users'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Users</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($stats['total_threads'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Threads</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #B87333; font-family: 'Courier New', monospace;">{{ number_format($stats['total_posts'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Posts</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">{{ number_format($stats['total_pow_points'] ?? 0, 1) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">PoW Points</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ $stats['total_boards'] ?? 0 }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Active Boards</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($stats['posts_per_day_avg'] ?? 0, 1) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Posts/Day Avg</div>
            </div>
        </div>
    </div>
</div>

<!-- Mining Analytics -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #CD5C5C, #B87333); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            ⛏️ Mining Analytics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Proof Distribution -->
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75;">
                <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px; text-align: center;">🎯 Difficulty Distribution</h4>
                <canvas id="difficultyChart" width="200" height="200"></canvas>
            </div>
            
            <!-- Mining Performance -->
            <div style="background: #FFFACD; padding: 20px; border-radius: 8px; border: 1px solid #708B75;">
                <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px;">📊 Mining Performance</h4>
                <div style="font-family: 'Courier New', monospace; font-size: 12px;">
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span>Success Rate:</span>
                        <span style="color: #00FF00; font-weight: bold;">{{ number_format(($stats['proofs_today'] / max(1, $stats['posts_today'])) * 100, 1) }}%</span>
                    </div>
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span>Avg Points/Proof:</span>
                        <span style="color: #9AB87A; font-weight: bold;">{{ number_format($stats['avg_points_per_proof'] ?? 0, 2) }}</span>
                    </div>
                    <div style="margin-bottom: 10px; display: flex; justify-content: space-between;">
                        <span>This Week:</span>
                        <span style="color: #708B75; font-weight: bold;">{{ $stats['posts_week'] ?? 0 }} posts</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Growth Rate:</span>
                        <span style="color: {{ ($stats['growth_rate'] ?? 0) > 0 ? '#00FF00' : '#FF6666' }}; font-weight: bold;">
                            {{ number_format($stats['growth_rate'] ?? 0, 1) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Contributors -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #B87333, #9AB87A); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🏆 Top Contributors (24h)
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="background: #FFFACD; border-radius: 8px; padding: 20px; border: 1px solid #708B75;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                <!-- Top Miners -->
                <div>
                    <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px;">⛏️ Top Miners</h4>
                    @if(isset($stats['top_miners']) && count($stats['top_miners']) > 0)
                        @foreach($stats['top_miners'] as $index => $miner)
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(112, 139, 117, 0.2);">
                            <span style="color: #3D315B; font-size: 12px;">
                                #{{ $index + 1 }} {{ $miner->getDisplayName() }}
                            </span>
                            <span style="color: #9AB87A; font-weight: bold; font-size: 12px;">{{ $miner->daily_proofs }} proofs</span>
                        </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #6B7A6B; font-style: italic;">No data available</div>
                    @endif
                </div>
                
                <!-- Top Posters -->
                <div>
                    <h4 style="color: #708B75; margin: 0 0 15px 0; font-size: 14px;">💬 Top Posters</h4>
                    @if(isset($stats['top_posters']) && count($stats['top_posters']) > 0)
                        @foreach($stats['top_posters'] as $index => $poster)
                        <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid rgba(112, 139, 117, 0.2);">
                            <span style="color: #3D315B; font-size: 12px;">
                                #{{ $index + 1 }} {{ $poster->getDisplayName() }}
                            </span>
                            <span style="color: #B87333; font-weight: bold; font-size: 12px;">{{ $poster->daily_posts }} posts</span>
                        </div>
                        @endforeach
                    @else
                        <div style="text-align: center; color: #6B7A6B; font-style: italic;">No data available</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Trading Floor Grid -->
<div class="trading-floor" style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr; gap: 20px; padding: 0 20px; margin-bottom: 30px;">
    
    <!-- Main Market Board -->
    <div class="market-board" style="background: #FFF; border: 1px solid #708B75; border-radius: 8px; padding: 20px; min-height: 500px;">
        <div class="board-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #DEE2E6; padding-bottom: 10px;">
            <h3 style="margin: 0; color: #3D315B; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <span>📊</span> Live Mining Positions
            </h3>
            <div class="refresh-indicator" id="refresh-indicator" style="font-size: 10px; color: #6B7A6B;">
                ⟳ Live
            </div>
        </div>

        <!-- Board Selection Tabs -->
        <div class="board-tabs" style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
            @foreach($boards->take(8) as $board)
            <button class="board-tab" data-board="{{ $board->code }}" style="
                background: rgba(112, 139, 117, 0.1); 
                border: 1px solid rgba(112, 139, 117, 0.3); 
                color: #708B75; 
                padding: 6px 12px; 
                border-radius: 4px; 
                font-size: 10px; 
                font-weight: bold;
                cursor: pointer;
                transition: all 0.2s ease;
            ">
                /{{ $board->code }}/
            </button>
            @endforeach
        </div>

        <!-- Live Threads with Mining Metrics -->
        <div class="mining-threads" id="mining-threads" style="display: flex; flex-direction: column; gap: 12px;">
            
            @foreach($recentThreads as $thread)
            <div class="mining-thread" data-thread-id="{{ $thread->id }}" data-mine-type="thread" data-board-code="{{ $thread->board->code }}" style="
                background: #F8F9FA; 
                border: 1px solid #DEE2E6; 
                border-radius: 6px; 
                padding: 12px; 
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
            ">
                <!-- Mining Aura Effect -->
                <div class="mining-aura" style="position: absolute; inset: -2px; background: linear-gradient(45deg, transparent, rgba(212, 175, 55, 0.1), transparent); border-radius: 8px; opacity: 0; transition: opacity 0.3s ease;"></div>
                
                <div style="display: flex; justify-content: between; align-items: flex-start; position: relative; z-index: 1;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <span style="background: #708B75; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px; font-weight: bold;">/{{ $thread->board->code }}/</span>
                            <span style="font-size: 10px; color: #6B7A6B;">{{ $thread->created_at->diffForHumans() }}</span>
                            <div class="mining-status-indicator" style="width: 6px; height: 6px; border-radius: 50%; background: #6C757D;"></div>
                        </div>
                        
                        <a href="/{{ $thread->board->code }}/{{ $thread->id }}" style="color: #3D315B; text-decoration: none; font-weight: bold; font-size: 13px; line-height: 1.3;">
                            {{ Str::limit($thread->title, 60) }}
                        </a>
                        
                        <div style="font-size: 11px; color: #6B7A6B; margin-top: 4px;">
                            By {{ $thread->bitcoinUser->username ?? 'Anonymous' }}
                        </div>
                    </div>
                    
                    <div class="thread-mining-stats" style="text-align: right; font-family: 'Courier New', monospace; font-size: 9px;">
                        <div style="color: #D4AF37; font-weight: bold;">⚡{{ number_format($thread->accumulated_points ?? 0, 1) }}</div>
                        <div style="color: #6B7A6B;">0 miners</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Mining Leaderboard & Stats -->
    <div class="mining-sidebar" style="display: flex; flex-direction: column; gap: 20px;">
        
        <!-- Top Miners Today -->
        <div class="leaderboard-panel" style="background: #FFF; border: 1px solid #708B75; border-radius: 8px; padding: 15px;">
            <h4 style="margin: 0 0 15px 0; color: #3D315B; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                <span>🏆</span> Top Miners (24h)
            </h4>
            
            @php
                $topMiners = \App\Models\BitcoinAuth::where('points', '>', 0)
                    ->orderBy('points', 'desc')
                    ->take(8)
                    ->get();
            @endphp
            
            @foreach($topMiners as $index => $miner)
            <div class="miner-rank" style="display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #F1F3F4;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 10px; color: #6B7A6B; font-family: 'Courier New', monospace; width: 15px;">#{{ $index + 1 }}</span>
                    <span style="font-size: 11px; font-weight: bold; color: #3D315B;">{{ Str::limit($miner->username, 12) }}</span>
                    @if($index === 0)
                        <span style="font-size: 10px;">👑</span>
                    @endif
                </div>
                <div style="font-size: 10px; color: #D4AF37; font-weight: bold; font-family: 'Courier New', monospace;">
                    {{ number_format($miner->points) }}pts
                </div>
            </div>
            @endforeach
        </div>

        <!-- Mining Insights -->
        <div class="insights-panel" style="background: #FFF; border: 1px solid #708B75; border-radius: 8px; padding: 15px;">
            <h4 style="margin: 0 0 15px 0; color: #3D315B; font-size: 14px;">📈 Market Insights</h4>
            
            <div class="insight-item" style="margin-bottom: 12px; padding: 8px; background: #F8F9FA; border-radius: 4px;">
                <div style="font-size: 11px; color: #6B7A6B;">Average 21e8 Discovery Time</div>
                <div style="font-size: 13px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;" id="avg-discovery-time">--</div>
            </div>
            
            <div class="insight-item" style="margin-bottom: 12px; padding: 8px; background: #F8F9FA; border-radius: 4px;">
                <div style="font-size: 11px; color: #6B7A6B;">Network Hash Rate</div>
                <div style="font-size: 13px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;" id="network-hashrate">--</div>
            </div>
            
            <div class="insight-item" style="margin-bottom: 12px; padding: 8px; background: #F8F9FA; border-radius: 4px;">
                <div style="font-size: 11px; color: #6B7A6B;">Peak Mining Hour</div>
                <div style="font-size: 13px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ date('H:i') }}</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-panel" style="background: linear-gradient(135deg, #708B75, #9AB87A); color: white; border-radius: 8px; padding: 15px; text-align: center;">
            <h4 style="margin: 0 0 15px 0; font-size: 14px;">⚡ Quick Actions</h4>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <a href="{{ route('forum.create', 'gen') }}" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                    ✍️ Create Thread
                </a>
                <a href="/chat" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                    💬 Join Chat
                </a>
                <a href="{{ route('mining.dashboard') }}" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                    📊 Full Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Overall Statistics -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px; margin-bottom: 30px;">
    <div style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            📚 Overall Statistics
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 20px;">
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ number_format($stats['total_users'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Users</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($stats['total_threads'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Threads</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #B87333; font-family: 'Courier New', monospace;">{{ number_format($stats['total_posts'] ?? 0) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Total Posts</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #CD5C5C; font-family: 'Courier New', monospace;">{{ number_format($stats['total_pow_points'] ?? 0, 1) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">PoW Points</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #708B75; font-family: 'Courier New', monospace;">{{ $stats['total_boards'] ?? 0 }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Active Boards</div>
            </div>
            
            <div style="background: #FFFACD; padding: 15px; border-radius: 8px; border: 1px solid #708B75; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #9AB87A; font-family: 'Courier New', monospace;">{{ number_format($stats['posts_per_day_avg'] ?? 0, 1) }}</div>
                <div style="color: #6B7A6B; font-size: 11px; text-transform: uppercase;">Posts/Day Avg</div>
            </div>
        </div>
    </div>
</div>

<!-- SHA256 Interactive Playground -->
<div class="sha256-playground" style="max-width: 1200px; margin: 20px auto; padding: 0 20px;">
    <div style="background: linear-gradient(135deg, #444B6E 0%, #3D315B 100%); border: 2px solid #708B75; border-radius: 12px; padding: 25px; color: #F5F5DC;">
        <h2 style="margin: 0 0 15px 0; font-size: 20px; display: flex; align-items: center; gap: 10px;">
            <span>🔬</span> SHA256 Live Laboratory
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <!-- Hash Input Area -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-size: 12px; color: #9AB87A;">Input Data:</label>
                <input type="text" id="hash-input" placeholder="Type anything..." 
                    style="width: 100%; padding: 10px; border: 1px solid #708B75; border-radius: 4px; background: #2A2D3A; color: #F5F5DC; font-family: 'Courier New', monospace; font-size: 13px;">
                <div style="margin-top: 10px; font-size: 11px; color: #9AB87A;">
                    <span id="input-length">0</span> characters
                </div>
            </div>
            
            <!-- Hash Output Area -->
            <div>
                <label style="display: block; margin-bottom: 8px; font-size: 12px; color: #9AB87A;">SHA256 Hash:</label>
                <div id="hash-output" style="
                    padding: 10px; 
                    background: #1A1D28; 
                    border: 1px solid #708B75; 
                    border-radius: 4px; 
                    font-family: 'Courier New', monospace; 
                    font-size: 11px; 
                    word-break: break-all;
                    min-height: 44px;
                    display: flex;
                    align-items: center;
                    color: #00FF00;
                ">
                    <span style="opacity: 0.5;">Hash will appear here...</span>
                </div>
                <div style="margin-top: 10px; font-size: 11px; display: flex; gap: 15px;">
                    <span>Starts with: <span id="hash-prefix" style="color: #D4AF37; font-weight: bold;">--</span></span>
                    <span>Zeros: <span id="zero-count" style="color: #DC3545; font-weight: bold;">0</span></span>
                </div>
            </div>
        </div>
        
        <!-- Mining Simulator -->
        <div style="background: rgba(0,0,0,0.2); border-radius: 8px; padding: 15px; margin-top: 20px;">
            <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #9AB87A;">⛏️ Nonce Miner Simulator</h3>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                <div>
                    <input type="text" id="mine-target" placeholder="Target prefix (e.g., 21e8)" 
                        value="21e8"
                        style="width: 100%; padding: 8px; border: 1px solid #708B75; border-radius: 4px; background: #2A2D3A; color: #F5F5DC; font-family: 'Courier New', monospace; font-size: 12px; margin-bottom: 10px;">
                    <div style="display: flex; gap: 10px;">
                        <button id="start-mining" style="
                            flex: 1;
                            padding: 10px;
                            background: linear-gradient(135deg, #28A745, #20893A);
                            border: none;
                            border-radius: 4px;
                            color: white;
                            font-weight: bold;
                            cursor: pointer;
                            font-size: 13px;
                        ">▶ Start Mining</button>
                        <button id="stop-mining" disabled style="
                            flex: 1;
                            padding: 10px;
                            background: linear-gradient(135deg, #DC3545, #C82333);
                            border: none;
                            border-radius: 4px;
                            color: white;
                            font-weight: bold;
                            cursor: pointer;
                            font-size: 13px;
                            opacity: 0.5;
                        ">⏹ Stop</button>
                    </div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 24px; font-weight: bold; color: #D4AF37; margin-bottom: 5px;" id="mining-hashrate">0</div>
                    <div style="color: #9AB87A; font-size: 11px;">Hashes/sec</div>
                    <div style="font-size: 14px; margin-top: 10px; color: #F5F5DC;">
                        Attempts: <span id="mining-attempts" style="font-family: 'Courier New', monospace;">0</span>
                    </div>
                </div>
            </div>
            <div id="mining-result" style="margin-top: 15px; padding: 12px; background: rgba(0,255,0,0.1); border: 1px solid #28A745; border-radius: 4px; display: none;">
                <div style="font-weight: bold; color: #28A745; margin-bottom: 5px;">✓ Hash Found!</div>
                <div style="font-family: 'Courier New', monospace; font-size: 10px; word-break: break-all; color: #9AB87A;">
                    <div>Nonce: <span id="found-nonce">--</span></div>
                    <div>Hash: <span id="found-hash">--</span></div>
                </div>
            </div>
        </div>
        
        <!-- Hash Pattern Explorer -->
        <div style="background: rgba(0,0,0,0.2); border-radius: 8px; padding: 15px; margin-top: 15px;">
            <h3 style="margin: 0 0 12px 0; font-size: 14px; color: #9AB87A;">🎯 Pattern Explorer</h3>
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; font-size: 11px;">
                <div class="pattern-box" style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-weight: bold; color: #F5F5DC; margin-bottom: 5px;">21e8</div>
                    <div style="color: #9AB87A;">1 in 1M</div>
                    <div style="font-size: 9px; color: #6B7A6B; margin-top: 3px;">~65k tries</div>
                </div>
                <div class="pattern-box" style="background: rgba(0,0,0,0.3); padding: 10px;
    border-radius: 4px; text-align: center;">
                    <div style="font-weight: bold; color: #F5F5DC; margin-bottom: 5px;">21e80</div>
                    <div style="color: #DC3545;">1 in 16M</div>
                    <div style="font-size: 9px; color: #6B7A6B; margin-top: 3px;">~1M tries</div>
                </div>
                <div class="pattern-box" style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-weight: bold; color: #F5F5DC; margin-bottom: 5px;">21e800</div>
                    <div style="color: #6F42C1;">1 in 256M</div>
                    <div style="font-size: 9px; color: #6B7A6B; margin-top: 3px;">~16M tries</div>
                </div>
                <div class="pattern-box" style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-weight: bold; color: #F5F5DC; margin-bottom: 5px;">21e8000</div>
                    <div style="color: #FFC107;">1 in 4B</div>
                    <div style="font-size: 9px; color: #6B7A6B; margin-top: 3px;">~268M tries</div>
                </div>
                <div class="pattern-box" style="background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px; text-align: center;">
                    <div style="font-weight: bold; color: #F5F5DC; margin-bottom: 5px;">21e80000</div>
                    <div style="color: #D4AF37;">1 in 68B</div>
                    <div style="font-size: 9px; color: #6B7A6B; margin-top: 3px;">~4B tries</div>
                </div>
            </div>
        </div>
        
        <!-- Hash Collision Demo -->
        <div style="background: rgba(0,0,0,0.2); border-radius: 8px; padding: 15px; margin-top: 15px;">
            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #9AB87A;">🔐 Collision Resistance Demo</h3>
            <p style="font-size: 11px; margin: 0 0 10px 0; color: #9AB87A; line-height: 1.5;">
                SHA256 produces 2^256 possible hashes. Finding a collision would take approximately 
                <span style="color: #D4AF37; font-weight: bold;">2^128 attempts</span> 
                (that's 340,282,366,920,938,463,463,374,607,431,768,211,456 hashes).
            </p>
            <div style="display: flex; gap: 10px; font-size: 10px;">
                <div style="flex: 1; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px;">
                    <div style="color: #9AB87A; margin-bottom: 5px;">All computers on Earth</div>
                    <div style="color: #F5F5DC;">Would take: <span style="color: #DC3545; font-weight: bold;">10^32 years</span></div>
                </div>
                <div style="flex: 1; background: rgba(0,0,0,0.3); padding: 10px; border-radius: 4px;">
                    <div style="color: #9AB87A; margin-bottom: 5px;">Biblical Timescales</div>
                    <div style="color: #F5F5DC;">Only: <span style="color: #28A745; font-weight: bold;">13.8 billion years</span></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Mining Activity -->
<div style="background: #F5F5DC; border: 2px solid #708B75; border-radius: 12px;">
    <div style="background: linear-gradient(135deg, #9AB87A, #708B75); color: #F5F5DC; padding: 15px 20px; border-radius: 10px 10px 0 0;">
        <h3 style="font-family: 'Nova Cut', serif; font-size: 18px; margin: 0; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
            🕐 Recent Mining Activity
        </h3>
    </div>
    
    <div style="padding: 25px;">
        <div style="background: #FFFACD; border-radius: 8px; padding: 20px; border: 1px solid #708B75;">
            @if(isset($stats['recent_mining']) && count($stats['recent_mining']) > 0)
                @foreach($stats['recent_mining'] as $activity)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid rgba(112, 139, 117, 0.2);">
                    <div style="flex-grow: 1;">
                        <div style="color: #3D315B; font-size: 13px; margin-bottom: 3px;">
                            {{ $activity['type'] === 'thread' ? '🧵' : '💬' }}
                            <strong>{{ Str::limit($activity['title'], 60) }}</strong>
                        </div>
                        <div style="color: #6B7A6B; font-size: 12px; text-transform: uppercase;">
                            /{{ $activity['board'] }}/ • {{ $activity['created_at']->diffForHumans() }}
                        </div>
                    </div>
                    <div style="background: #708B75; color: #F5F5DC; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">
                        ⚡{{ number_format($activity['points'], 1) }}
                    </div>
                </div>
                @endforeach
            @else
                <div style="text-align: center; color: #6B7A6B; font-style: italic; padding: 20px;">
                    No recent mining activity
                </div>
            @endif
        </div>
    </div>
</div>

<script nonce="{{ app('csp_nonce') }}">
// Activity Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: @json(collect($stats['daily_stats'] ?? [])->pluck('date')),
        datasets: [
            {
                label: 'Threads',
                data: @json(collect($stats['daily_stats'] ?? [])->pluck('threads')),
                borderColor: '#708B75',
                backgroundColor: 'rgba(112, 139, 117, 0.1)',
                tension: 0.3
            },
            {
                label: 'Posts',
                data: @json(collect($stats['daily_stats'] ?? [])->pluck('posts')),
                borderColor: '#9AB87A',
                backgroundColor: 'rgba(154, 184, 122, 0.1)',
                tension: 0.3
            },
            {
                label: 'Proofs',
                data: @json(collect($stats['daily_stats'] ?? [])->pluck('proofs')),
                borderColor: '#CD5C5C',
                backgroundColor: 'rgba(205, 92, 92, 0.1)',
                tension: 0.3
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Difficulty Distribution Chart
const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
const difficultyChart = new Chart(difficultyCtx, {
    type: 'doughnut',
    data: {
        labels: ['Easy (21)', 'Medium (21e)', 'Hard (21e8)', 'Extreme (21e88)'],
        datasets: [{
            data: [
                {{ $stats['difficulty_stats']['easy'] ?? 0 }},
                {{ $stats['difficulty_stats']['medium'] ?? 0 }},
                {{ $stats['difficulty_stats']['hard'] ?? 0 }},
                {{ $stats['difficulty_stats']['extreme'] ?? 0 }}
            ],
            backgroundColor: [
                '#9AB87A',
                '#708B75',
                '#CD5C5C',
                '#B87333'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Mining Brain Integration
let brainStatsInterval = null;
let miningBrain = null;

function initializeBrainIntegration() {
    // Check if mining brain is available
    if (window.haichanMiningBrain) {
        miningBrain = window.haichanMiningBrain;
        console.log('📊 STATS: Mining brain detected, starting live updates');
        
        // Update live stats immediately
        updateLiveBrainStats();
        
        // Start periodic updates
        brainStatsInterval = setInterval(updateLiveBrainStats, 2000);
        
        // Fetch enhanced server stats
        fetchEnhancedStats();
    } else {
        console.log('📊 STATS: Mining brain not detected, using fallback mode');
        // Retry in 2 seconds
        setTimeout(initializeBrainIntegration, 2000);
    }
}

function updateLiveBrainStats() {
    if (!miningBrain) return;
    
    try {
        const stats = miningBrain.state.sessionStats;
        const performance = miningBrain.state.performance;
        
        // Update live displays
        document.getElementById('live-hashrate').textContent = 
            stats.currentHashrate ? stats.currentHashrate.toLocaleString() : '--';
        document.getElementById('live-session-proofs').textContent = 
            stats.totalProofs ? stats.totalProofs.toString() : '--';
        document.getElementById('live-session-points').textContent = 
            stats.totalPoints ? stats.totalPoints.toFixed(1) : '--';
        document.getElementById('live-power-level').textContent = 
            miningBrain.state.power + '/10';
    } catch (error) {
        console.error('📊 STATS: Error updating live brain stats:', error);
    }
}

function fetchEnhancedStats() {
    fetch('/api/stats/brain')
        .then(response => response.json())
        .then(data => {
            console.log('📊 STATS: Enhanced brain stats received:', data);
            
            // Update enhanced mining performance
            if (data.server_stats) {
                document.getElementById('active-miners-count').textContent = 
                    data.server_stats.active_miners + ' active';
            }
            
            if (data.performance_metrics) {
                document.getElementById('mining-efficiency').textContent = 
                    data.performance_metrics.mining_efficiency.toFixed(2) + '/1000';
            }
            
            // Update difficulty chart with enhanced pattern data
            if (data.pattern_distribution && difficultyChart) {
                const patternData = data.pattern_distribution;
                difficultyChart.data.datasets[0].data = [
                    patternData.trivial + patternData.easy,
                    patternData.standard,
                    patternData.hard + patternData.very_hard,
                    patternData.extreme
                ];
                difficultyChart.update();
            }
        })
        .catch(error => {
            console.error('📊 STATS: Error fetching enhanced stats:', error);
        });
}

// Initialize brain integration when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initializeBrainIntegration, 1000);
});

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (brainStatsInterval) {
        clearInterval(brainStatsInterval);
    }
});

// Auto-refresh page every 5 minutes
setInterval(function() {
    if (!document.hidden && window.location.pathname.includes('/stats')) {
        window.location.reload();
    }
}, 300000); // 5 minutes
</script>

@endsection
