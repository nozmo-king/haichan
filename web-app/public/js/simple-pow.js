/**
 * Simple Proof-of-Work System
 * Real mining with mouseover functionality and bottom toolbar
 */

class SimpleProofOfWork {
    constructor() {
        console.log('🔨 Simple PoW: Initialized');
    }

    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async acquireProofFor(payload) {
        console.log('🔨 Simple PoW: Getting challenge for', payload);
        
        // Validate payload
        if (!payload.target_type || !payload.action || !payload.difficulty) {
            throw new Error('Invalid payload: missing required fields (target_type, action, difficulty)');
        }
        
        // 1. Get challenge from server
        const challengeResponse = await fetch('/api/mining/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(payload)
        });

        if (!challengeResponse.ok) {
            const errorText = await challengeResponse.text();
            console.error('🔨 Simple PoW: Challenge request failed:', challengeResponse.status, errorText);
            throw new Error('Failed to get challenge: ' + challengeResponse.statusText);
        }

        const challenge = await challengeResponse.json();
        
        if (!challenge.success) {
            console.error('🔨 Simple PoW: Challenge response failed:', challenge);
            throw new Error('Challenge failed: ' + (challenge.message || 'Unknown error'));
        }

        console.log('🔨 Simple PoW: Challenge received', challenge);

        // 2. Mine proof
        const challengeData = JSON.stringify(challenge.canonical_payload);
        console.log('🔨 Simple PoW: Starting mining with data:', challengeData);
        const proof = await this.mine(challengeData, payload.difficulty);
        
        console.log('🔨 Simple PoW: Proof found', proof);

        // 3. Return proof with challenge token
        return {
            nonce: proof.nonce,
            hash: proof.hash,
            challenge_id: challenge.token
        };
    }

    async mine(data, difficulty) {
        console.log('🔨 Simple PoW: Mining with difficulty', difficulty);
        
        let nonce = 0;
        const maxAttempts = 1000000; // Prevent infinite loops
        
        while (nonce < maxAttempts) {
            const testData = data + ':' + nonce;
            const hash = await this.sha256(testData);
            
            if (hash.toLowerCase().startsWith(difficulty.toLowerCase())) {
                console.log('🔨 Simple PoW: Found valid hash after', nonce, 'attempts');
                return { nonce, hash };
            }
            
            nonce++;
            
            // Update progress every 10000 hashes
            if (nonce % 10000 === 0) {
                console.log('🔨 Simple PoW: Progress -', nonce, 'hashes attempted');
                // Allow UI to update
                await new Promise(resolve => setTimeout(resolve, 1));
            }
        }
        
        throw new Error('Mining failed: Max attempts reached');
    }
}

// SimpleMouseoverMiner class for seamless background mining
class SimpleMouseoverMiner {
    constructor(pow) {
        this.pow = pow;
        this.currentTarget = null;
        this.enabled = true;
        this.currentDifficulty = '21e8';
        this.stats = { proofs: 0, points: 0, hashes: 0 };
        this.setupMouseoverEvents();
        console.log('🖱️ Mouseover mining: Initialized');
    }

    setupMouseoverEvents() {
        document.addEventListener('mouseover', (e) => {
            if (!this.enabled) return;
            
            const target = e.target.closest('[data-mine-type]');
            if (target && target !== this.currentTarget) {
                this.startMining(target);
            }
        });

        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('[data-mine-type]');
            if (target === this.currentTarget) {
                this.stopMining();
            }
        });
    }

    async startMining(element) {
        if (!this.enabled) return;
        
        this.currentTarget = element;
        
        const mineType = element.dataset.mineType;
        const threadId = element.dataset.threadId;
        const postId = element.dataset.postId;
        const boardCode = element.dataset.boardCode || 'd';

        try {
            let targetType, targetId;
            
            if (mineType === 'thread' || mineType === 'thread-op') {
                targetType = 'thread';
                targetId = threadId;
            } else if (mineType === 'post') {
                targetType = 'post';  
                targetId = postId;
            } else {
                targetType = 'general';
                targetId = null;
            }

            // Use current difficulty from toolbar
            const proof = await this.pow.acquireProofFor({
                board_code: boardCode,
                target_type: targetType,
                target_id: targetId,
                action: 'mine',
                difficulty: this.currentDifficulty
            });

            if (proof) {
                this.showSubtleEffect(element);
                await this.submitRealProof(proof, targetType, targetId, boardCode, this.currentDifficulty);
                
                // Update stats
                this.stats.proofs++;
                this.stats.points += this.calculatePoints(this.currentDifficulty);
                this.stats.hashes += parseInt(proof.nonce) || 1;
                
                if (window.miningToolbar) {
                    window.miningToolbar.updateStats(this.stats.proofs, this.stats.points, this.stats.hashes);
                }
            }
        } catch (error) {
            console.log('Mining failed silently:', error);
        }
    }

    calculatePoints(difficulty) {
        const points = {
            '5': 0.01, '4': 0.02, '3': 0.05, '2': 0.1,
            '21': 0.1, '21e': 0.5, '21e8': 100
        };
        return points[difficulty] || 0.1;
    }

    async submitRealProof(proof, targetType, targetId, boardCode, difficulty) {
        try {
            const response = await fetch('/api/proof-submissions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    challenge_token: proof.challenge_id,
                    client_nonce: proof.nonce,
                    hash: proof.hash
                })
            });
            
            if (response.ok) {
                console.log('✅ Proof submitted successfully');
            }
        } catch (error) {
            console.log('Failed to submit proof:', error);
        }
    }

    showSubtleEffect(element) {
        // Visual feedback for successful mining
        const original = {
            boxShadow: element.style.boxShadow,
            transform: element.style.transform,
            border: element.style.border
        };
        
        // Subtle glow and pulse effect
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = '0 0 8px rgba(76, 175, 80, 0.6)';
        element.style.transform = 'scale(1.02)';
        element.style.border = '1px solid rgba(76, 175, 80, 0.3)';
        
        // Add small floating indicator
        const indicator = document.createElement('div');
        indicator.innerHTML = '⚡';
        indicator.style.cssText = `
            position: absolute;
            top: -10px;
            right: 5px;
            font-size: 12px;
            color: #4CAF50;
            pointer-events: none;
            animation: fadeInOut 1s ease-out forwards;
            z-index: 1000;
        `;
        
        // Add animation if not exists
        if (!document.getElementById('mining-animations')) {
            const style = document.createElement('style');
            style.id = 'mining-animations';
            style.textContent = `
                @keyframes fadeInOut {
                    0% { opacity: 0; transform: translateY(5px); }
                    50% { opacity: 1; transform: translateY(-5px); }
                    100% { opacity: 0; transform: translateY(-15px); }
                }
            `;
            document.head.appendChild(style);
        }
        
        element.style.position = element.style.position || 'relative';
        element.appendChild(indicator);
        
        // Reset after animation
        setTimeout(() => {
            element.style.boxShadow = original.boxShadow;
            element.style.transform = original.transform;
            element.style.border = original.border;
            if (indicator.parentNode) {
                indicator.remove();
            }
        }, 1000);
    }

    stopMining() {
        this.currentTarget = null;
    }
}

// Mining toolbar class
class MiningToolbar {
    constructor(miner) {
        this.miner = miner;
        this.power = 5; // 1-10 scale
        this.stats = { proofs: 0, points: 0, hashes: 0 };
        this.createToolbar();
        this.updateMiningDifficulty();
    }

    createToolbar() {
        // Remove any existing toolbar first
        const existingToolbar = document.getElementById('mining-toolbar');
        if (existingToolbar) {
            existingToolbar.remove();
        }
        
        const toolbar = document.createElement('div');
        toolbar.id = 'mining-toolbar';
        toolbar.style.cssText = `
            position: fixed !important;
            bottom: 0 !important;
            left: 0 !important;
            right: 0 !important;
            height: 60px !important;
            background: linear-gradient(135deg, #708B75, #5a7860) !important;
            color: #F5F5DC !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 0 20px !important;
            font-family: monospace !important;
            font-size: 12px !important;
            z-index: 99999 !important;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.3) !important;
            border-top: 2px solid #9AB87A !important;
        `;

        toolbar.innerHTML = `
            <div style="display: flex; align-items: center; gap: 15px;">
                <span>⚡ Mining Difficulty: <strong>21e8</strong></span>
                <span id="power-value">Power: ${this.power}/10</span>
            </div>
            <div style="display: flex; align-items: center; gap: 15px;">
                <span id="mining-stats">Proofs: ${this.stats.proofs} | Points: ${this.stats.points} | Hashes: ${this.stats.hashes}</span>
                <button id="toggle-mining" style="padding: 5px 10px; background: #4CAF50; border: none; border-radius: 3px; color: white; cursor: pointer;">ON</button>
            </div>
        `;

        document.body.appendChild(toolbar);
        
        // Add bottom padding to body to prevent toolbar from covering content
        document.body.style.paddingBottom = '80px';
        
        this.setupToolbarEvents();
        console.log('🎯 Mining toolbar created and added to DOM');
    }

    setupToolbarEvents() {
        const powerValue = document.getElementById('power-value');
        const toggleBtn = document.getElementById('toggle-mining');

        toggleBtn.addEventListener('click', () => {
            if (this.miner.enabled) {
                this.miner.enabled = false;
                toggleBtn.textContent = 'OFF';
                toggleBtn.style.background = '#f44336';
            } else {
                this.miner.enabled = true;
                toggleBtn.textContent = 'ON';
                toggleBtn.style.background = '#4CAF50';
            }
        });
    }

    updateMiningDifficulty() {
        // All mining uses 21e8 for now
        this.miner.currentDifficulty = '21e8';
    }

    updateStats(proofs, points, hashes) {
        this.stats = { proofs, points, hashes };
        const statsEl = document.getElementById('mining-stats');
        if (statsEl) {
            statsEl.textContent = `Proofs: ${proofs} | Points: ${points.toFixed(1)} | Hashes: ${hashes}`;
        }
    }
}

// Initialize immediately
console.log('🔨 Simple PoW: Creating instance...');
window.simplePoW = new SimpleProofOfWork();
window.haichanMiningBrain = window.simplePoW; // Compatibility alias

// Initialize everything when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
        window.miningToolbar = new MiningToolbar(window.mouseoverMiner);
    });
} else {
    window.mouseoverMiner = new SimpleMouseoverMiner(window.simplePoW);
    window.miningToolbar = new MiningToolbar(window.mouseoverMiner);
}

console.log('🔨 Simple PoW: Ready with mouseover mining and toolbar');