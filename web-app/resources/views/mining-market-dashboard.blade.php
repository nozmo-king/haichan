@extends('layout')

@section('title', 'Haichan - Live Mining Market')

@section('content')

<!-- Magic Numbers Cascade -->
<div class="magic-numbers-stream" style="position: fixed; top: 0; right: 20px; width: 160px; height: 100vh; pointer-events: none; z-index: 1; overflow: hidden;">
    <div class="number-cascade" id="numbers-cascade" style="font-family: 'Courier New', monospace; font-size: 7px; color: rgba(112, 139, 117, 0.12); line-height: 1.1; white-space: pre;">
        <!-- Numbers will be populated by JavaScript -->
    </div>
</div>

<!-- Enhanced Navigation -->
<div class="haichan-nav" style="position: sticky; top: 0; background: linear-gradient(135deg, #FFFACD 0%, #F5F5DC 100%); border-bottom: 1px solid #708B75; padding: 12px 0; z-index: 100; backdrop-filter: blur(2px);">
    <div class="nav-container" style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 20px;">
        <div class="nav-primary" style="display: flex; gap: 8px;">
            <a href="{{ route('forum.index') }}" class="nav-item active">🏠 Market</a>
            <a href="{{ route('mining.dashboard') }}" class="nav-item">⛏️ Range</a>
            <a href="/chat" class="nav-item">💬 Chat</a>
            <a href="/shop" class="nav-item">🛒 Shop</a>
            <a href="{{ route('bounty') }}" class="nav-item">🏆 Bounty</a>
        </div>
        <div class="nav-mining-summary" id="mini-mining-display" style="font-family: 'Courier New', monospace; font-size: 10px; color: #708B75; display: flex; align-items: center; gap: 15px;">
            <span class="mining-status-compact" id="nav-status">⚡ 0 H/s</span>
            <span class="mining-heartbeat" id="nav-heartbeat">💚</span>
        </div>
    </div>
</div>

<!-- Market Hero Section -->
<div class="market-hero" style="background: linear-gradient(135deg, #F5F5DC 0%, #FFFACD 50%, #F0F8FF 100%); border: 2px solid #708B75; border-radius: 12px; padding: 30px 20px; text-align: center; margin: 20px auto; max-width: 1200px; position: relative; overflow: hidden;">
    
    <!-- Market Status Indicator -->
    <div class="market-status" style="position: absolute; top: 15px; right: 20px; display: flex; align-items: center; gap: 8px; font-size: 11px; color: #708B75;">
        <div class="status-dot" id="market-status-dot" style="width: 8px; height: 8px; border-radius: 50%; background: #28A745; animation: gentle-pulse 2s ease-in-out infinite;"></div>
        <span>MARKET LIVE</span>
    </div>

    <!-- Main Market Display -->
    <h1 style="font-family: 'Nova Cut', serif; font-size: 28px; color: #3D315B; margin: 0 0 8px 0; text-shadow: 1px 1px 2px rgba(0,0,0,0.05);">
        ⚡ 21e8 MINING MARKET
    </h1>
    <p style="color: #6B7A6B; font-size: 14px; max-width: 500px; margin: 0 auto 25px auto;">
        Live proof-of-work trading floor • Hyperconsistent • Hyperpersistent • Secure
    </p>

    <!-- Real-time Market Metrics -->
    <div class="market-ticker" style="display: flex; justify-content: center; gap: 25px; flex-wrap: wrap; margin-bottom: 20px; font-family: 'Courier New', monospace;">
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="global-hashrate" style="font-size: 18px; font-weight: bold; color: #D4AF37;">0</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">H/sec Global</div>
        </div>
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="magic-discovered" style="font-size: 18px; font-weight: bold; color: #DC3545;">0</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">21e8 Found</div>
        </div>
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="active-miners" style="font-size: 18px; font-weight: bold; color: #28A745;">{{ $activeSessions }}</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">Miners</div>
        </div>
        <div class="ticker-item" style="text-align: center;">
            <div class="ticker-value" id="market-cap" style="font-size: 18px; font-weight: bold; color: #6F42C1;">{{ number_format(\App\Models\ProofOfWork::sum('points')) }}</div>
            <div class="ticker-label" style="font-size: 9px; color: #6B7A6B; text-transform: uppercase; letter-spacing: 0.5px;">Points</div>
        </div>
    </div>

    <!-- Mining Difficulty Weather -->
    <div class="difficulty-weather" style="background: rgba(255,255,255,0.3); border-radius: 6px; padding: 12px; margin-top: 20px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; font-size: 12px;">
            <span>Difficulty Weather:</span>
            <div class="weather-display" id="mining-weather" style="display: flex; align-items: center; gap: 8px;">
                <span class="weather-icon">⛅</span>
                <span class="weather-text" style="font-family: 'Courier New', monospace; color: #708B75;">Moderate</span>
            </div>
            <span>|</span>
            <span>Next 21e8 Storm: <span id="storm-countdown" style="font-family: 'Courier New', monospace; color: #D4AF37;">--:--</span></span>
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
            @php
                $recentThreads = \App\Models\Thread::with(['board', 'bitcoinUser'])
                    ->orderBy('created_at', 'desc')
                    ->take(10)
                    ->get();
            @endphp
            
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

<!-- Floating Mining Insights (appears during active mining) -->
<div id="mining-insights" class="floating-panel" style="
    position: fixed;
    bottom: 80px;
    right: 20px;
    background: rgba(245, 245, 220, 0.95);
    border: 1px solid #708B75;
    border-radius: 6px;
    padding: 12px;
    font-size: 9px;
    max-width: 180px;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.3s ease;
    backdrop-filter: blur(2px);
    z-index: 50;
">
    <div style="font-weight: bold; margin-bottom: 8px; color: #3D315B; font-size: 10px;">⛏️ Mining Analysis</div>
    <div class="insight-row" style="display: flex; justify-content: space-between; margin-bottom: 4px;">
        <span>Target Efficiency:</span>
        <span class="value" id="target-efficiency" style="font-family: 'Courier New', monospace; color: #708B75;">--</span>
    </div>
    <div class="insight-row" style="display: flex; justify-content: space-between; margin-bottom: 4px;">
        <span>Pattern Quality:</span>
        <span class="value" id="pattern-quality" style="font-family: 'Courier New', monospace; color: #708B75;">--</span>
    </div>
    <div class="insight-row" style="display: flex; justify-content: space-between;">
        <span>21e8 Proximity:</span>
        <span class="value glow-text" id="magic-proximity" style="font-family: 'Courier New', monospace; color: #D4AF37;">--</span>
    </div>
</div>

<!-- Enhanced Styles -->
<style>
.nav-item {
    color: #3D315B;
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.nav-item:hover {
    background: rgba(112, 139, 117, 0.1);
    border-color: #9AB87A;
    color: #708B75;
    transform: translateY(-1px);
}

.nav-item.active {
    background: #708B75;
    color: #FFFACD;
    border-color: #708B75;
}

@keyframes gentle-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

@keyframes cascade {
    0% { transform: translateY(-100px); opacity: 0; }
    5% { opacity: 0.3; }
    95% { opacity: 0.3; }
    100% { transform: translateY(100vh); opacity: 0; }
}

.mining-thread:hover {
    border-color: #708B75;
    box-shadow: 0 2px 8px rgba(112, 139, 117, 0.1);
}

.mining-thread:hover .mining-aura {
    opacity: 1;
}

.mining-thread:hover .mining-status-indicator {
    background: #D4AF37 !important;
    animation: gentle-pulse 1s ease-in-out infinite;
}

.board-tab:hover {
    background: rgba(112, 139, 117, 0.2);
    border-color: #708B75;
    transform: translateY(-1px);
}

.board-tab.active {
    background: #708B75;
    color: white;
    border-color: #708B75;
}

.glow-text {
    text-shadow: 0 0 4px currentColor;
}

.proof-found-celebration {
    animation: success-flash 0.5s ease-out;
}

@keyframes success-flash {
    0% { background: rgba(154, 184, 122, 0); }
    50% { background: rgba(154, 184, 122, 0.3); }
    100% { background: rgba(154, 184, 122, 0); }
}

@media (max-width: 768px) {
    .magic-numbers-stream { display: none; }
    #mining-insights { display: none; }
    
    .trading-floor {
        grid-template-columns: 1fr;
        padding: 0 10px;
    }
    
    .market-ticker {
        gap: 15px !important;
    }
    
    .board-tabs {
        gap: 4px;
    }
    
    .board-tab {
        font-size: 9px;
        padding: 4px 8px;
    }
    
    .nav-container {
        flex-direction: column;
        gap: 10px;
    }
}
</style>

<!-- Enhanced Mining Market JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔮 Mining Market Dashboard initializing...');
    
    // Magic Numbers Cascade Effect
    function initializeMagicNumbers() {
        const cascade = document.getElementById('numbers-cascade');
        if (!cascade) return;
        
        let numbers = '';
        for (let i = 0; i < 100; i++) {
            const hash = '21e8' + Math.random().toString(16).substring(2, 64);
            numbers += hash + '\n';
        }
        cascade.textContent = numbers;
        cascade.style.animation = 'cascade 45s linear infinite';
        
        // Regenerate periodically
        setInterval(() => {
            let newNumbers = '';
            for (let i = 0; i < 100; i++) {
                const hash = '21e8' + Math.random().toString(16).substring(2, 64);
                newNumbers += hash + '\n';
            }
            cascade.textContent = newNumbers;
        }, 45000);
    }
    
    // Real-time Market Updates
    function updateMarketStats() {
        // Simulate real-time data updates
        const globalHashrate = document.getElementById('global-hashrate');
        const magicDiscovered = document.getElementById('magic-discovered');
        const avgDiscoveryTime = document.getElementById('avg-discovery-time');
        const networkHashrate = document.getElementById('network-hashrate');
        
        if (globalHashrate) {
            const hashrate = Math.floor(Math.random() * 1000) + 500;
            globalHashrate.textContent = hashrate;
        }
        
        if (magicDiscovered) {
            const discovered = Math.floor(Math.random() * 10) + 5;
            magicDiscovered.textContent = discovered;
        }
        
        if (avgDiscoveryTime) {
            const minutes = Math.floor(Math.random() * 15) + 5;
            avgDiscoveryTime.textContent = minutes + 'm';
        }
        
        if (networkHashrate) {
            const rate = (Math.random() * 2 + 0.5).toFixed(1);
            networkHashrate.textContent = rate + ' KH/s';
        }
    }
    
    // Mining Thread Interactions
    function setupThreadMining() {
        const threads = document.querySelectorAll('.mining-thread');
        threads.forEach(thread => {
            thread.addEventListener('mouseenter', () => {
                if (window.mouseoverMiner && window.mouseoverMiner.enabled) {
                    const statusIndicator = thread.querySelector('.mining-status-indicator');
                    if (statusIndicator) {
                        statusIndicator.style.background = '#D4AF37';
                        statusIndicator.style.animation = 'gentle-pulse 1s ease-in-out infinite';
                    }
                    
                    // Show mining insights
                    const insights = document.getElementById('mining-insights');
                    if (insights) {
                        insights.style.opacity = '1';
                        insights.style.transform = 'translateY(0)';
                        
                        // Update insight values
                        document.getElementById('target-efficiency').textContent = (Math.random() * 100).toFixed(1) + '%';
                        document.getElementById('pattern-quality').textContent = ['High', 'Med', 'Low'][Math.floor(Math.random() * 3)];
                        document.getElementById('magic-proximity').textContent = (Math.random() * 0.1).toFixed(3);
                    }
                }
            });
            
            thread.addEventListener('mouseleave', () => {
                const statusIndicator = thread.querySelector('.mining-status-indicator');
                if (statusIndicator) {
                    statusIndicator.style.background = '#6C757D';
                    statusIndicator.style.animation = 'none';
                }
                
                // Hide mining insights after delay
                setTimeout(() => {
                    const insights = document.getElementById('mining-insights');
                    if (insights) {
                        insights.style.opacity = '0';
                        insights.style.transform = 'translateY(10px)';
                    }
                }, 2000);
            });
        });
    }
    
    // Board Tab Switching
    function setupBoardTabs() {
        const tabs = document.querySelectorAll('.board-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active state from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active state to clicked tab
                tab.classList.add('active');
                
                // Filter threads by board (simplified)
                const boardCode = tab.dataset.board;
                const threads = document.querySelectorAll('.mining-thread');
                threads.forEach(thread => {
                    if (boardCode === 'all' || thread.dataset.boardCode === boardCode) {
                        thread.style.display = 'block';
                    } else {
                        thread.style.display = 'none';
                    }
                });
            });
        });
        
        // Set first tab as active by default
        if (tabs.length > 0) {
            tabs[0].classList.add('active');
        }
    }
    
    // Initialize all components
    initializeMagicNumbers();
    setupThreadMining();
    setupBoardTabs();
    
    // Start real-time updates
    updateMarketStats();
    setInterval(updateMarketStats, 15000); // Update every 15 seconds
    
    console.log('✅ Mining Market Dashboard ready');
});

// Integration with existing mining system
window.addEventListener('mouseoverMinerReady', (event) => {
    console.log('🔌 Mining system connected to market dashboard');
    
    // Update navigation status
    const navStatus = document.getElementById('nav-status');
    const navHeartbeat = document.getElementById('nav-heartbeat');
    
    if (navStatus && navHeartbeat) {
        setInterval(() => {
            if (window.mouseoverMiner) {
                const hashrate = window.mouseoverMiner.stats ? window.mouseoverMiner.stats.hashes * 2 : 0;
                navStatus.textContent = `⚡ ${hashrate} H/s`;
                
                if (hashrate > 0) {
                    navHeartbeat.textContent = '💚';
                    navHeartbeat.style.animation = 'gentle-pulse 1s ease-in-out infinite';
                } else {
                    navHeartbeat.textContent = '🤍';
                    navHeartbeat.style.animation = 'none';
                }
            }
        }, 2000);
    }
});
</script>

@endsection