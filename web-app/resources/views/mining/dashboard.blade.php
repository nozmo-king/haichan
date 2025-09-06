@extends('layout')

@section('title', '⛏️ HAICHAN MINING COMMAND CENTER')

@section('content')
<style>
        * { box-sizing: border-box; }
        
        .mining-page {
            background: #F5F5DC;
            color: #444B6E;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            min-height: calc(100vh - 60px);
        }
        
        .command-center {
            display: grid;
            grid-template-areas: 
                "header header header header"
                "stats stats network network"
                "controls controls network network"
                "terminal terminal leaderboard leaderboard"
                "hashview hashview activity activity";
            grid-template-columns: 1fr 1fr 1fr 1fr;
            grid-template-rows: auto auto auto 1fr auto;
            gap: 15px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .panel {
            background: #FFFACD;
            border: 2px solid #708B75;
            border-radius: 10px;
            padding: 20px;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .panel::before {
            display: none;
        }
        
        @keyframes scan {
            0% { transform: translateX(-100%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100%); opacity: 0; }
        }
        
        .header {
            grid-area: header;
            text-align: center;
            background: #9AB87A;
            border: 3px solid #708B75;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2.5em;
            color: #444B6E;
            margin-bottom: 10px;
        }
        
        
        .network-status {
            display: flex;
            justify-content: center;
            gap: 30px;
            font-size: 12px;
            margin-top: 10px;
        }
        
        .network-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #708B75;
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        
        .stats-grid {
            grid-area: stats;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 50, 0, 0.3));
            border: 1px solid #708B75;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #708B75;
            text-shadow: 0 0 10px #708B75;
            margin-bottom: 5px;
            font-family: 'Courier New', monospace;
        }
        
        .stat-label {
            font-size: 10px;
            color: #66ff66;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .mining-controls {
            grid-area: controls;
        }
        
        .controls-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            height: 100%;
        }
        
        .control-section h3 {
            color: #708B75;
            text-shadow: 0 0 5px #708B75;
            border-bottom: 1px solid #708B75;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        
        .mining-mode-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .mode-btn {
            flex: 1;
            padding: 10px;
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #708B75;
            color: #708B75;
            font-family: inherit;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
        }
        
        .mode-btn.active {
            background: rgba(0, 255, 0, 0.2);
            box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);
            text-shadow: 0 0 5px #708B75;
        }
        
        .mode-btn:hover {
            background: rgba(0, 255, 0, 0.1);
        }
        
        .control-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .btn {
            padding: 12px 16px;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 50, 0, 0.3));
            border: 1px solid #708B75;
            color: #708B75;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            background: rgba(0, 255, 0, 0.1);
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn.danger {
            border-color: #ff6b35;
            color: #ff6b35;
        }
        
        .network-viz {
            grid-area: network;
            position: relative;
        }
        
        .viz-container {
            width: 100%;
            height: 300px;
            background: radial-gradient(circle at center, rgba(0, 255, 0, 0.1), transparent);
            position: relative;
            overflow: hidden;
            border-radius: 5px;
        }
        
        .terminal {
            grid-area: terminal;
        }
        
        .terminal-screen {
            background: rgba(0, 0, 0, 0.9);
            border: 1px solid #708B75;
            border-radius: 5px;
            height: 250px;
            overflow-y: auto;
            padding: 10px;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .terminal-line {
            margin-bottom: 2px;
        }
        
        .terminal-prompt {
            color: #ffff00;
        }
        
        .terminal-success {
            color: #708B75;
            text-shadow: 0 0 3px #708B75;
        }
        
        .terminal-error {
            color: #ff6b35;
        }
        
        .terminal-info {
            color: #66ff66;
        }
        
        .leaderboard {
            grid-area: leaderboard;
        }
        
        .leaderboard-list {
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #708B75;
            border-radius: 5px;
            height: 250px;
            overflow-y: auto;
        }
        
        .leaderboard-entry {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 15px;
            border-bottom: 1px solid rgba(0, 255, 0, 0.2);
            font-size: 11px;
        }
        
        .leaderboard-entry:first-child {
            background: rgba(255, 215, 0, 0.1);
            color: #ffd700;
        }
        
        .leaderboard-entry:nth-child(2) {
            background: rgba(192, 192, 192, 0.1);
            color: #c0c0c0;
        }
        
        .leaderboard-entry:nth-child(3) {
            background: rgba(205, 127, 50, 0.1);
            color: #cd7f32;
        }
        
        .hash-visualizer {
            grid-area: hashview;
        }
        
        .hash-display {
            background: rgba(0, 0, 0, 0.9);
            border: 1px solid #708B75;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 9px;
            word-break: break-all;
            letter-spacing: 0.5px;
            line-height: 1.4;
        }
        
        .current-hash {
            color: #708B75;
            text-shadow: 0 0 5px #708B75;
        }
        
        .target-pattern {
            color: #ffff00;
            background: rgba(255, 255, 0, 0.1);
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .activity-feed {
            grid-area: activity;
        }
        
        .activity-list {
            background: rgba(0, 0, 0, 0.7);
            border: 1px solid #708B75;
            border-radius: 5px;
            height: 200px;
            overflow-y: auto;
            padding: 10px;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 0;
            border-bottom: 1px solid rgba(0, 255, 0, 0.1);
            font-size: 10px;
        }
        
        .activity-time {
            color: #888;
            min-width: 50px;
        }
        
        .activity-type {
            min-width: 60px;
            text-transform: uppercase;
        }
        
        .activity-proof {
            color: #708B75;
            text-shadow: 0 0 3px #708B75;
        }
        
        .activity-board {
            color: #ffff00;
        }
        
        .activity-thread {
            color: #66ff66;
        }
        
        @media (max-width: 1200px) {
            .command-center {
                grid-template-areas: 
                    "header header"
                    "stats stats"
                    "controls controls"
                    "network network"
                    "terminal terminal"
                    "leaderboard leaderboard"
                    "hashview hashview"
                    "activity activity";
                grid-template-columns: 1fr 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .command-center {
                grid-template-areas: 
                    "header"
                    "stats"
                    "controls"
                    "network"
                    "terminal"
                    "leaderboard"
                    "hashview"
                    "activity";
                grid-template-columns: 1fr;
                padding: 10px;
            }
            
            .controls-inner {
                grid-template-columns: 1fr;
            }
        }
    </style>

<div class="mining-page">
    <div class="command-center">
        <!-- HEADER -->
        <div class="panel header">
            <h1>⛏️ HAICHAN MINING COMMAND CENTER ⛏️</h1>
            <div class="network-status">
                <div class="network-stat">
                    <div class="status-dot"></div>
                    <span>NETWORK: <span id="network-status">ONLINE</span></span>
                </div>
                <div class="network-stat">
                    <div class="status-dot"></div>
                    <span>MINERS: <span id="active-miners">1</span></span>
                </div>
                <div class="network-stat">
                    <div class="status-dot"></div>
                    <span>DIFFICULTY: <span id="network-difficulty">21e8</span></span>
                </div>
                <div class="network-stat">
                    <div class="status-dot"></div>
                    <span>UPTIME: <span id="network-uptime">00:00:00</span></span>
                </div>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="panel stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="hash-rate">0</div>
                <div class="stat-label">Hash Rate (H/s)</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="total-hashes">0</div>
                <div class="stat-label">Total Hashes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="valid-proofs">0</div>
                <div class="stat-label">Valid Proofs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="session-points">0</div>
                <div class="stat-label">Session Points</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="success-rate">0.0%</div>
                <div class="stat-label">Success Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="session-time">00:00</div>
                <div class="stat-label">Session Time</div>
            </div>
        </div>

        <!-- MINING CONTROLS -->
        <div class="panel mining-controls">
            <div class="controls-inner">
                <div class="control-section">
                    <h3>Mining Mode</h3>
                    <div class="mining-mode-selector">
                        <button class="mode-btn active" data-mode="idle">
                            🟢 IDLE<br><span style="font-size:8px;">~100 H/s</span>
                        </button>
                        <button class="mode-btn" data-mode="active">
                            🟡 ACTIVE<br><span style="font-size:8px;">~1K H/s</span>
                        </button>
                        <button class="mode-btn" data-mode="hyper">
                            🔴 HYPER<br><span style="font-size:8px;">~3K+ H/s</span>
                        </button>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-size:10px;">TARGET PATTERN:</label>
                        <select id="difficulty-select" style="width:100%; padding:5px; background:rgba(0,0,0,0.7); border:1px solid #708B75; color:#708B75; font-family:inherit;">
                            <option value="21">21 (Idle - 0.1 pts)</option>
                            <option value="21e8" selected>21e8 (Normal - 1 pt)</option>
                            <option value="21e80">21e80 (Hard - 5 pts)</option>
                            <option value="21e800">21e800 (Extreme - 25 pts)</option>
                            <option value="21e8000">21e8000 (Insane - 125 pts)</option>
                            <option value="000021e8">000021e8 (Godlike - 625 pts)</option>
                        </select>
                    </div>
                </div>
                
                <div class="control-section">
                    <h3>Operations</h3>
                    <div class="control-buttons">
                        <button class="btn" id="start-mining">🚀 START</button>
                        <button class="btn danger" id="stop-mining" disabled>⛔ STOP</button>
                    </div>
                    <div class="control-buttons">
                        <button class="btn" id="clear-terminal">🧹 CLEAR</button>
                        <button class="btn" id="refresh-stats">📊 STATS</button>
                        <button class="btn" id="export-data">💾 EXPORT</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- NETWORK VISUALIZATION -->
        <div class="panel network-viz">
            <h3 style="margin-bottom:15px;">Network Visualization</h3>
            <div class="viz-container" id="network-canvas">
                <canvas id="network-viz-canvas" width="400" height="280" style="width:100%; height:100%;"></canvas>
            </div>
        </div>

        <!-- TERMINAL -->
        <div class="panel terminal">
            <h3 style="margin-bottom:15px;">Mining Terminal</h3>
            <div class="terminal-screen" id="terminal-output">
                <div class="terminal-line terminal-success">[SYSTEM] Haichan Mining Command Center initialized</div>
                <div class="terminal-line terminal-info">[INFO] WebCrypto API detected and ready</div>
                <div class="terminal-line terminal-info">[INFO] Select mining mode and click START to begin</div>
                <div class="terminal-line terminal-prompt">mining@haichan:~$ <span class="terminal-cursor">_</span></div>
            </div>
        </div>

        <!-- LEADERBOARD -->
        <div class="panel leaderboard">
            <h3 style="margin-bottom:15px;">Network Leaderboard</h3>
            <div class="leaderboard-list" id="leaderboard-list">
                <div class="leaderboard-entry">
                    <span>🥇 Anonymous#a1b2c3d4</span>
                    <span>15,847 pts</span>
                </div>
                <div class="leaderboard-entry">
                    <span>🥈 Anonymous#f5e6d7c8</span>
                    <span>12,356 pts</span>
                </div>
                <div class="leaderboard-entry">
                    <span>🥉 Anonymous#9k8j7h6g</span>
                    <span>8,901 pts</span>
                </div>
                <div class="leaderboard-entry">
                    <span>4. Anonymous#x7y8z9a0</span>
                    <span>6,543 pts</span>
                </div>
                <div class="leaderboard-entry">
                    <span>5. Anonymous#p3q4r5s6</span>
                    <span>4,201 pts</span>
                </div>
                <div class="leaderboard-entry">
                    <span>6. You</span>
                    <span id="your-rank-points">0 pts</span>
                </div>
            </div>
        </div>

        <!-- HASH VISUALIZER -->
        <div class="panel hash-visualizer">
            <h3 style="margin-bottom:15px;">Current Hash Analysis</h3>
            <div class="hash-display">
                <div style="margin-bottom:10px;">
                    <strong>MINING TARGET:</strong> <span class="target-pattern" id="current-target">21e8</span>
                </div>
                <div style="margin-bottom:10px;">
                    <strong>LATEST HASH:</strong>
                </div>
                <div class="current-hash" id="current-hash-display">
                    No hashes generated yet - start mining to see live hash data
                </div>
            </div>
        </div>

        <!-- ACTIVITY FEED -->
        <div class="panel activity-feed">
            <h3 style="margin-bottom:15px;">Network Activity</h3>
            <div class="activity-list" id="activity-feed">
                <div class="activity-item">
                    <span class="activity-time">15:42</span>
                    <span class="activity-type activity-proof">PROOF</span>
                    <span>Pattern 21e8 found on /g/</span>
                </div>
                <div class="activity-item">
                    <span class="activity-time">15:41</span>
                    <span class="activity-type activity-board">BUMP</span>
                    <span class="activity-thread">Thread #1234 bumped</span>
                </div>
                <div class="activity-item">
                    <span class="activity-time">15:40</span>
                    <span class="activity-type activity-proof">PROOF</span>
                    <span>Pattern 21e80 found on /tech/</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        class HaichanCommandCenter {
            constructor() {
                this.isMining = false;
                this.miningMode = 'idle';
                this.targetPattern = '21e8';
                this.hashRate = 0;
                this.totalHashes = 0;
                this.validProofs = 0;
                this.sessionPoints = 0;
                this.sessionStart = Date.now();
                this.currentHash = '';
                this.nonce = Math.floor(Math.random() * 1000000);
                
                this.initializeUI();
                this.initializeNetworkViz();
                this.startUpdateLoop();
            }

            initializeUI() {
                // Mining mode buttons
                document.querySelectorAll('.mode-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        this.miningMode = btn.dataset.mode;
                        this.updateMiningSpeed();
                    });
                });

                // Control buttons
                document.getElementById('start-mining').addEventListener('click', () => this.startMining());
                document.getElementById('stop-mining').addEventListener('click', () => this.stopMining());
                document.getElementById('clear-terminal').addEventListener('click', () => this.clearTerminal());
                document.getElementById('refresh-stats').addEventListener('click', () => this.refreshStats());
                document.getElementById('export-data').addEventListener('click', () => this.exportData());

                // Difficulty selector
                document.getElementById('difficulty-select').addEventListener('change', (e) => {
                    this.targetPattern = e.target.value;
                    document.getElementById('current-target').textContent = this.targetPattern;
                    document.getElementById('network-difficulty').textContent = this.targetPattern;
                });
            }

            initializeNetworkViz() {
                const canvas = document.getElementById('network-viz-canvas');
                const ctx = canvas.getContext('2d');
                
                // Draw network nodes and connections
                setInterval(() => {
                    if (!this.isMining) return;
                    
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    ctx.strokeStyle = '#708B75';
                    ctx.fillStyle = '#708B75';
                    
                    // Draw central node
                    ctx.beginPath();
                    ctx.arc(canvas.width/2, canvas.height/2, 8, 0, 2 * Math.PI);
                    ctx.fill();
                    
                    // Draw connecting nodes
                    for (let i = 0; i < 6; i++) {
                        const angle = (i / 6) * 2 * Math.PI;
                        const x = canvas.width/2 + Math.cos(angle) * 80;
                        const y = canvas.height/2 + Math.sin(angle) * 80;
                        
                        ctx.beginPath();
                        ctx.arc(x, y, 4, 0, 2 * Math.PI);
                        ctx.fill();
                        
                        // Draw connection
                        ctx.beginPath();
                        ctx.moveTo(canvas.width/2, canvas.height/2);
                        ctx.lineTo(x, y);
                        ctx.stroke();
                    }
                }, 100);
            }

            async startMining() {
                if (this.isMining) return;
                
                this.isMining = true;
                this.sessionStart = Date.now();
                
                document.getElementById('start-mining').disabled = true;
                document.getElementById('stop-mining').disabled = false;
                document.getElementById('network-status').textContent = 'MINING';
                
                this.terminalLog('🚀 Mining operation commenced', 'success');
                this.terminalLog(`Target pattern: ${this.targetPattern}`, 'info');
                this.terminalLog(`Mining mode: ${this.miningMode.toUpperCase()}`, 'info');
                
                this.mine();
            }

            stopMining() {
                this.isMining = false;
                
                document.getElementById('start-mining').disabled = false;
                document.getElementById('stop-mining').disabled = true;
                document.getElementById('network-status').textContent = 'STANDBY';
                
                this.terminalLog('⛔ Mining operation terminated', 'error');
                this.terminalLog(`Session stats: ${this.totalHashes} hashes, ${this.validProofs} proofs, ${this.sessionPoints} points`, 'info');
            }

            async mine() {
                const batchSize = this.getBatchSize();
                
                while (this.isMining) {
                    const startTime = Date.now();
                    
                    for (let i = 0; i < batchSize && this.isMining; i++) {
                        const timestamp = Date.now();
                        const baseData = `global:haichan:${timestamp}`;
                        const fullData = `${baseData}:${this.nonce}`;
                        const hash = await this.sha256(fullData);
                        
                        this.totalHashes++;
                        this.nonce++;
                        this.currentHash = hash;
                        
                        if (this.isValidProof(hash)) {
                            this.validProofs++;
                            const points = this.getPoints(this.targetPattern);
                            this.sessionPoints += points;
                            
                            this.terminalLog(`🎯 PROOF FOUND! ${hash.substring(0, 16)}... (+${points} pts)`, 'success');
                            this.addActivity('PROOF', `Pattern ${this.targetPattern} found (+${points} pts)`);
                            
                            // Submit proof - send baseData without nonce, PHP will add it
                            await this.submitProof({
                                hash: hash,
                                nonce: this.nonce - 1,
                                data: baseData,
                                pattern: this.targetPattern
                            });
                        }
                    }
                    
                    // Calculate hash rate
                    const elapsed = (Date.now() - startTime) / 1000;
                    this.hashRate = Math.floor(batchSize / elapsed);
                    
                    // Small delay to prevent browser freeze
                    await new Promise(resolve => setTimeout(resolve, 1));
                }
            }

            getBatchSize() {
                switch (this.miningMode) {
                    case 'idle': return 100;
                    case 'active': return 500;
                    case 'hyper': return 1000;
                    default: return 100;
                }
            }

            getPoints(pattern) {
                const points = {
                    '21': 0.1,
                    '21e8': 1,
                    '21e80': 5,
                    '21e800': 25,
                    '21e8000': 125,
                    '000021e8': 625
                };
                return points[pattern] || 1;
            }

            async sha256(text) {
                const encoder = new TextEncoder();
                const data = encoder.encode(text);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            isValidProof(hash) {
                return hash.toLowerCase().startsWith(this.targetPattern.toLowerCase());
            }

            async submitProof(proof) {
                try {
                    const response = await fetch('/api/submit-proof', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(proof)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.terminalLog(`✅ Proof accepted by network (+${result.points} pts)`, 'success');
                    } else {
                        this.terminalLog(`❌ Proof rejected: ${result.message}`, 'error');
                    }
                } catch (error) {
                    this.terminalLog(`🔥 Network error: ${error.message}`, 'error');
                }
            }

            updateStats() {
                document.getElementById('hash-rate').textContent = this.hashRate.toLocaleString();
                document.getElementById('total-hashes').textContent = this.totalHashes.toLocaleString();
                document.getElementById('valid-proofs').textContent = this.validProofs.toLocaleString();
                document.getElementById('session-points').textContent = this.sessionPoints.toLocaleString();
                document.getElementById('your-rank-points').textContent = this.sessionPoints + ' pts';
                
                // Success rate
                const successRate = this.totalHashes > 0 ? ((this.validProofs / this.totalHashes) * 100).toFixed(3) : 0;
                document.getElementById('success-rate').textContent = successRate + '%';
                
                // Session time
                const elapsed = Math.floor((Date.now() - this.sessionStart) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                document.getElementById('session-time').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                // Network uptime
                document.getElementById('network-uptime').textContent = document.getElementById('session-time').textContent;
                
                // Current hash display
                if (this.currentHash) {
                    document.getElementById('current-hash-display').textContent = this.currentHash;
                }
            }

            updateMiningSpeed() {
                this.terminalLog(`Mining mode changed to ${this.miningMode.toUpperCase()}`, 'info');
            }

            terminalLog(message, type = 'info') {
                const terminal = document.getElementById('terminal-output');
                const line = document.createElement('div');
                line.className = `terminal-line terminal-${type}`;
                line.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
                terminal.appendChild(line);
                terminal.scrollTop = terminal.scrollHeight;
                
                // Keep only last 50 lines
                while (terminal.children.length > 50) {
                    terminal.removeChild(terminal.firstChild);
                }
            }

            addActivity(type, message) {
                const feed = document.getElementById('activity-feed');
                const activity = document.createElement('div');
                activity.className = 'activity-item';
                activity.innerHTML = `
                    <span class="activity-time">${new Date().toLocaleTimeString().substring(0,5)}</span>
                    <span class="activity-type activity-${type.toLowerCase()}">${type}</span>
                    <span>${message}</span>
                `;
                feed.insertBefore(activity, feed.firstChild);
                
                // Keep only last 20 activities
                while (feed.children.length > 20) {
                    feed.removeChild(feed.lastChild);
                }
            }

            clearTerminal() {
                document.getElementById('terminal-output').innerHTML = `
                    <div class="terminal-line terminal-success">[SYSTEM] Terminal cleared</div>
                    <div class="terminal-line terminal-prompt">mining@haichan:~$ <span class="terminal-cursor">_</span></div>
                `;
            }

            refreshStats() {
                this.updateStats();
                this.terminalLog('📊 Statistics refreshed', 'info');
            }

            exportData() {
                const data = {
                    session_stats: {
                        total_hashes: this.totalHashes,
                        valid_proofs: this.validProofs,
                        session_points: this.sessionPoints,
                        hash_rate: this.hashRate,
                        mining_mode: this.miningMode,
                        target_pattern: this.targetPattern,
                        session_duration: Date.now() - this.sessionStart
                    },
                    timestamp: new Date().toISOString()
                };
                
                const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `haichan_mining_session_${Date.now()}.json`;
                a.click();
                URL.revokeObjectURL(url);
                
                this.terminalLog('💾 Session data exported', 'success');
            }

            startUpdateLoop() {
                setInterval(() => {
                    this.updateStats();
                }, 1000);
            }
        }

        // Initialize when page loads
        window.addEventListener('DOMContentLoaded', () => {
            window.haichanCommandCenter = new HaichanCommandCenter();
        });
    </script>
</div>
@endsection