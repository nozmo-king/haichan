@extends('layout')

@section('title', 'Mining - Haichan')

@section('content')

<div style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    
    <!-- Mining Header -->
    <div style="background: var(--content-bg); border: 3px solid var(--accent-color); border-radius: 12px; padding: 25px; margin-bottom: 25px; text-align: center;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--accent-color); margin: 0 0 10px 0;">
            ⛏️ PROOF-OF-WORK MINING
        </h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin: 0;">
            Earn points through cryptographic work • Real SHA-256 hashing • Hyperpersistent rewards
        </p>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
        
        <!-- Global Stats -->
        <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px;">
            <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">🌍 Global Stats</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Proofs:</span>
                    <strong>{{ number_format($totalProofs) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Total Points:</span>
                    <strong>{{ number_format(\App\Models\ProofOfWork::sum('points')) }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Active Miners:</span>
                    <strong id="active-miners">{{ $activeSessions }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>All-Time Miners:</span>
                    <strong>{{ $totalMiners }}</strong>
                </div>
            </div>
        </div>

        <!-- Your Stats -->
        <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px;">
            <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">👤 Your Stats</h3>
            @if($user)
                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Username:</span>
                        <strong>{{ $user->username }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Points:</span>
                        <strong id="user-total-points">{{ number_format($user->total_pow_points) }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Level:</span>
                        <strong id="user-level">{{ $user->level }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Mining Power:</span>
                        <strong>{{ number_format($user->mining_power, 2) }}x</strong>
                    </div>
                </div>
            @else
                <div style="text-align: center; color: var(--text-secondary); padding: 20px 0;">
                    <p style="margin: 0 0 10px 0;">Login to track your mining stats!</p>
                    <a href="/login" style="display: inline-block; background: var(--accent-color); color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none;">Login</a>
                </div>
            @endif
        </div>

        <!-- Session Stats -->
        <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px;">
            <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">⏱️ This Session</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Proofs Mined:</span>
                    <strong id="session-proofs">0</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Points Earned:</span>
                    <strong id="session-points">0</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Hash Rate:</span>
                    <strong id="hash-rate">0 H/s</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Status:</span>
                    <strong id="mining-status" style="color: #999;">Idle</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Mining Interface -->
    <div class="mining-grid" style="display: grid; grid-template-columns: 1fr 350px; gap: 20px;">
        
        <!-- Main Mining Area -->
        <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 25px;">
            <h2 style="color: var(--accent-color); margin: 0 0 20px 0; font-size: 20px;">⚡ Mining Console</h2>
            
            <!-- Mining Controls -->
            <div style="margin-bottom: 20px;">
                <button id="start-mining-btn" style="background: #28A745; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; margin-right: 10px; transition: all 0.3s;">
                    ▶️ Start Mining
                </button>
                <button id="stop-mining-btn" style="background: #DC3545; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: bold; display: none; transition: all 0.3s;">
                    ⏸️ Stop Mining
                </button>
            </div>

            <!-- Mining Output -->
            <div style="background: #1a1a1a; color: #00ff00; border: 2px solid #333; border-radius: 6px; padding: 15px; font-family: 'Courier New', monospace; font-size: 12px; height: 400px; overflow-y: auto;" id="mining-output">
                <div style="color: #00ff00;">⛏️ Mining Console Ready...</div>
                <div style="color: #888;">Waiting for mining to start...</div>
            </div>

            <!-- Recent Proofs -->
            <div style="margin-top: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">📜 Recent Proofs (Last 24h)</h3>
                <div id="recent-proofs" style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                    @forelse($recentProofs->take(10) as $proof)
                        <div style="background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: 4px; padding: 10px; font-size: 11px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                <span style="color: var(--accent-color); font-weight: bold;">{{ $proof->user ? $proof->user->username : 'Anonymous' }}</span>
                                <span style="color: var(--text-secondary);">{{ $proof->created_at->diffForHumans() }}</span>
                            </div>
                            <div style="font-family: 'Courier New', monospace; color: var(--text-secondary); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ substr($proof->hash, 0, 16) }}...{{ substr($proof->hash, -16) }}
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                                <span>Pattern: <strong>{{ $proof->pattern }}</strong></span>
                                <span style="color: #D4AF37; font-weight: bold;">+{{ number_format($proof->points) }} pts</span>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--text-secondary); padding: 20px;">
                            No recent proofs. Start mining to see your work here!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Leaderboard Sidebar -->
        <div>
            <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">🏆 Top Miners</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($topMiners as $index => $miner)
                        <div style="display: flex; align-items: center; gap: 10px; padding: 8px; background: var(--secondary-bg); border-radius: 4px; font-size: 12px;">
                            <div style="font-size: 18px; font-weight: bold; color: {{ $index === 0 ? '#FFD700' : ($index === 1 ? '#C0C0C0' : ($index === 2 ? '#CD7F32' : 'var(--text-secondary)')) }};">
                                #{{ $index + 1 }}
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: bold; color: var(--text-primary);">{{ $miner->username }}</div>
                                <div style="color: var(--text-secondary); font-size: 10px;">{{ number_format($miner->total_pow_points) }} pts</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Mining Tips -->
            <div style="background: var(--content-bg); border: 2px solid var(--border-color); border-radius: 8px; padding: 20px;">
                <h3 style="color: var(--accent-color); margin: 0 0 15px 0; font-size: 16px;">💡 Mining Tips</h3>
                <div style="font-size: 12px; color: var(--text-secondary); line-height: 1.6;">
                    <p>• Mining uses real SHA-256 cryptography</p>
                    <p>• Points are awarded based on hash difficulty</p>
                    <p>• Higher difficulty = more points</p>
                    <p>• Keep mining to increase your level</p>
                    <p>• Use earned points in the shop</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script nonce="{{ app('csp_nonce') }}">
// Mining Interface
let miningActive = false;
let sessionProofs = 0;
let sessionPoints = 0;
let miningStartTime = null;

const startBtn = document.getElementById('start-mining-btn');
const stopBtn = document.getElementById('stop-mining-btn');
const output = document.getElementById('mining-output');

function log(message, color = '#00ff00') {
    const timestamp = new Date().toLocaleTimeString();
    const line = document.createElement('div');
    line.style.color = color;
    line.textContent = `[${timestamp}] ${message}`;
    output.appendChild(line);
    output.scrollTop = output.scrollHeight;
}

async function startContinuousMining() {
    if (!miningActive) return;
    
    try {
        // Create a virtual mining target
        const virtualTarget = document.createElement('div');
        virtualTarget.dataset.mineType = 'general';
        virtualTarget.dataset.boardCode = 'gen';
        
        if (window.simplePoW) {
            await window.simplePoW.startMining(virtualTarget);
            
            // Continue mining in a loop
            if (miningActive) {
                setTimeout(() => startContinuousMining(), 100);
            }
        }
    } catch (error) {
        log(`⚠️ Mining error: ${error.message}`, '#ff9900');
        if (miningActive) {
            setTimeout(() => startContinuousMining(), 2000);
        }
    }
}

startBtn.addEventListener('click', () => {
    if (miningActive) return;
    
    miningActive = true;
    miningStartTime = Date.now();
    sessionProofs = 0;
    sessionPoints = 0;
    
    startBtn.style.display = 'none';
    stopBtn.style.display = 'inline-block';
    
    document.getElementById('mining-status').textContent = 'Mining...';
    document.getElementById('mining-status').style.color = '#28A745';
    
    log('⛏️ Mining started!', '#00ff00');
    log('Using SHA-256 proof-of-work algorithm...', '#888');
    
    // Wait for mining system to be available
    function waitForMiningSystem() {
        if (window.simplePoW) {
            log('✅ Mining system active', '#00ff00');
            startContinuousMining();
        } else {
            log('⏳ Waiting for mining system to initialize...', '#ff9900');
            setTimeout(waitForMiningSystem, 1000);
        }
    }
    
    waitForMiningSystem();
});

stopBtn.addEventListener('click', () => {
    if (!miningActive) return;
    
    miningActive = false;
    
    startBtn.style.display = 'inline-block';
    stopBtn.style.display = 'none';
    
    document.getElementById('mining-status').textContent = 'Idle';
    document.getElementById('mining-status').style.color = '#999';
    
    log('⏸️ Mining stopped', '#ff9900');
    
    if (window.simplePoW) {
        window.simplePoW.stopMining();
    }
});

// Listen for mining events
document.addEventListener('proofSubmitted', (event) => {
    const { points, hash, pattern, total_points } = event.detail;
    
    sessionProofs++;
    sessionPoints += points;
    
    // Update displays
    document.getElementById('session-proofs').textContent = sessionProofs;
    document.getElementById('session-points').textContent = number_format(sessionPoints);
    
    if (total_points) {
        document.getElementById('user-total-points').textContent = number_format(total_points);
    }
    
    if (user_level) {
        const levelEl = document.getElementById('user-level');
        if (levelEl) {
            levelEl.textContent = user_level;
        }
    }
    
    // Calculate hash rate
    if (miningStartTime) {
        const elapsed = (Date.now() - miningStartTime) / 1000;
        const hashRate = (sessionProofs / elapsed).toFixed(2);
        document.getElementById('hash-rate').textContent = hashRate + ' H/s';
    }
    
    // Log to console
    log(`✅ Proof accepted! +${points} points`, '#00ff00');
    log(`   Hash: ${hash.substring(0, 16)}...${hash.substring(48)}`, '#888');
    log(`   Pattern: ${pattern} | Total: ${number_format(total_points || 0)}`, '#888');
});

document.addEventListener('miningError', (event) => {
    log(`❌ Error: ${event.detail.message}`, '#ff0000');
});

// Helper function for number formatting
function number_format(number) {
    return new Intl.NumberFormat().format(number);
}

// Auto-refresh stats every 30 seconds
setInterval(() => {
    fetch('/api/mining/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                document.getElementById('user-total-points').textContent = number_format(data.user.total_points);
                document.getElementById('user-level').textContent = data.user.level;
            }
        })
        .catch(error => console.error('Stats refresh error:', error));
}, 30000);

// Button hover effects
startBtn.addEventListener('mouseenter', () => {
    startBtn.style.transform = 'translateY(-2px)';
    startBtn.style.boxShadow = '0 4px 12px rgba(40, 167, 69, 0.4)';
});
startBtn.addEventListener('mouseleave', () => {
    startBtn.style.transform = 'translateY(0)';
    startBtn.style.boxShadow = 'none';
});

stopBtn.addEventListener('mouseenter', () => {
    stopBtn.style.transform = 'translateY(-2px)';
    stopBtn.style.boxShadow = '0 4px 12px rgba(220, 53, 69, 0.4)';
});
stopBtn.addEventListener('mouseleave', () => {
    stopBtn.style.transform = 'translateY(0)';
    stopBtn.style.boxShadow = 'none';
});
</script>

<style>
/* Responsive Design */
@media (max-width: 1024px) {
    .mining-grid {
        grid-template-columns: 1fr !important;
    }
}

@media (max-width: 768px) {
    #start-mining-btn,
    #stop-mining-btn {
        width: 100%;
        margin: 5px 0;
    }
    
    #mining-output {
        height: 250px !important;
    }
}

/* Mining output animations */
#mining-output::-webkit-scrollbar {
    width: 8px;
}

#mining-output::-webkit-scrollbar-track {
    background: #0a0a0a;
}

#mining-output::-webkit-scrollbar-thumb {
    background: #333;
    border-radius: 4px;
}

#mining-output::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Pulse animation for status */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

#mining-status.mining {
    animation: pulse 2s ease-in-out infinite;
}
</style>

@endsection
