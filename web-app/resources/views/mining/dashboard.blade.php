<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mining Arsenal - Haichan</title>
    <link rel="stylesheet" href="/css/haichan.css">
    <style>
        * { box-sizing: border-box; }
        
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #eee;
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 0;
        }

        .mining-arsenal {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
            padding-bottom: 80px;
        }

        .grid-layout {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .panel {
            background: rgba(15, 52, 96, 0.6);
            border: 2px solid #00d9ff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 217, 255, 0.2);
        }

        .panel h2 {
            color: #00d9ff;
            margin-top: 0;
            border-bottom: 1px solid #00d9ff;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .shooting-range {
            grid-column: 1 / -1;
            background: rgba(30, 30, 60, 0.8);
        }

        .target-canvas {
            background: #0a0a0a;
            border: 2px solid #ff6b6b;
            border-radius: 4px;
            height: 300px;
            position: relative;
            overflow: hidden;
            margin: 15px 0;
            cursor: crosshair;
        }

        .target {
            position: absolute;
            width: 60px;
            height: 60px;
            background: radial-gradient(circle, #ff6b6b 0%, #ff0000 50%, #8b0000 100%);
            border: 2px solid #fff;
            border-radius: 50%;
            cursor: pointer;
            transition: transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .target:hover {
            transform: scale(1.1);
        }

        .explosion {
            position: absolute;
            pointer-events: none;
            font-size: 40px;
            animation: explode 0.5s ease-out forwards;
        }

        @keyframes explode {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(3); opacity: 0; }
        }

        .mining-controls {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 15px 0;
        }

        .btn {
            background: linear-gradient(135deg, #00d9ff 0%, #0099cc 100%);
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            font-family: 'Courier New', monospace;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 217, 255, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #cc0000 100%);
            color: #fff;
        }

        .btn-success {
            background: linear-gradient(135deg, #51cf66 0%, #2b8a3e 100%);
            color: #fff;
        }

        input[type="text"], input[type="number"], textarea, select {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #00d9ff;
            color: #eee;
            padding: 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            width: 100%;
            margin: 5px 0;
        }

        textarea {
            min-height: 80px;
            resize: vertical;
        }

        .hash-output {
            background: #000;
            border: 1px solid #00ff00;
            color: #00ff00;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            word-break: break-all;
            margin: 10px 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin: 15px 0;
        }

        .stat-box {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #00d9ff;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            color: #00ff00;
            font-weight: bold;
        }

        .stat-label {
            font-size: 12px;
            color: #aaa;
            margin-top: 5px;
        }

        .target-selector {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .log-entry {
            background: rgba(0, 0, 0, 0.3);
            border-left: 3px solid #00d9ff;
            padding: 8px;
            margin: 5px 0;
            font-size: 12px;
        }

        .log-entry.success {
            border-left-color: #00ff00;
        }

        .log-entry.error {
            border-left-color: #ff0000;
        }

        .score-display {
            font-size: 48px;
            color: #00ff00;
            text-align: center;
            text-shadow: 0 0 20px rgba(0, 255, 0, 0.8);
            margin: 10px 0;
        }

    </style>
</head>
<body>
    @include('components.navigation')
    @include('components.info-stats-toolbar')

    <div class="mining-arsenal">
        <!-- ARCHIVED: Recursive 21e8 Mining Toolbar - Removed but code kept for future use -->

        <!-- Shooting Range -->
        <div class="panel shooting-range">
            <h2>🎯 SHOOTING RANGE</h2>
            <p>Click targets to mine them! Each hit submits a proof-of-work.</p>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value" id="range-score">0</div>
                    <div class="stat-label">SCORE</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="range-hits">0</div>
                    <div class="stat-label">HITS</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="range-misses">0</div>
                    <div class="stat-label">MISSES</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value" id="range-accuracy">100%</div>
                    <div class="stat-label">ACCURACY</div>
                </div>
            </div>

            <div class="target-canvas" id="targetCanvas"></div>

            <div class="mining-controls">
                <button class="btn btn-success" id="startRange">START RANGE</button>
                <button class="btn btn-danger" id="stopRange">STOP RANGE</button>
                <label>
                    Difficulty: 
                    <select id="rangeDifficulty">
                        <option value="easy">Easy (Slow)</option>
                        <option value="medium" selected>Medium</option>
                        <option value="hard">Hard (Fast)</option>
                        <option value="insane">Insane (Chaos)</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="grid-layout">
            <!-- Live SHA256 Hasher -->
            <div class="panel">
                <h2>🔐 LIVE SHA256 HASHER</h2>
                <p>Instant hash computation as you type</p>
                
                <textarea id="hashInput" placeholder="Type anything here..."></textarea>
                
                <div style="margin: 10px 0;">
                    <strong>SHA256:</strong>
                    <div class="hash-output" id="hashOutput">e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</div>
                </div>

                <div style="margin: 10px 0;">
                    <strong>Leading Zeros:</strong>
                    <span id="leadingZeros" style="color: #00ff00; font-size: 18px;">0</span>
                </div>

                <button class="btn" id="copyHash">📋 COPY HASH</button>
            </div>

            <!-- Target Mining -->
            <div class="panel">
                <h2>🎯 TARGET MINING</h2>
                <p>Mine yourself, others, or any thread</p>

                <div class="target-selector">
                    <label>Target Type:</label>
                    <select id="targetType">
                        <option value="self">Mine Myself</option>
                        <option value="user">Mine Another User</option>
                        <option value="thread">Mine a Thread</option>
                        <option value="custom">Custom Target</option>
                    </select>
                </div>

                <div id="targetInputArea" style="margin: 10px 0;">
                    <input type="text" id="targetInput" placeholder="Enter username, thread ID, or custom data..." style="display: none;">
                </div>

                <div class="mining-controls">
                    <button class="btn btn-success" id="startTargetMining">⛏️ START MINING</button>
                    <button class="btn btn-danger" id="stopTargetMining">⏹️ STOP</button>
                </div>

                <div style="margin: 10px 0;">
                    <strong>Status:</strong>
                    <div id="targetStatus" style="color: #ffff00;">Ready</div>
                </div>

                <div style="margin: 10px 0;">
                    <strong>Hash Rate:</strong>
                    <span id="targetHashRate" style="color: #00ff00; font-size: 20px;">0 H/s</span>
                </div>
            </div>
        </div>

        <!-- Mining Log -->
        <div class="panel">
            <h2>📊 MINING LOG</h2>
            <div id="miningLog" style="max-height: 300px; overflow-y: auto;">
                <div class="log-entry">System initialized. Ready to mine.</div>
            </div>
        </div>
    </div>

    <script nonce="{{ app('csp_nonce') }}">
        // Live SHA256 Hasher
        const hashInput = document.getElementById('hashInput');
        const hashOutput = document.getElementById('hashOutput');
        const leadingZeros = document.getElementById('leadingZeros');

        async function computeSHA256(text) {
            const encoder = new TextEncoder();
            const data = encoder.encode(text);
            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            return hashHex;
        }

        function countLeadingZeros(hash) {
            let count = 0;
            for (let char of hash) {
                if (char === '0') count++;
                else break;
            }
            return count;
        }

        hashInput.addEventListener('input', async () => {
            const text = hashInput.value;
            const hash = await computeSHA256(text);
            hashOutput.textContent = hash;
            leadingZeros.textContent = countLeadingZeros(hash);
        });

        document.getElementById('copyHash').addEventListener('click', () => {
            navigator.clipboard.writeText(hashOutput.textContent);
            addLog('Hash copied to clipboard!', 'success');
        });

        // Shooting Range
        let rangeActive = false;
        let rangeScore = 0;
        let rangeHits = 0;
        let rangeMisses = 0;
        let targets = [];
        let targetInterval;
        
        const targetCanvas = document.getElementById('targetCanvas');

        function createTarget() {
            const target = document.createElement('div');
            target.className = 'target';
            target.textContent = '🎯';
            
            const maxX = targetCanvas.clientWidth - 60;
            const maxY = targetCanvas.clientHeight - 60;
            
            target.style.left = Math.random() * maxX + 'px';
            target.style.top = Math.random() * maxY + 'px';
            
            target.addEventListener('click', (e) => {
                e.stopPropagation();
                hitTarget(target);
            });
            
            targetCanvas.appendChild(target);
            targets.push(target);
            
            const difficulty = document.getElementById('rangeDifficulty').value;
            let lifetime = 3000;
            if (difficulty === 'easy') lifetime = 4000;
            if (difficulty === 'hard') lifetime = 2000;
            if (difficulty === 'insane') lifetime = 1000;
            
            setTimeout(() => {
                if (target.parentElement) {
                    target.remove();
                    targets = targets.filter(t => t !== target);
                }
            }, lifetime);
        }

        async function hitTarget(target) {
            rangeHits++;
            const points = Math.floor(Math.random() * 10) + 5;
            rangeScore += points;
            
            const explosion = document.createElement('div');
            explosion.className = 'explosion';
            explosion.textContent = '💥';
            explosion.style.left = target.style.left;
            explosion.style.top = target.style.top;
            targetCanvas.appendChild(explosion);
            
            setTimeout(() => explosion.remove(), 500);
            
            target.remove();
            targets = targets.filter(t => t !== target);
            
            updateRangeStats();
            addLog(`Target hit! +${points} points`, 'success');
            
            // Submit mining proof
            await submitMiningProof(`target:${Date.now()}:${Math.random()}`);
        }

        targetCanvas.addEventListener('click', () => {
            if (rangeActive) {
                rangeMisses++;
                updateRangeStats();
                addLog('Missed!', 'error');
            }
        });

        function updateRangeStats() {
            document.getElementById('range-score').textContent = rangeScore;
            document.getElementById('range-hits').textContent = rangeHits;
            document.getElementById('range-misses').textContent = rangeMisses;
            const total = rangeHits + rangeMisses;
            const accuracy = total > 0 ? Math.round((rangeHits / total) * 100) : 100;
            document.getElementById('range-accuracy').textContent = accuracy + '%';
        }

        document.getElementById('startRange').addEventListener('click', () => {
            rangeActive = true;
            const difficulty = document.getElementById('rangeDifficulty').value;
            let interval = 1500;
            if (difficulty === 'easy') interval = 2000;
            if (difficulty === 'hard') interval = 1000;
            if (difficulty === 'insane') interval = 500;
            
            targetInterval = setInterval(createTarget, interval);
            addLog('Shooting range started!', 'success');
        });

        document.getElementById('stopRange').addEventListener('click', () => {
            rangeActive = false;
            clearInterval(targetInterval);
            targets.forEach(t => t.remove());
            targets = [];
            addLog('Shooting range stopped.', 'error');
        });

        // Target Mining
        let targetMiningActive = false;
        let targetMiningInterval;
        let hashRate = 0;

        document.getElementById('targetType').addEventListener('change', (e) => {
            const input = document.getElementById('targetInput');
            if (e.target.value === 'self') {
                input.style.display = 'none';
            } else {
                input.style.display = 'block';
                if (e.target.value === 'user') {
                    input.placeholder = 'Enter username...';
                } else if (e.target.value === 'thread') {
                    input.placeholder = 'Enter thread ID...';
                } else {
                    input.placeholder = 'Enter custom data...';
                }
            }
        });

        document.getElementById('startTargetMining').addEventListener('click', () => {
            if (targetMiningActive) return;
            
            targetMiningActive = true;
            const targetType = document.getElementById('targetType').value;
            let targetData = '';
            
            if (targetType === 'self') {
                targetData = 'self:{{ $user->username ?? "guest" }}';
            } else {
                const input = document.getElementById('targetInput').value;
                if (!input) {
                    addLog('Please enter target data!', 'error');
                    targetMiningActive = false;
                    return;
                }
                targetData = `${targetType}:${input}`;
            }
            
            document.getElementById('targetStatus').textContent = `Mining ${targetData}...`;
            document.getElementById('targetStatus').style.color = '#00ff00';
            addLog(`Started mining target: ${targetData}`, 'success');
            
            let hashCount = 0;
            const startTime = Date.now();
            
            targetMiningInterval = setInterval(async () => {
                const nonce = Date.now() + ':' + Math.random();
                const data = targetData + ':' + nonce;
                await submitMiningProof(data);
                
                hashCount++;
                const elapsed = (Date.now() - startTime) / 1000;
                hashRate = Math.round(hashCount / elapsed);
                document.getElementById('targetHashRate').textContent = hashRate + ' H/s';
            }, 100);
        });

        document.getElementById('stopTargetMining').addEventListener('click', () => {
            targetMiningActive = false;
            clearInterval(targetMiningInterval);
            document.getElementById('targetStatus').textContent = 'Stopped';
            document.getElementById('targetStatus').style.color = '#ff0000';
            addLog('Target mining stopped.', 'error');
        });

        // Mining proof submission
        async function submitMiningProof(data) {
            try {
                const hash = await computeSHA256(data);
                const leadingZeroCount = countLeadingZeros(hash);
                
                if (leadingZeroCount >= 3) {
                    addLog(`✨ Rare hash found! ${leadingZeroCount} leading zeros: ${hash.substring(0, 20)}...`, 'success');
                }
                
                // Submit to backend (simplified)
                const response = await fetch('/api/mining/submit-proof', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        challenge_token: 'mining_arsenal_' + Date.now(),
                        client_nonce: Math.floor(Math.random() * 1000000),
                        hash: hash,
                        data: data
                    })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        addLog(`Proof accepted! +${result.points} points`, 'success');
                    }
                }
            } catch (error) {
                console.error('Mining error:', error);
            }
        }

        // Logging
        function addLog(message, type = '') {
            const log = document.getElementById('miningLog');
            const entry = document.createElement('div');
            entry.className = 'log-entry ' + type;
            const timestamp = new Date().toLocaleTimeString();
            entry.textContent = `[${timestamp}] ${message}`;
            log.insertBefore(entry, log.firstChild);
            
            if (log.children.length > 50) {
                log.removeChild(log.lastChild);
            }
        }

        // Initialize
        addLog('Mining Arsenal loaded and ready.', 'success');

    </script>

</body>
</html>
