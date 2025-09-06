<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>⛏️ HAICHAN MINING COMMAND CENTER</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style>
        /* Additional mining-specific styles */
        .mining-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px;
        }
        
        .stat-card {
            background: #F5F5DC;
            border: 1px solid #708B75;
            padding: 15px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 18pt;
            font-weight: bold;
            color: #444B6E;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 8pt;
            color: #888;
            text-transform: uppercase;
        }
        
        .mining-controls {
            background: #F5F5DC;
            border: 1px solid #708B75;
            margin: 20px;
            padding: 20px;
        }
        
        .mining-controls h3 {
            color: #444B6E;
            margin-bottom: 15px;
            border-bottom: 1px solid #708B75;
            padding-bottom: 5px;
        }
        
        .control-row {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .control-row label {
            font-weight: bold;
            color: #444B6E;
            min-width: 100px;
        }
        
        .mining-output {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px;
        }
        
        .output-panel {
            background: #F5F5DC;
            border: 1px solid #708B75;
            padding: 15px;
        }
        
        .output-panel h4 {
            color: #444B6E;
            margin-bottom: 10px;
            border-bottom: 1px solid #708B75;
            padding-bottom: 5px;
        }
        
        .hash-display {
            background: #FFFFEE;
            border: 1px solid #708B75;
            padding: 10px;
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            word-break: break-all;
            margin: 10px 0;
        }
        
        .mining-log {
            background: #FFFFEE;
            border: 1px solid #708B75;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 8pt;
        }
        
        .log-entry {
            margin-bottom: 3px;
            padding: 2px 0;
        }
        
        .log-success {
            color: #789922;
            font-weight: bold;
        }
        
        .log-info {
            color: #444B6E;
        }
        
        .log-error {
            color: #8B0000;
        }
        
        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }
        
        .status-mining {
            background: #9AB87A;
            animation: pulse 2s infinite;
        }
        
        .status-idle {
            background: #888;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        @media (max-width: 768px) {
            .mining-output {
                grid-template-columns: 1fr;
            }
            
            .control-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        @include('components.navigation')

        <div class="board-header">
            <h2>⛏️ Mining Dashboard</h2>
            <p>Mine SHA256 hashes to earn points and bump threads</p>
            <p style="font-size: 9pt; margin-top: 5px;">
                <span class="status-indicator status-idle" id="miningStatus"></span>
                <span id="miningStatusText">Ready to Mine</span>
            </p>
        </div>

        <div class="mining-stats">
            <div class="stat-card">
                <div class="stat-value" id="hashrate">0</div>
                <div class="stat-label">Hashes/sec</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="totalHashes">0</div>
                <div class="stat-label">Total Hashes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="validProofs">0</div>
                <div class="stat-label">Valid Proofs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="sessionPoints">0</div>
                <div class="stat-label">Session Points</div>
            </div>
        </div>

        <div class="mining-controls">
            <h3>Mining Controls</h3>
            
            <div class="control-row">
                <label for="difficultySelect">Difficulty Pattern:</label>
                <select id="difficultySelect">
                    <option value="21e8">21e8 (Easy - 1 point)</option>
                    <option value="21e80">21e80 (Medium - 5 points)</option>
                    <option value="21e800">21e800 (Hard - 25 points)</option>
                    <option value="21e8000">21e8000 (Extreme - 125 points)</option>
                    <option value="000021e8">000021e8 (Insane - 625 points)</option>
                </select>
            </div>
            
            <div class="control-row">
                <button id="startMining" class="btn-primary">🚀 Start Mining</button>
                <button id="stopMining" class="btn-stop" disabled>⛔ Stop Mining</button>
                <button id="clearLog">🧹 Clear Log</button>
                <button id="refreshStats">📊 Refresh Stats</button>
            </div>
        </div>

        <div class="mining-output">
            <div class="output-panel">
                <h4>Current Hash</h4>
                <div class="hash-display" id="currentHashDisplay">
                    <strong>Latest Hash:</strong><br>
                    <span id="currentHash">No hashes generated yet</span>
                </div>
                
                <div style="margin-top: 15px;">
                    <strong>Target Pattern:</strong> <span id="targetPattern">21e8</span><br>
                    <strong>Session Time:</strong> <span id="sessionTime">00:00:00</span><br>
                    <strong>Average Rate:</strong> <span id="avgHashrate">0</span> H/s
                </div>
            </div>

            <div class="output-panel">
                <h4>Mining Log</h4>
                <div class="mining-log" id="miningLog">
                    <div class="log-entry log-info">[SYSTEM] Haichan mining engine ready. Select difficulty and start mining.</div>
                </div>
            </div>
        </div>

        <div class="mining-controls">
            <h3>How It Works</h3>
            <p style="font-size: 9pt; margin-bottom: 8px;">
                • Your browser mines SHA256 hashes looking for specific patterns
            </p>
            <p style="font-size: 9pt; margin-bottom: 8px;">
                • When found, you earn points based on difficulty
            </p>
            <p style="font-size: 9pt; margin-bottom: 8px;">
                • Use these proofs to bump threads on boards for visibility
            </p>
            <p style="font-size: 9pt; margin-bottom: 8px;">
                • Higher difficulty = exponentially more points but much harder to find
            </p>
            <p style="font-size: 9pt;">
                • Start with "21e8" pattern - it's the easiest to find
            </p>
        </div>

        <div style="text-align: center; padding: 20px; color: #444B6E; font-size: 8pt; background: #F5F5DC; border: 1px solid #708B75; margin: 20px;">
            <p style="margin-bottom: 8px;">Mining is performed locally in your browser using WebCrypto API</p>
            <p><a href="/boards" style="color: #444B6E; text-decoration: underline;">Return to Boards</a> | <a href="/rules" style="color: #444B6E; text-decoration: underline;">Read Rules</a></p>
        </div>
    </div>

    <script>
        class HaichanMiner {
            constructor() {
                this.isMining = false;
                this.totalHashes = 0;
                this.validProofs = 0;
                this.sessionPoints = 0;
                this.sessionStartTime = null;
                this.nonce = Math.floor(Math.random() * 1000000);
                this.targetPattern = '21e8';
                this.currentHash = '';
                this.lastHashCount = 0;
                
                this.initializeUI();
                this.startStatsUpdater();
            }

            initializeUI() {
                document.getElementById('startMining').addEventListener('click', () => this.startMining());
                document.getElementById('stopMining').addEventListener('click', () => this.stopMining());
                document.getElementById('clearLog').addEventListener('click', () => this.clearLog());
                document.getElementById('refreshStats').addEventListener('click', () => this.refreshStats());
                
                document.getElementById('difficultySelect').addEventListener('change', (e) => {
                    this.targetPattern = e.target.value;
                    document.getElementById('targetPattern').textContent = this.targetPattern;
                });
            }

            async sha256(text) {
                const encoder = new TextEncoder();
                const data = encoder.encode(text);
                const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }

            async mine() {
                const timestamp = Date.now();
                const baseData = `global:haichan:${timestamp}`;
                
                while (this.isMining) {
                    const data = `${baseData}:${this.nonce}`;
                    const hash = await this.sha256(data);
                    
                    this.totalHashes++;
                    this.nonce++;
                    this.currentHash = hash;
                    
                    if (this.totalHashes % 100 === 0) {
                        this.updateDisplay();
                    }
                    
                    if (this.isValidProof(hash)) {
                        this.validProofs++;
                        this.logSuccess(`🎯 PROOF FOUND! Pattern "${this.targetPattern}" in hash: ${hash.substring(0, 32)}...`);
                        
                        await this.submitProof({
                            hash: hash,
                            nonce: this.nonce - 1,
                            data: data,
                            pattern: this.targetPattern
                        });
                    }
                    
                    if (this.totalHashes % 1000 === 0) {
                        await new Promise(resolve => setTimeout(resolve, 1));
                    }
                }
            }

            isValidProof(hash) {
                return hash.toLowerCase().includes(this.targetPattern.toLowerCase());
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
                        this.sessionPoints += result.points;
                        this.logSuccess(`✅ Proof accepted! +${result.points} points. Session total: ${this.sessionPoints}`);
                        document.getElementById('sessionPoints').textContent = this.sessionPoints;
                    } else {
                        this.logError(`❌ Proof rejected: ${result.message}`);
                    }
                } catch (error) {
                    this.logError(`🔥 Network error: ${error.message}`);
                }
            }

            startMining() {
                if (this.isMining) return;
                
                this.isMining = true;
                this.sessionStartTime = Date.now();
                this.lastHashCount = this.totalHashes;
                
                document.getElementById('startMining').disabled = true;
                document.getElementById('stopMining').disabled = false;
                document.getElementById('miningStatus').className = 'status-indicator status-mining';
                document.getElementById('miningStatusText').textContent = 'Mining Active';
                
                this.logInfo(`🚀 Started mining pattern: ${this.targetPattern}`);
                this.mine();
            }

            stopMining() {
                this.isMining = false;
                
                document.getElementById('startMining').disabled = false;
                document.getElementById('stopMining').disabled = true;
                document.getElementById('miningStatus').className = 'status-indicator status-idle';
                document.getElementById('miningStatusText').textContent = 'Mining Stopped';
                
                this.logInfo(`⛔ Mining stopped. Session stats: ${this.totalHashes} hashes, ${this.validProofs} proofs, ${this.sessionPoints} points`);
            }

            updateDisplay() {
                document.getElementById('currentHash').textContent = this.currentHash;
                document.getElementById('totalHashes').textContent = this.totalHashes.toLocaleString();
                document.getElementById('validProofs').textContent = this.validProofs;
                
                if (this.sessionStartTime) {
                    const elapsed = (Date.now() - this.sessionStartTime) / 1000;
                    const hashrate = Math.round((this.totalHashes - this.lastHashCount) / Math.max(elapsed, 1));
                    document.getElementById('hashrate').textContent = hashrate.toLocaleString();
                    document.getElementById('avgHashrate').textContent = hashrate.toLocaleString();
                    
                    const hours = Math.floor(elapsed / 3600);
                    const minutes = Math.floor((elapsed % 3600) / 60);
                    const seconds = Math.floor(elapsed % 60);
                    document.getElementById('sessionTime').textContent = 
                        `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }
            }

            startStatsUpdater() {
                setInterval(() => {
                    if (this.isMining) {
                        this.updateDisplay();
                    }
                }, 1000);
            }

            refreshStats() {
                this.updateDisplay();
                this.logInfo('📊 Stats refreshed');
            }

            logInfo(message) { this.addLogEntry(message, 'log-info'); }
            logSuccess(message) { this.addLogEntry(message, 'log-success'); }
            logError(message) { this.addLogEntry(message, 'log-error'); }

            addLogEntry(message, className) {
                const log = document.getElementById('miningLog');
                const entry = document.createElement('div');
                entry.className = `log-entry ${className}`;
                entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
                log.appendChild(entry);
                log.scrollTop = log.scrollHeight;

                while (log.children.length > 50) {
                    log.removeChild(log.firstChild);
                }
            }

            clearLog() {
                document.getElementById('miningLog').innerHTML = '';
                this.logInfo('🧹 Mining log cleared');
            }
        }

        window.addEventListener('DOMContentLoaded', () => {
            window.haichanMiner = new HaichanMiner();
        });
    </script>
</body>
</html>
