@extends('layout')

@section('title', 'Mining Dashboard - Haichan')

@section('content')
    <style>
        /* Proof celebration animations */
        @keyframes floatUp {
            0% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -70%) scale(1.2);
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
            20%, 40%, 60%, 80% { transform: translateX(2px); }
        }
        
        @keyframes rainbow {
            0% { filter: hue-rotate(0deg); }
            100% { filter: hue-rotate(360deg); }
        }
        
        @keyframes pulse {
            0%, 100% { 
                box-shadow: 0 0 5px rgba(154, 184, 122, 0.5);
                transform: scale(1);
            }
            50% { 
                box-shadow: 0 0 20px rgba(154, 184, 122, 0.8);
                transform: scale(1.02);
            }
        }
        
        @keyframes glow {
            0%, 100% { 
                text-shadow: 0 0 5px rgba(154, 184, 122, 0.5);
            }
            50% { 
                text-shadow: 0 0 15px rgba(154, 184, 122, 1);
            }
        }
        
        /* Celebration styles by rarity */
        body.proof-celebration {
            animation: shake 0.5s ease-in-out;
        }
        
        body.celebration-common h2,
        body.celebration-common h3 {
            animation: pulse 1s ease-in-out 3;
            color: #9AB87A !important;
        }
        
        body.celebration-uncommon {
            animation: shake 0.3s ease-in-out 3;
        }
        
        body.celebration-uncommon h2,
        body.celebration-uncommon h3 {
            animation: pulse 0.8s ease-in-out 4;
            color: #708B75 !important;
        }
        
        body.celebration-rare {
            animation: shake 0.2s ease-in-out 5;
        }
        
        body.celebration-rare h2,
        body.celebration-rare h3 {
            animation: glow 0.6s ease-in-out 5;
            color: #ff6b35 !important;
        }
        
        body.celebration-epic {
            animation: shake 0.15s ease-in-out 8;
        }
        
        body.celebration-epic h2,
        body.celebration-epic h3 {
            animation: rainbow 2s linear 1.5, glow 0.4s ease-in-out 8;
            color: #9AB87A !important;
        }
        
        body.celebration-legendary {
            animation: shake 0.1s ease-in-out 15;
        }
        
        body.celebration-legendary h2,
        body.celebration-legendary h3 {
            animation: rainbow 1s linear 3, glow 0.2s ease-in-out 15;
            color: #ffd700 !important;
        }
        
        body.celebration-legendary {
            background: linear-gradient(45deg, #3D315B, #444B6E, #708B75, #9AB87A) !important;
            background-size: 400% 400% !important;
            animation: rainbow 2s linear infinite, shake 0.1s ease-in-out 15 !important;
        }
        
        /* Floating points animation */
        .floating-points {
            font-family: 'Times New Roman', serif !important;
        }
    </style>
    <div style="padding: 20px;">
        <div style="text-align: center; margin-bottom: 30px; padding: 20px; background: #F5F5DC; border: 1px solid #708B75; border-radius: 5px;">
            <h2 style="color: #444B6E; margin-bottom: 10px; font-size: 24px;">
                <span class="strobing-emoji">⛏️</span> Mining Dashboard <span class="strobing-emoji">⛏️</span>
            </h2>
            <p style="color: #666; font-size: 14px; margin: 0;">Client-side SHA-256 computation for discourse validation</p>
        </div>

        <!-- Mining Controls -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div style="background: #F5F5DC; border: 1px solid #708B75; border-radius: 5px; padding: 15px;">
                <h3 style="color: #444B6E; margin-bottom: 10px; font-size: 16px;">Mining Controls</h3>
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <button class="btn-primary" id="start-mining" onclick="startMining()">Start Mining</button>
                    <button class="btn" id="stop-mining" onclick="stopMining()" disabled>Stop Mining</button>
                </div>
                <div style="margin-bottom: 10px;">
                    <label style="font-size: 12px; color: #666;">Mining Mode:</label>
                    <select id="mining-mode" style="width: 100%; padding: 5px; border: 1px solid #708B75; border-radius: 3px;">
                        <option value="idle">IDLE (~100 H/s)</option>
                        <option value="active">ACTIVE (~1K H/s)</option>
                        <option value="hyper">HYPER (~3K H/s)</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 12px; color: #666;">Target Pattern:</label>
                    <select id="target-pattern" style="width: 100%; padding: 5px; border: 1px solid #708B75; border-radius: 3px;">
                        <option value="21">21 (0.1 points)</option>
                        <option value="21e8" selected>21e8 (1 point)</option>
                        <option value="21e80">21e80 (5 points)</option>
                        <option value="21e800">21e800 (25 points)</option>
                        <option value="21e8000">21e8000 (100 points)</option>
                        <option value="21e80000">21e80000 (500 points)</option>
                    </select>
                </div>
            </div>
            
            <div style="background: #F5F5DC; border: 1px solid #708B75; border-radius: 5px; padding: 15px;">
                <h3 style="color: #444B6E; margin-bottom: 10px; font-size: 16px;">Mining Stats</h3>
                <div style="font-size: 12px; line-height: 1.6;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Hash Rate:</span>
                        <span id="hash-rate" style="color: #9AB87A; font-weight: bold;">0 H/s</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Total Hashes:</span>
                        <span id="total-hashes" style="color: #9AB87A; font-weight: bold;">0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Proofs Found:</span>
                        <span id="proofs-found" style="color: #9AB87A; font-weight: bold;">0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Mining Time:</span>
                        <span id="mining-time" style="color: #9AB87A; font-weight: bold;">00:00</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Targeted Mining Section -->
        <div style="background: #F5F5DC; border: 1px solid #708B75; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
            <h3 style="color: #444B6E; margin-bottom: 10px; font-size: 16px;">🎯 Targeted Mining</h3>
            <div style="margin-bottom: 10px;">
                <label style="font-size: 12px; color: #666;">Mine specific thread/post (enter SHA256 hash):</label>
                <input type="text" id="target-hash" placeholder="Enter SHA256 hash of thread or post content..."
                       style="width: 100%; padding: 8px; border: 1px solid #708B75; border-radius: 3px; font-family: monospace; font-size: 11px;">
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button class="btn" onclick="setTargetHash()">Set Target</button>
                <button class="btn" onclick="clearTargetHash()">Clear (Global Mining)</button>
                <span id="target-status" style="font-size: 11px; color: #666; font-style: italic;">Global mining mode</span>
            </div>
            <div style="margin-top: 10px; font-size: 11px; color: #999;">
                💡 Tip: Copy SHA256 hash from thread/post headers or use browser dev tools to inspect content hashes
            </div>
        </div>

        <!-- Current Hash Display -->
        <div style="background: #F5F5DC; border: 1px solid #708B75; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
            <h3 style="color: #444B6E; margin-bottom: 10px; font-size: 16px;">Current Hash</h3>
            <div style="background: #000; color: #00ff00; font-family: 'Courier New', monospace; font-size: 12px; padding: 10px; border-radius: 3px; word-break: break-all; text-shadow: 0 0 3px #00ff00;" id="current-hash-display">
                waiting for hash...
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                Mining target: <span id="current-target" style="color: #444B6E; font-weight: bold;">global</span>
            </div>
        </div>

        <!-- Mining Log -->
        <div style="background: #F5F5DC; border: 1px solid #708B75; border-radius: 5px; padding: 15px;">
            <h3 style="color: #444B6E; margin-bottom: 10px; font-size: 16px;">Mining Log</h3>
            <div id="mining-log" style="background: #000; color: #00ff00; font-family: 'Courier New', monospace; font-size: 11px; padding: 10px; border-radius: 3px; height: 200px; overflow-y: auto;">
                <div class="log-entry">🚀 Haichan Mining Dashboard Ready</div>
                <div class="log-entry">💡 Configure mining settings and click 'Start Mining'</div>
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
        let targetHash = null;

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
            });
            
            // Start mining loop with optimized batch sizes and timing
            const mode = document.getElementById('mining-mode').value;
            let batchSize, intervalMs;
            
            if (mode === 'idle') {
                batchSize = 5;   // ~100 H/s
                intervalMs = 50;
            } else if (mode === 'active') {
                batchSize = 50;  // ~1000 H/s  
                intervalMs = 50;
            } else if (mode === 'hyper') {
                batchSize = 150; // ~3000 H/s
                intervalMs = 50;
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
            });
        }
        
        async function mineBatch(batchSize) {
            if (!miningActive) return;
            
            const pattern = document.getElementById('target-pattern').value;
            const baseData = generateMiningData();
            
            // Process hashes in parallel for better performance
            const promises = [];
            
            for (let i = 0; i < batchSize; i++) {
                const currentNonce = nonce + i;
                const testData = `${baseData}:${currentNonce}`;
                
                promises.push(
                    sha256(testData).then(hash => {
                        hashCount++;
                        
                        // Update current hash display occasionally
                        if (i % 10 === 0) {
                            document.getElementById('current-hash-display').textContent = hash;
                        }
                        
                        // Check if hash matches pattern
                        if (hash.toLowerCase().startsWith(pattern.toLowerCase())) {
                            foundProof(hash, currentNonce, testData, pattern);
                        }
                        
                        return hash;
                    }).catch(error => {
                        logMessage(`❌ Hashing error: ${error.message}`);
                        return null;
                    })
                );
            }
            
            try {
                await Promise.all(promises);
                nonce += batchSize;
            } catch (error) {
                logMessage(`❌ Batch error: ${error.message}`);
            }
        }
        
        function generateMiningData() {
            const timestamp = Date.now();
            if (targetHash) {
                return `target:${targetHash}:${timestamp}`;
            } else {
                return `global:haichan:${timestamp}`;
            }
        }

        function setTargetHash() {
            const hashInput = document.getElementById('target-hash').value.trim();
            if (!hashInput) {
                alert('Please enter a SHA256 hash');
                return;
            }

            // Validate hash format (64 hex characters)
            if (!/^[a-fA-F0-9]{64}$/.test(hashInput)) {
                alert('Invalid SHA256 hash format. Must be 64 hexadecimal characters.');
                return;
            }

            targetHash = hashInput.toLowerCase();
            document.getElementById('target-status').textContent = `Targeting: ${targetHash}`;
            document.getElementById('current-target').textContent = `custom hash (${targetHash})`;

            logMessage(`🎯 Target set: ${targetHash}`);
        }

        function clearTargetHash() {
            targetHash = null;
            document.getElementById('target-hash').value = '';
            document.getElementById('target-status').textContent = 'Global mining mode';
            document.getElementById('current-target').textContent = 'global';

            logMessage('🌐 Switched to global mining mode');
        }
        
        async function sha256(message) {
            // Check if crypto.subtle is available (HTTPS/localhost context)
            if (crypto && crypto.subtle) {
                try {
                    const msgBuffer = new TextEncoder().encode(message);
                    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
                    const hashArray = Array.from(new Uint8Array(hashBuffer));
                    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                } catch (error) {
                    // Fallback to JavaScript implementation
                    return sha256Fallback(message);
                }
            } else {
                // Fallback to JavaScript implementation
                return sha256Fallback(message);
            }
        }
        
        function sha256Fallback(message) {
            // NO FALLBACK - This is a production system requiring real SHA-256
            throw new Error('Crypto.subtle unavailable - Real SHA-256 computation required for production');
        }
        
        async function foundProof(hash, nonce, data, pattern) {
            proofsFound++;
            const points = calculatePoints(pattern);
            sessionPoints += points;
            
            logMessage(`🎯 PROOF FOUND! ${hash} (${points} points)`);
            
            // Trigger celebration animations
            celebrateProof(pattern, points);
            
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
                        target_type: targetHash ? 'custom' : 'global',
                        target_id: targetHash ? targetHash : 'haichan'
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
        
        function celebrateProof(pattern, points) {
            // Add celebration class to body
            document.body.classList.add('proof-celebration');
            
            // Get celebration intensity based on rarity
            let intensity = 'common';
            if (pattern === '000021e8') intensity = 'legendary';
            else if (pattern === '21e8000') intensity = 'epic';
            else if (pattern === '21e800') intensity = 'rare';
            else if (pattern === '21e80') intensity = 'uncommon';
            
            document.body.classList.add(`celebration-${intensity}`);
            
            // Create floating points animation
            createFloatingPoints(points);
            
            // Remove celebration after 3 seconds
            setTimeout(() => {
                document.body.classList.remove('proof-celebration');
                document.body.classList.remove(`celebration-${intensity}`);
            }, 3000);
        }
        
        function createFloatingPoints(points) {
            const pointsElement = document.createElement('div');
            pointsElement.className = 'floating-points';
            pointsElement.textContent = `+${points} points!`;
            pointsElement.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 48px;
                font-weight: bold;
                color: #9AB87A;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
                pointer-events: none;
                z-index: 9999;
                animation: floatUp 3s ease-out forwards;
            `;
            
            document.body.appendChild(pointsElement);
            
            // Remove after animation
            setTimeout(() => {
                if (pointsElement.parentNode) {
                    pointsElement.parentNode.removeChild(pointsElement);
                }
            }, 3000);
        }
        
        function calculatePoints(pattern) {
            const points = {
                '21': 0.1,
                '21e8': 1,
                '21e80': 5,
                '21e800': 25,
                '21e8000': 100,
                '21e80000': 500
            };
            return points[pattern] || 0.1;
        }
        
        function updateStats() {
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
            
            // Keep only last 50 entries
            while (log.children.length > 50) {
                log.removeChild(log.firstChild);
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            logMessage('🔄 Mining dashboard initialized');
        });
    </script>
@endsection