/**
 * TRANSPARENT PROOF-OF-WORK SYSTEM
 * Real-time PoW calculation and display with full transparency
 */

class TransparentPoWSystem {
    constructor() {
        this.isInitialized = false;
        this.activeMiningTargets = new Map();
        this.proofQueue = [];
        this.displayWarningLogged = false;
        this.systemStats = {
            totalProofs: 0,
            sessionProofs: 0,
            sessionPoints: 0,
            globalHashrate: 0
        };

        this.init();
    }

    init() {
        console.log('🔥 TRANSPARENT PoW SYSTEM INITIALIZING');

        // Initialize real-time PoW display
        this.setupRealtimeDisplay();

        // Setup transparent mining interface
        this.setupTransparentMining();

        // Initialize global stats
        this.updateGlobalStats();

        // Start real-time updates
        setInterval(() => this.updateRealtimeDisplay(), 1000);
        setInterval(() => this.updateGlobalStats(), 10000);

        this.isInitialized = true;
        console.log('✅ TRANSPARENT PoW SYSTEM READY');
    }

    setupRealtimeDisplay() {
        // PoW transparency overlay disabled - using main mini dashboard instead
        console.log('🎯 PoW transparency disabled - integrated into main dashboard');
        
        // Still create the toggle button but make it invisible/disabled
        const toggleBtn = document.createElement('button');
        toggleBtn.id = 'pow-transparency-btn';
        toggleBtn.textContent = '🔍 PoW';
        toggleBtn.style.cssText = `
            display: none;
        `;
        // toggleBtn.onclick = () => this.toggleTransparency();

        // document.body.appendChild(toggleBtn);
    }

    toggleTransparency() {
        const overlay = document.getElementById('pow-transparency-overlay');
        overlay.style.display = overlay.style.display === 'none' ? 'block' : 'none';
    }

    setupTransparentMining() {
        // Make ALL elements show their PoW potential on hover
        document.addEventListener('mouseover', (e) => {
            this.handleElementHover(e.target);
        });

        document.addEventListener('mouseout', (e) => {
            this.handleElementMouseOut(e.target);
        });
    }

    handleElementHover(element) {
        // Determine if element is mineable
        const mineableInfo = this.getMineableInfo(element);

        if (mineableInfo) {
            this.startTransparentMining(element, mineableInfo);
        }
    }

    handleElementMouseOut(element) {
        this.stopTransparentMining(element);
    }

    getMineableInfo(element) {
        // Check if element or parent has mining data
        let current = element;
        for (let i = 0; i < 5; i++) {
            if (!current) break;

            // Thread containers
            if (current.dataset && current.dataset.threadId) {
                return {
                    type: 'thread',
                    id: current.dataset.threadId,
                    title: current.dataset.threadTitle || `Thread #${current.dataset.threadId}`,
                    difficulty: '21e',
                    basePoints: 5
                };
            }

            // Post containers
            if (current.dataset && current.dataset.postId) {
                return {
                    type: 'post',
                    id: current.dataset.postId,
                    threadId: current.dataset.threadId,
                    title: `Post #${current.dataset.postId}`,
                    difficulty: '21e',
                    basePoints: 2
                };
            }

            // Images
            if (current.tagName === 'IMG') {
                return {
                    type: 'image',
                    id: `img_${Date.now()}`,
                    title: current.alt || 'Image',
                    difficulty: '21e',
                    basePoints: 3
                };
            }

            current = current.parentElement;
        }

        return null;
    }

    async startTransparentMining(element, info) {
        if (this.activeMiningTargets.has(element)) return;

        console.log(`🔥 Starting transparent mining on ${info.type} ${info.id}`);

        const miningData = {
            element,
            info,
            startTime: Date.now(),
            attempts: 0,
            hashrate: 0,
            isActive: true
        };

        this.activeMiningTargets.set(element, miningData);

        // Update display
        this.updateCurrentTarget(info);

        // Start actual mining
        this.performTransparentMining(miningData);
    }

    stopTransparentMining(element) {
        const miningData = this.activeMiningTargets.get(element);
        if (miningData) {
            miningData.isActive = false;
            this.activeMiningTargets.delete(element);

            // Clear display if this was the active target
            const targetEl = document.getElementById('pow-current-target');
            const hashrateEl = document.getElementById('pow-current-hashrate');
            
            if (targetEl) targetEl.textContent = 'Target: None';
            if (hashrateEl) hashrateEl.textContent = 'Hashrate: 0 H/s';
        }
    }

    async performTransparentMining(miningData) {
        const { info, element } = miningData;
        const targetPattern = info.difficulty;
        const challengeData = `${info.type}_${info.id}_${Date.now()}`;

        let nonce = 0;
        const startTime = Date.now();

        while (miningData.isActive && nonce < 10000) {
            const testData = `${challengeData}_${nonce}`;

            try {
                const hash = await this.calculateHash(testData);
                miningData.attempts = nonce + 1;

                // Update hashrate
                const elapsed = (Date.now() - startTime) / 1000;
                miningData.hashrate = Math.floor(miningData.attempts / elapsed);

                // Update display
                this.updateMiningProgress(miningData);

                if (hash.startsWith(targetPattern.toLowerCase())) {
                    console.log(`💎 PROOF FOUND! ${info.type} ${info.id}: ${hash}`);

                    await this.submitProof({
                        type: info.type,
                        targetId: info.id,
                        threadId: info.threadId,
                        hash,
                        nonce,
                        pattern: targetPattern,
                        challengeData
                    });

                    this.showProofSuccess(info, hash, miningData.attempts);
                    break;
                }

                nonce++;

                // Yield control every 100 attempts
                if (nonce % 100 === 0) {
                    await new Promise(resolve => setTimeout(resolve, 1));
                }

            } catch (error) {
                console.error('Mining error:', error);
                break;
            }
        }
    }

    async calculateHash(data) {
        const encoder = new TextEncoder();
        const dataBuffer = encoder.encode(data);
        const hashBuffer = await crypto.subtle.digest('SHA-256', dataBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    async submitProof(proof) {
        // API endpoint not implemented - using mock success for transparent mining display
        console.log(`📝 Mock proof submission: ${proof.type} ${proof.targetId} - ${proof.hash.substring(0, 16)}...`);
        
        // Mock successful response
        const mockPoints = this.calculateMockPoints(proof.pattern);
        const result = {
            success: true,
            points: mockPoints,
            message: 'Proof accepted (mock)'
        };

        // Update stats for display purposes
        this.systemStats.sessionProofs++;
        this.systemStats.sessionPoints += mockPoints;

        this.addRecentProof({
            type: proof.type,
            hash: proof.hash,
            points: mockPoints,
            timestamp: Date.now()
        });

        // Update thread display if applicable
        this.updateThreadDisplay(proof.threadId || proof.targetId, mockPoints);

        return result;
    }

    calculateMockPoints(pattern) {
        const pointMap = {
            '21': 0.1,
            '21e': 0.5,
            '21e8': 100,
            '21e80': 500,
            '21e800': 2500
        };
        return pointMap[pattern] || 0.1;
    }

    updateCurrentTarget(info) {
        const targetEl = document.getElementById('pow-current-target');
        const difficultyEl = document.getElementById('pow-current-difficulty');
        
        if (targetEl) targetEl.textContent = `Target: ${info.title}`;
        if (difficultyEl) difficultyEl.textContent = `Difficulty: ${info.difficulty}`;
        
        // Elements may not exist on all pages - this is normal
    }

    updateMiningProgress(miningData) {
        const hashrateEl = document.getElementById('pow-current-hashrate');
        const attemptsEl = document.getElementById('pow-current-attempts');
        
        if (hashrateEl) hashrateEl.textContent = `Hashrate: ${miningData.hashrate} H/s`;
        if (attemptsEl) attemptsEl.textContent = `Attempts: ${miningData.attempts}`;
    }

    updateRealtimeDisplay() {
        // Only update if elements exist - these are in the mining dashboard
        const proofsEl = document.getElementById('pow-session-proofs');
        const pointsEl = document.getElementById('pow-session-points');
        const hashrateEl = document.getElementById('pow-global-hashrate');
        
        if (proofsEl) proofsEl.textContent = this.systemStats.sessionProofs;
        if (pointsEl) pointsEl.textContent = this.systemStats.sessionPoints;
        if (hashrateEl) hashrateEl.textContent = `${this.systemStats.globalHashrate} H/s`;
        
        // Debug log when elements don't exist (only once to avoid spam)
        if (!proofsEl && !pointsEl && !hashrateEl && !this.displayWarningLogged) {
            console.log('ℹ️ PoW display elements not found on this page - transparent mining running without dashboard');
            this.displayWarningLogged = true;
        }
    }

    async updateGlobalStats() {
        // Temporarily disabled - API endpoint not implemented
        // TODO: Implement /api/proof-of-work/stats endpoint
        try {
            // Mock data for now
            this.systemStats.globalHashrate = Math.floor(Math.random() * 1000) + 500;
            this.systemStats.totalProofs = this.systemStats.totalProofs + Math.floor(Math.random() * 3);
        } catch (error) {
            console.error('Failed to update global stats:', error);
        }
    }

    addRecentProof(proof) {
        const container = document.getElementById('pow-recent-proofs');
        if (!container) return; // Element doesn't exist on this page
        
        const proofDiv = document.createElement('div');
        proofDiv.style.cssText = 'margin: 2px 0; padding: 2px; background: rgba(0,255,0,0.1); font-size: 10px;';

        const time = new Date(proof.timestamp).toLocaleTimeString();
        proofDiv.innerHTML = `
            <div>${time} | ${proof.type.toUpperCase()}</div>
            <div style="color: #FFFF00;">${proof.hash.substring(0, 16)}... (+${proof.points})</div>
        `;

        container.appendChild(proofDiv);

        // Keep only last 10 proofs
        const proofs = container.querySelectorAll('div[style*="background"]');
        if (proofs.length > 10) {
            proofs[0].remove();
        }
    }

    updateThreadDisplay(threadId, points) {
        // Find thread display elements and update PoW scores
        const threadElements = document.querySelectorAll(`[data-thread-id="${threadId}"]`);

        threadElements.forEach(element => {
            const powBadge = element.querySelector('.pow-badge, [id*="pow-number"]');
            if (powBadge) {
                const currentPoints = parseInt(powBadge.textContent.replace(/[^\d]/g, '')) || 0;
                const newTotal = currentPoints + points;
                powBadge.textContent = newTotal.toFixed(1);

                // Flash animation
                powBadge.style.animation = 'powUpdate 1s ease';
                setTimeout(() => {
                    powBadge.style.animation = '';
                }, 1000);
            }
        });
    }

    showProofSuccess(info, hash, attempts) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #000;
            color: #00FF00;
            border: 2px solid #00FF00;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            z-index: 10001;
            border-radius: 10px;
            text-align: center;
            animation: proofSuccess 3s ease-in-out;
        `;

        notification.innerHTML = `
            <div style="color: #FFFF00; font-weight: bold; margin-bottom: 10px;">
                💎 PROOF-OF-WORK FOUND! 💎
            </div>
            <div>Target: ${info.title}</div>
            <div>Hash: ${hash.substring(0, 20)}...</div>
            <div>Attempts: ${attempts.toLocaleString()}</div>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
}

// Global functions for theme integration
window.togglePoWTransparency = function() {
    if (window.transparentPoW) {
        window.transparentPoW.toggleTransparency();
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.transparentPoW = new TransparentPoWSystem();
});

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes powUpdate {
        0% { background-color: transparent; }
        50% { background-color: rgba(0, 255, 0, 0.5); }
        100% { background-color: transparent; }
    }

    @keyframes proofSuccess {
        0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
        20% { opacity: 1; transform: translate(-50%, -50%) scale(1.1); }
        80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
        100% { opacity: 0; transform: translate(-50%, -50%) scale(0.9); }
    }
`;
document.head.appendChild(style);

console.log('🔥 TRANSPARENT PoW SYSTEM LOADED');