<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mining Dashboard - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style>
        /* Mining-specific styles */
        .mining-container {
            max-width: 1200px;
            margin: 0 auto;
            background: #FFFFEE;
            min-height: 100vh;
            padding: 20px;
        }
        
        .mining-header {
            background: #708B75;
            padding: 15px;
            text-align: center;
            color: #FFFFEE;
            margin-bottom: 20px;
            border: 2px solid #444B6E;
        }
        
        .mining-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .mining-panel {
            background: #F5F5DC;
            border: 1px solid #708B75;
            padding: 15px;
        }
        
        .mining-panel h3 {
            color: #444B6E;
            margin-bottom: 10px;
            font-size: 14pt;
        }
        
        .mining-button {
            background: #9AB87A;
            color: #444B6E;
            border: 1px solid #708B75;
            padding: 8px 16px;
            cursor: pointer;
            font-family: inherit;
            font-size: 10pt;
            font-weight: bold;
            margin-right: 5px;
        }
        
        .mining-button:hover {
            background: #708B75;
            color: #FFFFEE;
        }
        
        .mining-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .mining-input, .mining-select {
            width: 100%;
            padding: 5px;
            border: 1px solid #708B75;
            font-family: inherit;
            font-size: 9pt;
            background: #FFFFEE;
            color: #444B6E;
        }
        
        .hash-display {
            background: #444B6E;
            color: #FFFFEE;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            padding: 10px;
            word-break: break-all;
            border: 1px solid #708B75;
        }
        
        .mining-log {
            background: #444B6E;
            color: #FFFFEE;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            border: 1px solid #708B75;
        }
        
        .log-entry {
            margin-bottom: 2px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10pt;
        }
        
        .stat-value {
            color: #708B75;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .mining-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Proof celebration animations */
        @keyframes celebrate {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .celebrating {
            animation: celebrate 0.5s ease-in-out 3;
        }
        
        .floating-points {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 36px;
            font-weight: bold;
            color: #9AB87A;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            pointer-events: none;
            z-index: 9999;
            animation: floatUp 2s ease-out forwards;
        }
        
        @keyframes floatUp {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -100px);
            }
        }
    </style>
</head>
<body>
    <div class="mining-container">
        <div class="mining-header">
            <h1><a href="/" style="color: #FFFFEE; text-decoration: none;">Haichan</a></h1>
            <h2>⛏️ Mining Dashboard ⛏️</h2>
            <p>Client-side SHA-256 computation for discourse validation</p>
            <nav style="margin-top: 10px;">
                <a href="/boards" style="color: #FFFFEE; margin: 0 10px;">📋 Boards</a>
                <a href="/catalog" style="color: #FFFFEE; margin: 0 10px;">🗂️ Catalog</a>
            </nav>
        </div>

        <!-- Mining Controls -->
        <div class="mining-grid">
            <div class="mining-panel">
                <h3>Mining Controls</h3>
                <div style="margin-bottom: 10px;">
                    <button class="mining-button" id="start-mining" onclick="startMining()">Start Mining</button>
                    <button class="mining-button" id="stop-mining" onclick="stopMining()" disabled>Stop Mining</button>
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="font-size: 10pt; color: #444B6E;">Mining Mode:</label>
                    <select id="mining-mode" class="mining-select">
                        <option value="idle">IDLE (~100 H/s)</option>
                        <option value="active" selected>ACTIVE (~1K H/s)</option>
                        <option value="hyper">HYPER (~3K H/s)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 10pt; color: #444B6E;">Target Pattern:</label>
                    <select id="target-pattern" class="mining-select">
                        <optgroup label="Testing Patterns (Mining Page Only)">
                            <option value="21">21 (0.1 points) - Easy</option>
                            <option value="21e">21e (0.5 points) - Testing</option>
                        </optgroup>
                        <optgroup label="Production Patterns (Required for Threads/Replies)">
                            <option value="21e8" selected>21e8 (100 points) - Standard</option>
                            <option value="21e80">21e80 (500 points) - Hard</option>
                            <option value="21e800">21e800 (2500 points) - Very Hard</option>
                            <option value="21e8000">21e8000 (10000 points) - Extreme</option>
                        </optgroup>
                    </select>
                </div>
            </div>
            
            <div class="mining-panel">
                <h3>Mining Stats</h3>
                <div class="stat-row">
                    <span>Hash Rate:</span>
                    <span id="hash-rate" class="stat-value">0 H/s</span>
                </div>
                <div class="stat-row">
                    <span>Total Hashes:</span>
                    <span id="total-hashes" class="stat-value">0</span>
                </div>
                <div class="stat-row">
                    <span>Proofs Found:</span>
                    <span id="proofs-found" class="stat-value">0</span>
                </div>
                <div class="stat-row">
                    <span>Session Points:</span>
                    <span id="session-points" class="stat-value">0</span>
                </div>
                <div class="stat-row">
                    <span>Mining Time:</span>
                    <span id="mining-time" class="stat-value">00:00</span>
                </div>
            </div>
        </div>

        <!-- Current Hash Display -->
        <div class="mining-panel" style="margin-bottom: 20px;">
            <h3>Current Hash</h3>
            <div class="hash-display" id="current-hash-display">
                waiting for hash...
            </div>
            <div style="margin-top: 10px; font-size: 10pt; color: #444B6E;">
                Mining target: <span id="current-target" style="color: #708B75; font-weight: bold;">global</span>
            </div>
        </div>

        <!-- Mining Log -->
        <div class="mining-panel">
            <h3>Mining Log</h3>
            <div id="mining-log" class="mining-log">
                <div class="log-entry">🚀 Haichan Mining Dashboard Ready</div>
                <div class="log-entry">💡 Configure mining settings and click 'Start Mining'</div>
                <div class="log-entry">⚠️ PoW is MANDATORY for thread creation and replies</div>
            </div>
        </div>
    </div>

    <script>
        let miningActive = false;
        let miningInterval = null;
        let statsInterval = null;
        let startTime = null;
        let hashCount = 0;
        let proofsFound = 0;
        let sessionPoints = 0;
        let nonce = crypto.getRandomValues(new Uint32Array(1))[0];

        function startMining() {
            if (miningActive) return;
            
            miningActive = true;
            startTime = Date.now();
            hashCount = 0;
            
            document.getElementById('start-mining').disabled = true;
            document.getElementById('stop-mining').disabled = false;
            
            logMessage('✅ Mining started');
            
            // Start mining session with server
            fetch('/api/start-mining-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            }).catch(e => logMessage('⚠️ Session start failed: ' + e.message));
            
            // Start mining loop
            const mode = document.getElementById('mining-mode').value;
            let batchSize, intervalMs;
            
            if (mode === 'idle') {
                batchSize = 10;
                intervalMs = 100;
            } else if (mode === 'active') {
                batchSize = 50;
                intervalMs = 50;
            } else if (mode === 'hyper') {
                batchSize = 100;
                intervalMs = 30;
            }
            
            miningInterval = setInterval(() => {
                mineBatch(batchSize);
            }, intervalMs);
            
            // Update stats every second
            statsInterval = setInterval(updateStats, 1000);
        }
        
        function stopMining() {
            if (!miningActive) return;
            
            miningActive = false;
            
            if (miningInterval) {
                clearInterval(miningInterval);
                miningInterval = null;
            }
            
            if (statsInterval) {
                clearInterval(statsInterval);
                statsInterval = null;
            }
            
            document.getElementById('start-mining').disabled = false;
            document.getElementById('stop-mining').disabled = true;
            
            logMessage('⏹️ Mining stopped');
            
            // End mining session with server
            fetch('/api/end-mining-session', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            }).catch(e => logMessage('⚠️ Session end failed: ' + e.message));
        }
        
        async function mineBatch(batchSize) {
            if (!miningActive) return;
            
            const pattern = document.getElementById('target-pattern').value;
            const baseData = `global:haichan:${Date.now()}`;
            
            for (let i = 0; i < batchSize; i++) {
                if (!miningActive) break;
                
                const currentNonce = nonce + i;
                const testData = `${baseData}:${currentNonce}`;
                
                try {
                    const hash = await sha256(testData);
                    hashCount++;
                    
                    // Update current hash display occasionally
                    if (i % 10 === 0) {
                        document.getElementById('current-hash-display').textContent = hash;
                    }
                    
                    // Check if hash matches pattern
                    if (hash.toLowerCase().startsWith(pattern.toLowerCase())) {
                        foundProof(hash, currentNonce, testData, pattern);
                        break; // Found proof, reset and continue
                    }
                } catch (error) {
                    logMessage(`❌ Hashing error: ${error.message}`);
                }
            }
            
            nonce += batchSize;
        }
        
        async function sha256(message) {
            // Check for secure context (HTTPS or localhost)
            if (!window.isSecureContext) {
                throw new Error('Secure context required - Please use HTTPS');
            }
            
            if (crypto && crypto.subtle) {
                try {
                    const msgBuffer = new TextEncoder().encode(message);
                    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                } catch (error) {
                    logMessage(`❌ Crypto error: ${error.message}`);
                    throw new Error('SHA-256 computation failed: ' + error.message);
                }
            } else {
                throw new Error('Web Crypto API not available - HTTPS required for mining');
            }
        }
        
        async function foundProof(hash, nonce, data, pattern) {
            proofsFound++;
            const points = calculatePoints(pattern);
            sessionPoints += points;
            
            logMessage(`🎯 PROOF FOUND! ${hash} (${points} points)`);
            
            // Celebrate
            celebrateProof(points);
            
            // Submit proof to server
            try {
                const response = await fetch('/api/submit-proof', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        hash: hash,
                        nonce: nonce,
                        data: data,
                        pattern: pattern,
                        target_type: 'global',
                        target_id: 'haichan'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    logMessage(`✅ Proof accepted! +${points} points`);
                } else {
                    logMessage(`❌ Proof rejected: ${result.message}`);
                }
            } catch (error) {
                logMessage(`❌ Submit error: ${error.message}`);
            }
            
            // Reset nonce to continue mining
            nonce = crypto.getRandomValues(new Uint32Array(1))[0];
        }
        
        function celebrateProof(points) {
            // Add celebration class
            document.body.classList.add('celebrating');
            setTimeout(() => document.body.classList.remove('celebrating'), 1500);
            
            // Create floating points
            const pointsElement = document.createElement('div');
            pointsElement.className = 'floating-points';
            pointsElement.textContent = `+${points} points!`;
            document.body.appendChild(pointsElement);
            
            setTimeout(() => {
                if (pointsElement.parentNode) {
                    pointsElement.parentNode.removeChild(pointsElement);
                }
            }, 2000);
        }
        
        function calculatePoints(pattern) {
            const points = {
                '21': 0.1,
                '21e': 0.5,
                '21e8': 100,
                '21e80': 500,
                '21e800': 2500,
                '21e8000': 10000
            };
            return points[pattern] || 0.1;
        }
        
        function updateStats() {
            if (!startTime) return;
            
            const elapsed = (Date.now() - startTime) / 1000;
            const hashRate = Math.floor(hashCount / elapsed);
            
            document.getElementById('hash-rate').textContent = `${hashRate} H/s`;
            document.getElementById('total-hashes').textContent = hashCount.toLocaleString();
            document.getElementById('proofs-found').textContent = proofsFound;
            document.getElementById('session-points').textContent = sessionPoints.toFixed(1);
            
            const minutes = Math.floor(elapsed / 60);
            const seconds = Math.floor(elapsed % 60);
            document.getElementById('mining-time').textContent = 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        function logMessage(message) {
            const log = document.getElementById('mining-log');
            const timestamp = new Date().toLocaleTimeString();
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            entry.textContent = `[${timestamp}] ${message}`;
            log.appendChild(entry);
            log.scrollTop = log.scrollHeight;
            
            // Keep only last 100 entries
            while (log.children.length > 100) {
                log.removeChild(log.firstChild);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            logMessage('🔄 Mining dashboard initialized');
            
            // Check secure context and Web Crypto API availability
            if (!window.isSecureContext) {
                logMessage('⚠️ HTTPS required for mining - crypto.subtle unavailable over HTTP');
                logMessage('🔐 Please access this page via HTTPS for full functionality');
                document.getElementById('start-mining').disabled = true;
                document.getElementById('start-mining').textContent = 'HTTPS Required';
                document.getElementById('start-mining').style.background = '#dc3545';
            } else if (!crypto || !crypto.subtle) {
                logMessage('❌ Web Crypto API not available');
                document.getElementById('start-mining').disabled = true;
                document.getElementById('start-mining').textContent = 'Crypto API Missing';
                document.getElementById('start-mining').style.background = '#dc3545';
            } else {
                logMessage('✅ HTTPS secure context detected');
                logMessage('✅ Web Crypto API available - ready for mining');
                logMessage('💡 You can now mine real SHA-256 proofs!');
            }
        });
        
        // Auto-stop mining on page unload
        window.addEventListener('beforeunload', function() {
            if (miningActive) {
                stopMining();
            }
        });
    </script>
</body>
</html>