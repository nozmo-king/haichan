@extends('layout')

@section('title', 'Mining Dashboard - Haichan')

@section('content')
<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <!-- Header -->
    <div style="text-align: center; margin-bottom: 40px;">
        <h1 style="font-family: 'Nova Cut', serif; font-size: 36px; color: var(--text-primary); margin: 0 0 10px 0;">
            ⛏️ HAICHAN MINING
        </h1>
        <p style="color: var(--text-secondary); font-size: 16px;">
            Proof-of-Work mining dashboard • Mine hashes • Earn points • Climb the leaderboard
        </p>
    </div>

    <!-- Mining Stats Overview -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 2px solid var(--border-color); text-align: center;">
            <div style="color: var(--accent-color); font-size: 24px; font-weight: bold; margin-bottom: 5px;" id="user-points">
                {{ session('bitcoin_auth_user') ? number_format(session('bitcoin_auth_user')->total_pow_points) : '0' }}
            </div>
            <div style="color: var(--text-secondary); font-size: 12px;">YOUR POINTS</div>
        </div>
        
        <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 2px solid var(--border-color); text-align: center;">
            <div style="color: var(--text-primary); font-size: 24px; font-weight: bold; margin-bottom: 5px;" id="user-level">
                {{ session('bitcoin_auth_user') ? session('bitcoin_auth_user')->level : '1' }}
            </div>
            <div style="color: var(--text-secondary); font-size: 12px;">MINING LEVEL</div>
        </div>
        
        <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 2px solid var(--border-color); text-align: center;">
            <div style="color: #4CAF50; font-size: 24px; font-weight: bold; margin-bottom: 5px;" id="session-hashes">0</div>
            <div style="color: var(--text-secondary); font-size: 12px;">HASHES THIS SESSION</div>
        </div>
        
        <div style="background: var(--content-bg); padding: 20px; border-radius: 12px; border: 2px solid var(--border-color); text-align: center;">
            <div style="color: #FF9800; font-size: 24px; font-weight: bold; margin-bottom: 5px;" id="session-proofs">0</div>
            <div style="color: var(--text-secondary); font-size: 12px;">PROOFS FOUND</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        
        <!-- Personal Mining Section -->
        <div style="background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
            <h2 style="color: var(--text-primary); font-size: 20px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                🏠 Personal Mining
            </h2>
            
            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px; line-height: 1.4;">
                Mine hashes for your own account. Find rare patterns to earn bonus points!
            </p>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Mining Target:
                </label>
                <select id="personal-target" style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary);">
                    <option value="account">My Account ({{ session('bitcoin_auth_user') ? session('bitcoin_auth_user')->username : 'Anonymous' }})</option>
                    <option value="profile">Profile Hash</option>
                    <option value="signature">Signature Hash</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Difficulty:
                </label>
                <select id="personal-difficulty" style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary);">
                    <option value="21">21 - Easy (0.1 pts)</option>
                    <option value="21e">21e - Medium (0.5 pts)</option>
                    <option value="21e8" selected>21e8 - Hard (100 pts)</option>
                    <option value="21e80">21e80 - Very Hard (500 pts)</option>
                    <option value="21e800">21e800 - Extreme (2500 pts)</option>
                </select>
            </div>
            
            <button id="personal-mine-btn" onclick="startPersonalMining()" 
                    style="width: 100%; background: linear-gradient(135deg, #4CAF50, #45a049); color: white; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s;">
                🚀 START PERSONAL MINING
            </button>
            
            <div id="personal-status" style="margin-top: 15px; padding: 10px; background: var(--secondary-bg); border-radius: 6px; font-size: 12px; color: var(--text-secondary); min-height: 20px;">
                Ready to mine personal hashes
            </div>
        </div>

        <!-- Thread/Post Mining Section -->
        <div style="background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
            <h2 style="color: var(--text-primary); font-size: 20px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                💬 Thread/Post Mining
            </h2>
            
            <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px; line-height: 1.4;">
                Mine existing threads and posts to boost their scores and earn points!
            </p>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Target Type:
                </label>
                <select id="target-type" style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary);">
                    <option value="thread">Thread</option>
                    <option value="post">Post</option>
                    <option value="random">Random Target</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Target ID:
                </label>
                <input type="number" id="target-id" placeholder="Enter thread/post ID..." 
                       style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); box-sizing: border-box;">
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Mining Difficulty:
                </label>
                <select id="target-difficulty" style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary);">
                    <option value="21">21 - Easy (0.1 pts)</option>
                    <option value="21e">21e - Medium (0.5 pts)</option>
                    <option value="21e8" selected>21e8 - Hard (100 pts)</option>
                    <option value="21e80">21e80 - Very Hard (500 pts)</option>
                    <option value="21e800">21e800 - Extreme (2500 pts)</option>
                </select>
            </div>
            
            <button id="target-mine-btn" onclick="startTargetMining()" 
                    style="width: 100%; background: linear-gradient(135deg, #FF9800, #F57C00); color: white; border: none; padding: 15px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s;">
                ⛏️ START TARGET MINING
            </button>
            
            <div id="target-status" style="margin-top: 15px; padding: 10px; background: var(--secondary-bg); border-radius: 6px; font-size: 12px; color: var(--text-secondary); min-height: 20px;">
                Ready to mine targets
            </div>
        </div>
    </div>

    <!-- Mining Console -->
    <div style="margin-top: 30px; background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
        <h2 style="color: var(--text-primary); font-size: 20px; margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">
            📊 Mining Console
        </h2>
        
        <div style="background: #2d5016 !important; color: #ffffff !important; font-family: 'Courier New', monospace; padding: 20px; border-radius: 8px; height: 300px; overflow-y: auto; font-size: 14px; border: 2px solid #7ba05b;" id="mining-console">
            <div>🔨 HAICHAN MINING CONSOLE INITIALIZED</div>
            <div>⚡ Ready for mining operations...</div>
            <div>💎 Find rare patterns for bonus points!</div>
            <div style="margin-top: 10px;">---</div>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button onclick="clearConsole()" style="background: #666; color: white; border: none; padding: 8px 15px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                Clear Console
            </button>
            <button onclick="exportMiningLog()" style="background: var(--accent-color); color: white; border: none; padding: 8px 15px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                Export Log
            </button>
            <div style="margin-left: auto; color: var(--text-secondary); font-size: 12px; display: flex; align-items: center;">
                Hash Rate: <span id="hash-rate" style="color: var(--accent-color); font-weight: bold; margin-left: 5px;">0 H/s</span>
            </div>
        </div>
    </div>

    <!-- Shooting Range Section -->
    <div style="margin-top: 30px; background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
        <h2 style="color: var(--text-primary); font-size: 20px; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
            🎯 Hash Shooting Range
        </h2>
        
        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 20px;">
            Practice your mining skills! Try different targets, patterns, and challenges. Each successful "shot" earns points!
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
            <button onclick="startChallengeMode('speed')" class="challenge-btn" style="background: linear-gradient(135deg, #2196F3, #1976D2); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                ⚡ Speed Challenge<br>
                <small style="opacity: 0.8;">Find 10 hashes ASAP</small>
            </button>
            
            <button onclick="startChallengeMode('rare')" class="challenge-btn" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                💎 Rare Hunter<br>
                <small style="opacity: 0.8;">Find special patterns</small>
            </button>
            
            <button onclick="startChallengeMode('endurance')" class="challenge-btn" style="background: linear-gradient(135deg, #FF5722, #D84315); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                🔥 Endurance<br>
                <small style="opacity: 0.8;">Mine for 10 minutes</small>
            </button>
            
            <button onclick="startChallengeMode('lucky')" class="challenge-btn" style="background: linear-gradient(135deg, #4CAF50, #388E3C); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                🍀 Lucky Dip<br>
                <small style="opacity: 0.8;">Random difficulties</small>
            </button>
            
            <button onclick="startChallengeMode('sniper')" class="challenge-btn" style="background: linear-gradient(135deg, #607D8B, #455A64); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                🎯 Sniper Mode<br>
                <small style="opacity: 0.8;">Precision targets</small>
            </button>
            
            <button onclick="startChallengeMode('chaos')" class="challenge-btn" style="background: linear-gradient(135deg, #795548, #5D4037); color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">
                💀 Chaos Mode<br>
                <small style="opacity: 0.8;">Extreme difficulty</small>
            </button>
        </div>
        
        <div id="challenge-status" style="background: var(--secondary-bg); padding: 15px; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 12px;">
            <div style="color: var(--accent-color); font-weight: bold; margin-bottom: 10px;">🎯 READY TO SHOOT</div>
            <div style="color: var(--text-secondary);">Select a challenge mode above to start your hash shooting session!</div>
        </div>
    </div>

    <!-- Target Practice Grid -->
    <div style="margin-top: 30px; background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
        <h2 style="color: var(--text-primary); font-size: 20px; margin: 0 0 20px 0;">
            🎲 Target Practice Grid
        </h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; margin-bottom: 20px;" id="target-grid">
            <!-- Target grid will be populated by JavaScript -->
        </div>
        
        <div style="display: flex; gap: 10px; margin-bottom: 15px;">
            <button onclick="generateNewTargets()" style="background: var(--accent-color); color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                🎲 New Targets
            </button>
            <button onclick="clearTargetGrid()" style="background: #666; color: white; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer;">
                🧹 Clear Grid
            </button>
            <div style="margin-left: auto; color: var(--text-secondary); display: flex; align-items: center;">
                Targets Hit: <span id="targets-hit" style="color: var(--accent-color); font-weight: bold; margin-left: 5px;">0</span>
            </div>
        </div>
    </div>

    <!-- Hash Pattern Lab -->
    <div style="margin-top: 30px; background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
        <h2 style="color: var(--text-primary); font-size: 20px; margin: 0 0 20px 0;">
            🧪 Hash Pattern Laboratory
        </h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div>
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Custom Pattern:
                </label>
                <input type="text" id="custom-pattern" placeholder="e.g., deadbeef, 1337, 000" 
                       style="width: 100%; padding: 10px; border: 2px solid var(--border-color); border-radius: 6px; background: var(--primary-bg); color: var(--text-primary); font-family: monospace; box-sizing: border-box;">
                
                <button onclick="mineCustomPattern()" style="width: 100%; margin-top: 10px; background: linear-gradient(135deg, #E91E63, #C2185B); color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                    🔬 Mine Custom Pattern
                </button>
            </div>
            
            <div>
                <label style="display: block; color: var(--text-primary); font-weight: bold; margin-bottom: 8px;">
                    Famous Patterns:
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <button onclick="mineCustomPattern('deadbeef')" class="pattern-btn">💀 DEADBEEF</button>
                    <button onclick="mineCustomPattern('1337')" class="pattern-btn">😎 1337</button>
                    <button onclick="mineCustomPattern('000')" class="pattern-btn">⚫ 000</button>
                    <button onclick="mineCustomPattern('666')" class="pattern-btn">😈 666</button>
                    <button onclick="mineCustomPattern('c0de')" class="pattern-btn">💻 C0DE</button>
                    <button onclick="mineCustomPattern('beef')" class="pattern-btn">🥩 BEEF</button>
                </div>
            </div>
        </div>
        
        <div id="pattern-lab-status" style="margin-top: 15px; padding: 10px; background: var(--secondary-bg); border-radius: 6px; font-size: 12px; color: var(--text-secondary);">
            Enter a custom pattern or try famous ones above!
        </div>
    </div>

    <!-- Recent Finds & Leaderboard -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 30px;">
        
        <!-- Recent Finds -->
        <div style="background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
            <h2 style="color: var(--text-primary); font-size: 18px; margin: 0 0 15px 0;">
                🎯 Recent Finds
            </h2>
            <div id="recent-finds" style="max-height: 200px; overflow-y: auto;">
                <div style="color: var(--text-secondary); text-align: center; padding: 20px;">
                    Start mining to see your recent proof-of-work finds here!
                </div>
            </div>
        </div>

        <!-- Achievements & Stats -->
        <div style="background: var(--content-bg); padding: 25px; border-radius: 12px; border: 2px solid var(--border-color);">
            <h2 style="color: var(--text-primary); font-size: 18px; margin: 0 0 15px 0;">
                🏅 Achievements
            </h2>
            <div id="achievements" style="max-height: 200px; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--border-color);">
                    <span>🎯 First Shot</span>
                    <span style="color: #999;">Locked</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--border-color);">
                    <span>💎 Rare Hunter</span>
                    <span style="color: #999;">Locked</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--border-color);">
                    <span>⚡ Speed Demon</span>
                    <span style="color: #999;">Locked</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 5px 0; border-bottom: 1px solid var(--border-color);">
                    <span>🔥 Endurance Champion</span>
                    <span style="color: #999;">Locked</span>
                </div>
                <div style="color: var(--text-secondary); text-align: center; padding: 10px; font-size: 12px;">
                    Complete challenges to unlock achievements!
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load PoW mining system -->
<script src="/js/simple-pow.js"></script>

<script>
class MiningDashboard {
    constructor() {
        this.isPersonalMining = false;
        this.isTargetMining = false;
        this.sessionStats = {
            hashes: 0,
            proofs: 0,
            points: 0,
            startTime: Date.now()
        };
        this.hashRateInterval = null;
        
        this.init();
    }
    
    init() {
        this.updateHashRate();
        this.loadLeaderboard();
        this.setupAutoRefresh();
        
        // Make mining system available globally
        if (window.simplePoW) {
            this.miningSystem = window.simplePoW;
        } else {
            console.warn('Simple PoW not available, creating fallback');
            this.createFallbackMiner();
        }
    }
    
    createFallbackMiner() {
        this.miningSystem = {
            acquireProofFor: async (payload) => {
                // Direct challenge and mining
                const response = await fetch('/api/mining/challenges', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify(payload)
                });
                
                const challenge = await response.json();
                if (!challenge.success) throw new Error(challenge.message);
                
                // Simple mining
                const proof = await this.mineSimple(challenge.canonical_payload, payload.difficulty);
                return {
                    nonce: proof.nonce,
                    hash: proof.hash,
                    challenge_id: challenge.token
                };
            }
        };
    }
    
    async mineSimple(canonicalPayload, difficulty) {
        const data = JSON.stringify(canonicalPayload);
        let nonce = 0;
        
        while (nonce < 1000000) {
            const hashInput = data + ':' + nonce;
            const hashBuffer = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(hashInput));
            const hashArray = new Uint8Array(hashBuffer);
            const hash = Array.from(hashArray).map(b => b.toString(16).padStart(2, '0')).join('');
            
            this.sessionStats.hashes++;
            
            if (hash.toLowerCase().startsWith(difficulty.toLowerCase())) {
                return { nonce, hash };
            }
            
            nonce++;
        }
        
        throw new Error('Mining failed after 1M attempts');
    }
    
    setupAutoRefresh() {
        // Update stats every 2 seconds
        setInterval(() => {
            this.updateSessionDisplay();
        }, 2000);
        
        // Refresh user points from server every 10 seconds
        setInterval(() => {
            refreshUserStats();
        }, 10000);
        
        // Update leaderboard every 30 seconds
        setInterval(() => {
            this.loadLeaderboard();
        }, 30000);
    }
    
    updateHashRate() {
        const elapsed = (Date.now() - this.sessionStats.startTime) / 1000;
        const rate = elapsed > 0 ? Math.round(this.sessionStats.hashes / elapsed) : 0;
        
        const hashRateEl = document.getElementById('hash-rate');
        if (hashRateEl) {
            hashRateEl.textContent = rate.toLocaleString() + ' H/s';
        }
    }
    
    updateSessionDisplay() {
        document.getElementById('session-hashes').textContent = this.sessionStats.hashes.toLocaleString();
        document.getElementById('session-proofs').textContent = this.sessionStats.proofs.toLocaleString();
        this.updateHashRate();
    }
    
    logToConsole(message, type = 'info') {
        const console = document.getElementById('mining-console');
        const timestamp = new Date().toLocaleTimeString();
        const prefix = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
        
        const line = document.createElement('div');
        line.innerHTML = `[${timestamp}] ${prefix} ${message}`;
        if (type === 'success') line.style.color = '#90ee90';
        if (type === 'error') line.style.color = '#ff6b6b';
        if (type === 'warning') line.style.color = '#ffd93d';
        if (type === 'info') line.style.color = '#ffffff';
        
        console.appendChild(line);
        console.scrollTop = console.scrollHeight;
    }
    
    async loadLeaderboard() {
        try {
            // For now, show a placeholder leaderboard
            const leaderboard = document.getElementById('leaderboard');
            leaderboard.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span>1. {{ session('bitcoin_auth_user') ? session('bitcoin_auth_user')->username : 'You' }}</span>
                    <span style="color: var(--accent-color);">{{ session('bitcoin_auth_user') ? number_format(session('bitcoin_auth_user')->total_pow_points) : '0' }} pts</span>
                </div>
                <div style="color: var(--text-secondary); text-align: center; padding: 10px;">
                    Start mining to compete!
                </div>
            `;
        } catch (error) {
            console.error('Failed to load leaderboard:', error);
        }
    }
    
    addRecentFind(hash, points, pattern) {
        const container = document.getElementById('recent-finds');
        const find = document.createElement('div');
        find.style.cssText = 'display: flex; justify-content: space-between; padding: 8px; border-bottom: 1px solid var(--border-color); font-family: monospace; font-size: 11px;';
        
        const hashShort = hash.substring(0, 12) + '...';
        find.innerHTML = `
            <span style="color: var(--text-primary);">${hashShort}</span>
            <span style="color: var(--accent-color); font-weight: bold;">+${points} pts</span>
        `;
        
        container.insertBefore(find, container.firstChild);
        
        // Keep only last 10 finds
        while (container.children.length > 10) {
            container.removeChild(container.lastChild);
        }
    }
}

// Initialize mining dashboard
const miningDashboard = new MiningDashboard();

// Mining functions
async function startPersonalMining() {
    const btn = document.getElementById('personal-mine-btn');
    const status = document.getElementById('personal-status');
    const target = document.getElementById('personal-target').value;
    const difficulty = document.getElementById('personal-difficulty').value;
    
    if (miningDashboard.isPersonalMining) {
        // Stop mining
        miningDashboard.isPersonalMining = false;
        btn.textContent = '🚀 START PERSONAL MINING';
        btn.style.background = 'linear-gradient(135deg, #4CAF50, #45a049)';
        status.innerHTML = 'Personal mining stopped';
        return;
    }
    
    // Start mining
    miningDashboard.isPersonalMining = true;
    btn.textContent = '⏹️ STOP MINING';
    btn.style.background = 'linear-gradient(135deg, #f44336, #d32f2f)';
    status.innerHTML = '<span style="color: #4CAF50;">⛏️ Mining personal hashes...</span>';
    
    miningDashboard.logToConsole(`Started personal mining: ${target} @ ${difficulty}`, 'info');
    
    try {
        while (miningDashboard.isPersonalMining) {
            const proof = await miningDashboard.miningSystem.acquireProofFor({
                target_type: 'general',
                target_id: 'personal_' + target,
                action: 'mine',
                difficulty: difficulty
            });
            
            // Submit proof and award points
            await submitMiningProof(proof, 'personal', target);
            
            const points = calculatePoints(difficulty, proof.hash);
            miningDashboard.sessionStats.proofs++;
            miningDashboard.sessionStats.points += points;
            
            miningDashboard.logToConsole(`Personal proof found! Hash: ${proof.hash.substring(0, 16)}... (+${points} pts)`, 'success');
            miningDashboard.addRecentFind(proof.hash, points, difficulty);
            
            // Brief pause before next mining cycle
            await new Promise(resolve => setTimeout(resolve, 100));
        }
    } catch (error) {
        miningDashboard.logToConsole(`Personal mining error: ${error.message}`, 'error');
        status.innerHTML = '<span style="color: #f44336;">❌ Mining error - check console</span>';
        
        // Reset button
        miningDashboard.isPersonalMining = false;
        btn.textContent = '🚀 START PERSONAL MINING';
        btn.style.background = 'linear-gradient(135deg, #4CAF50, #45a049)';
    }
}

async function startTargetMining() {
    const btn = document.getElementById('target-mine-btn');
    const status = document.getElementById('target-status');
    const targetType = document.getElementById('target-type').value;
    const targetId = document.getElementById('target-id').value;
    const difficulty = document.getElementById('target-difficulty').value;
    
    if (miningDashboard.isTargetMining) {
        // Stop mining
        miningDashboard.isTargetMining = false;
        btn.textContent = '⛏️ START TARGET MINING';
        btn.style.background = 'linear-gradient(135deg, #FF9800, #F57C00)';
        status.innerHTML = 'Target mining stopped';
        return;
    }
    
    if (!targetId && targetType !== 'random') {
        status.innerHTML = '<span style="color: #f44336;">❌ Please enter a target ID</span>';
        return;
    }
    
    // Start mining
    miningDashboard.isTargetMining = true;
    btn.textContent = '⏹️ STOP MINING';
    btn.style.background = 'linear-gradient(135deg, #f44336, #d32f2f)';
    status.innerHTML = '<span style="color: #FF9800;">⛏️ Mining target...</span>';
    
    const finalTargetId = targetType === 'random' ? Math.floor(Math.random() * 100) + 1 : targetId;
    miningDashboard.logToConsole(`Started target mining: ${targetType} #${finalTargetId} @ ${difficulty}`, 'info');
    
    try {
        while (miningDashboard.isTargetMining) {
            const proof = await miningDashboard.miningSystem.acquireProofFor({
                target_type: targetType,
                target_id: finalTargetId,
                action: 'mine',
                difficulty: difficulty
            });
            
            // Submit proof
            await submitMiningProof(proof, targetType, finalTargetId);
            
            const points = calculatePoints(difficulty, proof.hash);
            miningDashboard.sessionStats.proofs++;
            miningDashboard.sessionStats.points += points;
            
            miningDashboard.logToConsole(`Target proof found! ${targetType} #${finalTargetId} - Hash: ${proof.hash.substring(0, 16)}... (+${points} pts)`, 'success');
            miningDashboard.addRecentFind(proof.hash, points, difficulty);
            
            // Brief pause before next mining cycle
            await new Promise(resolve => setTimeout(resolve, 100));
        }
    } catch (error) {
        miningDashboard.logToConsole(`Target mining error: ${error.message}`, 'error');
        status.innerHTML = '<span style="color: #f44336;">❌ Mining error - check console</span>';
        
        // Reset button
        miningDashboard.isTargetMining = false;
        btn.textContent = '⛏️ START TARGET MINING';
        btn.style.background = 'linear-gradient(135deg, #FF9800, #F57C00)';
    }
}

async function submitMiningProof(proof, targetType, targetId) {
    try {
        const response = await fetch('/api/mining/submit-proof', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                challenge_token: proof.challenge_id,
                client_nonce: proof.nonce,
                hash: proof.hash
            })
        });
        
        const result = await response.json();
        if (result.success) {
            miningDashboard.logToConsole(`Proof submitted successfully: +${result.points || 0} pts (Total: ${result.total_points})`, 'success');
            
            // Update displayed stats with animation
            const pointsEl = document.getElementById('user-points');
            const levelEl = document.getElementById('user-level');
            
            if (pointsEl && result.total_points !== undefined) {
                pointsEl.textContent = result.total_points.toLocaleString();
                pointsEl.style.animation = 'pulse 0.5s ease-in-out';
                setTimeout(() => pointsEl.style.animation = '', 500);
            }
            
            if (levelEl && result.user_level !== undefined) {
                levelEl.textContent = result.user_level;
            }
            
            // Force refresh stats from server
            await refreshUserStats();
            
        } else {
            miningDashboard.logToConsole(`Proof submission failed: ${result.message}`, 'warning');
        }
    } catch (error) {
        miningDashboard.logToConsole(`Proof submission error: ${error.message}`, 'error');
    }
}

// Refresh user stats from API
async function refreshUserStats() {
    try {
        const response = await fetch('/api/mining/stats');
        const data = await response.json();
        
        if (data.success && data.user) {
            const pointsEl = document.getElementById('user-points');
            const levelEl = document.getElementById('user-level');
            
            if (pointsEl) pointsEl.textContent = data.user.total_points.toLocaleString();
            if (levelEl) levelEl.textContent = data.user.level;
            
            console.log('✅ Stats refreshed:', data.user);
        }
    } catch (error) {
        console.warn('Could not refresh stats:', error.message);
    }
}

function calculatePoints(difficulty, hash) {
    const pointMap = {
        '2': 1,
        '21': 2.5,
        '21e': 5,
        '21e8': 10,
        '21e80': 50,
        '21e800': 250
    };
    
    let points = pointMap[difficulty] || 0.1;
    
    // Bonus for rare patterns
    if (hash.startsWith('000')) points *= 10;
    else if (hash.startsWith('666')) points *= 15;
    else if (hash.includes('dead')) points *= 8;
    
    return points;
}

function clearConsole() {
    const console = document.getElementById('mining-console');
    console.innerHTML = '<div>🔨 Console cleared</div>';
}

function exportMiningLog() {
    const console = document.getElementById('mining-console');
    const logText = Array.from(console.children).map(el => el.textContent).join('\n');
    
    const blob = new Blob([logText], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `haichan-mining-log-${Date.now()}.txt`;
    a.click();
    URL.revokeObjectURL(url);
}

// Challenge mode functions
async function startChallengeMode(mode) {
    const status = document.getElementById('challenge-status');
    
    miningDashboard.logToConsole(`🎯 Starting ${mode} challenge mode`, 'info');
    
    switch (mode) {
        case 'speed':
            status.innerHTML = `
                <div style="color: #2196F3; font-weight: bold;">⚡ SPEED CHALLENGE ACTIVE</div>
                <div style="color: var(--text-secondary);">Find 10 proofs as fast as possible!</div>
                <div style="margin-top: 5px;">Progress: <span id="speed-progress">0/10</span></div>
            `;
            await speedChallenge();
            break;
            
        case 'rare':
            status.innerHTML = `
                <div style="color: #9C27B0; font-weight: bold;">💎 RARE HUNTER ACTIVE</div>
                <div style="color: var(--text-secondary);">Hunt for special hash patterns!</div>
            `;
            await rareHunterChallenge();
            break;
            
        case 'lucky':
            status.innerHTML = `
                <div style="color: #4CAF50; font-weight: bold;">🍀 LUCKY DIP ACTIVE</div>
                <div style="color: var(--text-secondary);">Random difficulties each round!</div>
            `;
            await luckyDipChallenge();
            break;
            
        default:
            miningDashboard.logToConsole(`Challenge mode "${mode}" not implemented yet`, 'warning');
            status.innerHTML = `
                <div style="color: #FF5722; font-weight: bold;">🚧 COMING SOON</div>
                <div style="color: var(--text-secondary);">${mode} challenge mode is under development!</div>
            `;
    }
}

async function speedChallenge() {
    let proofs = 0;
    const target = 10;
    const startTime = Date.now();
    
    while (proofs < target) {
        try {
            const proof = await miningDashboard.miningSystem.acquireProofFor({
                target_type: 'general',
                target_id: 'speed_challenge',
                action: 'mine',
                difficulty: '21'
            });
            
            await submitMiningProof(proof, 'challenge', 'speed');
            proofs++;
            
            document.getElementById('speed-progress').textContent = `${proofs}/${target}`;
            
            const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
            miningDashboard.logToConsole(`Speed challenge: ${proofs}/${target} (${elapsed}s)`, 'info');
            
        } catch (error) {
            miningDashboard.logToConsole(`Speed challenge error: ${error.message}`, 'error');
            break;
        }
    }
    
    if (proofs === target) {
        const totalTime = ((Date.now() - startTime) / 1000).toFixed(1);
        miningDashboard.logToConsole(`🏆 Speed challenge completed in ${totalTime}s!`, 'success');
        
        document.getElementById('challenge-status').innerHTML = `
            <div style="color: #4CAF50; font-weight: bold;">🏆 SPEED CHALLENGE COMPLETED!</div>
            <div style="color: var(--text-secondary);">Time: ${totalTime} seconds</div>
        `;
    }
}

async function rareHunterChallenge() {
    const rarePatterns = ['000', '111', '666', '777', 'beef', 'dead'];
    let found = 0;
    
    while (found < 3) {
        const pattern = rarePatterns[Math.floor(Math.random() * rarePatterns.length)];
        
        try {
            miningDashboard.logToConsole(`🔍 Hunting for pattern: ${pattern}`, 'info');
            
            const proof = await miningDashboard.miningSystem.acquireProofFor({
                target_type: 'general',
                target_id: 'rare_hunter',
                action: 'mine',
                difficulty: pattern
            });
            
            await submitMiningProof(proof, 'challenge', 'rare');
            found++;
            
            miningDashboard.logToConsole(`💎 Found rare pattern: ${pattern} in hash ${proof.hash.substring(0, 16)}...`, 'success');
            
        } catch (error) {
            miningDashboard.logToConsole(`Rare hunter error: ${error.message}`, 'error');
            break;
        }
    }
    
    if (found >= 3) {
        miningDashboard.logToConsole(`🏆 Rare hunter challenge completed!`, 'success');
    }
}

async function luckyDipChallenge() {
    const difficulties = ['21', '21e', '21e8'];
    let round = 1;
    
    while (round <= 5) {
        const difficulty = difficulties[Math.floor(Math.random() * difficulties.length)];
        
        try {
            miningDashboard.logToConsole(`🍀 Lucky dip round ${round}: ${difficulty}`, 'info');
            
            const proof = await miningDashboard.miningSystem.acquireProofFor({
                target_type: 'general',
                target_id: 'lucky_dip',
                action: 'mine',
                difficulty: difficulty
            });
            
            await submitMiningProof(proof, 'challenge', 'lucky');
            round++;
            
        } catch (error) {
            miningDashboard.logToConsole(`Lucky dip error: ${error.message}`, 'error');
            break;
        }
    }
    
    if (round > 5) {
        miningDashboard.logToConsole(`🏆 Lucky dip challenge completed!`, 'success');
    }
}

// Target grid functions
function generateNewTargets() {
    const grid = document.getElementById('target-grid');
    grid.innerHTML = '';
    
    const patterns = ['21', '21e', '21e8', '000', '111', '666', 'beef', 'dead'];
    
    for (let i = 0; i < 16; i++) {
        const pattern = patterns[Math.floor(Math.random() * patterns.length)];
        const target = document.createElement('div');
        target.style.cssText = `
            background: var(--content-bg);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: monospace;
            font-weight: bold;
        `;
        
        target.innerHTML = `
            <div style="font-size: 18px; margin-bottom: 5px;">🎯</div>
            <div style="font-size: 12px; color: var(--text-secondary);">${pattern}</div>
        `;
        
        target.onclick = () => mineTarget(target, pattern);
        grid.appendChild(target);
    }
}

async function mineTarget(element, pattern) {
    element.style.background = '#ffc107';
    element.innerHTML = `
        <div style="font-size: 18px; margin-bottom: 5px;">⛏️</div>
        <div style="font-size: 12px;">Mining...</div>
    `;
    
    try {
        const proof = await miningDashboard.miningSystem.acquireProofFor({
            target_type: 'general',
            target_id: 'target_grid',
            action: 'mine',
            difficulty: pattern
        });
        
        await submitMiningProof(proof, 'target', pattern);
        
        element.style.background = '#4CAF50';
        element.innerHTML = `
            <div style="font-size: 18px; margin-bottom: 5px;">✅</div>
            <div style="font-size: 10px;">HIT!</div>
        `;
        
        const hitCounter = document.getElementById('targets-hit');
        hitCounter.textContent = parseInt(hitCounter.textContent) + 1;
        
        miningDashboard.logToConsole(`🎯 Target hit: ${pattern}`, 'success');
        
    } catch (error) {
        element.style.background = '#f44336';
        element.innerHTML = `
            <div style="font-size: 18px; margin-bottom: 5px;">❌</div>
            <div style="font-size: 10px;">MISS</div>
        `;
        
        miningDashboard.logToConsole(`🎯 Target miss: ${error.message}`, 'error');
    }
}

function clearTargetGrid() {
    document.getElementById('target-grid').innerHTML = '';
    document.getElementById('targets-hit').textContent = '0';
}

// Custom pattern mining
async function mineCustomPattern(pattern) {
    if (!pattern) {
        pattern = document.getElementById('custom-pattern').value.trim();
    }
    
    if (!pattern) {
        miningDashboard.logToConsole('⚠️ Please enter a pattern to mine', 'warning');
        return;
    }
    
    const status = document.getElementById('pattern-lab-status');
    status.innerHTML = `<span style="color: #E91E63;">🔬 Mining custom pattern: ${pattern}...</span>`;
    
    try {
        miningDashboard.logToConsole(`🔬 Starting custom pattern mining: ${pattern}`, 'info');
        
        const proof = await miningDashboard.miningSystem.acquireProofFor({
            target_type: 'general',
            target_id: 'custom_pattern',
            action: 'mine',
            difficulty: pattern
        });
        
        await submitMiningProof(proof, 'custom', pattern);
        
        const points = calculatePoints(pattern, proof.hash);
        status.innerHTML = `<span style="color: #4CAF50;">✅ Custom pattern found! Hash: ${proof.hash.substring(0, 16)}... (+${points} pts)</span>`;
        
        miningDashboard.logToConsole(`🎉 Custom pattern "${pattern}" found: ${proof.hash}`, 'success');
        
    } catch (error) {
        status.innerHTML = `<span style="color: #f44336;">❌ Custom pattern mining failed: ${error.message}</span>`;
        miningDashboard.logToConsole(`🔬 Custom pattern mining failed: ${error.message}`, 'error');
    }
}

// Initialize target grid on page load
document.addEventListener('DOMContentLoaded', function() {
    generateNewTargets();
});

// Add CSS for pattern buttons
const patternButtonStyle = document.createElement('style');
patternButtonStyle.textContent = `
    .pattern-btn {
        background: var(--secondary-bg) !important;
        color: var(--text-primary) !important;
        border: 1px solid var(--border-color) !important;
        padding: 8px !important;
        border-radius: 4px !important;
        cursor: pointer !important;
        font-size: 10px !important;
        font-weight: bold !important;
        transition: all 0.2s ease !important;
    }
    
    .pattern-btn:hover {
        background: var(--accent-color) !important;
        color: white !important;
        transform: scale(1.05) !important;
    }
    
    .challenge-btn:hover {
        transform: scale(1.02) !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3) !important;
    }
`;
document.head.appendChild(patternButtonStyle);
</script>

@endsection