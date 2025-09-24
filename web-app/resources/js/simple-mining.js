// Simple Haichan Mining System v5.0 - Thread-focused dashboard with gradient power slider
// Default: OFF (user opt-in)

class SimpleMiner {
    constructor() {
        this.isActive = false;
        this.hashCount = 0;
        this.proofsFound = 0;
        this.startTime = 0;
        this.currentHash = '';
        this.pattern = '21e8';
        this.nonce = 0;

        // Targeting
        this.targetType = 'global';
        this.targetId = 'haichan';
        this.defaultTargetType = 'global';
        this.defaultTargetId = 'haichan';
        this.isHovering = false;

        // Power slider (0-100). 0 => OFF
        this.powerLevel = 0;
        this.mode = 'off'; // derived from powerLevel

        this.init();
    }

    init() {
        this.detectMiningTarget();
        this.setupDashboard();
        this.setupStatusBar();
        this.setupHoverMining();
        this.autoStart();
    }

    detectMiningTarget() {
        const path = window.location.pathname;

        // Thread pages like /gen/123 - prefer thread mining
        const threadMatch = path.match(/^\/(\w+)\/(\d+)$/);
        if (threadMatch) {
            this.targetType = 'thread';
            this.targetId = threadMatch[2];
        } else {
            this.targetType = 'global';
            this.targetId = 'haichan';
        }

        this.defaultTargetType = this.targetType;
        this.defaultTargetId = this.targetId;
    }

    autoStart() {
        const savedPower = parseInt(localStorage.getItem('haichan_power_level') || '0', 10);
        const savedProofCount = localStorage.getItem('haichan_proof_count') || '0';

        this.proofsFound = parseInt(savedProofCount, 10);
        this.setPowerLevel(isNaN(savedPower) ? 0 : savedPower);
    }

    setPowerLevel(level) {
        const clamped = Math.max(0, Math.min(100, parseInt(level, 10)));
        this.powerLevel = clamped;
        localStorage.setItem('haichan_power_level', String(this.powerLevel));

        if (this.powerLevel === 0) {
            this.mode = 'off';
            this.stop();
        } else {
            this.mode = 'variable';
            this.start();
        }

        this.updateMiningUI();
    }

    start() {
        if (this.isActive) return;

        console.log(`🔥 Starting mining (power ${this.powerLevel}%), target: ${this.targetType}:${this.targetId}`);
        this.isActive = true;
        this.startTime = Date.now();
        this.hashCount = 0;

        this.mine();
        this.updateMiningUI();
    }

    stop() {
        if (!this.isActive) return;
        this.isActive = false;
        console.log('⏸️ Mining stopped');
        this.updateMiningUI();
    }

    // Map power level to batch size and delay. Higher power → larger batch and shorter delay.
    computeWorkParams() {
        // Smooth non-linear scaling for better UX
        const p = this.powerLevel / 100; // 0..1
        const batchSize = Math.floor(5 + (p ** 0.8) * 195); // 5..200
        const delayMs = Math.max(10, Math.floor(250 - (p ** 0.8) * 240)); // 250..10
        return { batchSize, delayMs };
    }

    async mine() {
        if (!this.isActive) return;

        const { batchSize, delayMs } = this.computeWorkParams();

        for (let i = 0; i < batchSize && this.isActive; i++) {
            await this.performHash();
        }

        if (this.isActive) {
            setTimeout(() => this.mine(), delayMs);
        }
    }

    async performHash() {
        // Challenge data correlates with thread/global target
        const challengeData = `${this.targetType}:${this.targetId}`;
        const fullData = `${challengeData}:${this.nonce}`;
        const hash = await this.sha256(fullData);
        this.currentHash = hash;
        this.hashCount++;
        this.nonce++;

        if (hash.startsWith(this.pattern)) {
            console.log(`💎 PROOF FOUND! ${hash.substring(0, 16)}... after ${this.hashCount} attempts`);
            console.log(`📊 Challenge: ${challengeData} | Nonce: ${this.nonce - 1}`);
            this.flashDashboard();
            await this.submitProof(challengeData, this.nonce - 1, hash);
        }

        this.updateHashDisplay();
    }

    async sha256(text) {
        const encoder = new TextEncoder();
        const data = encoder.encode(text);
        const hashBuffer = await crypto.subtle.digest('SHA-256', data);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    flashDashboard() {
        const dashboard = document.getElementById('simple-mini-dashboard');
        if (dashboard) {
            dashboard.style.animation = 'haichan-seizure 0.5s ease-in-out';
            setTimeout(() => {
                dashboard.style.animation = '';
            }, 500);
        }
    }

    async submitProof(data, nonce, hash) {
        try {
            const response = await fetch('/api/proof', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    target_type: this.targetType,
                    target_id: this.targetId,
                    pattern: this.pattern,
                    hash,
                    nonce,
                    challenge_data: data,
                    hashes_computed: this.hashCount,
                    metadata: {
                        hash_rate: this.getHashRate(),
                        browser: navigator.userAgent.substring(0, 100),
                        power_level: this.powerLevel
                    }
                })
            });

            const result = await response.json();
            if (result.success) {
                this.proofsFound++;
                localStorage.setItem('haichan_proof_count', this.proofsFound.toString());
                console.log(`✅ Proof accepted! Total proofs: ${this.proofsFound}`);

                if (this.targetType === 'thread') {
                    this.updateThreadMiningBadge(result.new_difficulty || result.difficulty);

                    if (window.location.pathname.includes('/catalog')) {
                        this.reorderCatalogByPoW(this.targetId, result.new_difficulty || result.difficulty);
                    }
                }
            } else {
                console.log('❌ Proof rejected:', result.message);
            }
        } catch (error) {
            console.log('❌ Submit error:', error);
        }
    }

    getHashRate() {
        if (this.startTime === 0) return 0;
        const elapsed = (Date.now() - this.startTime) / 1000;
        return Math.floor(this.hashCount / elapsed);
    }

    updateHashDisplay() {
        const hashEl = document.getElementById('network-hashrate');
        if (hashEl) {
            hashEl.textContent = `${this.getHashRate().toLocaleString()} H/s`;
        }

        const currentHashEl = document.getElementById('current-mining-hash');
        if (currentHashEl && this.currentHash) {
            currentHashEl.textContent = this.currentHash.substring(0, 16) + '...';
        }
    }

    updateMiningUI() {
        // Update main layout mini dashboard elements
        const targetEl = document.getElementById('dashboard-target');
        const hashrateEl = document.getElementById('dashboard-hashrate');  
        const proofsEl = document.getElementById('dashboard-proofs');
        const hashEl = document.getElementById('dashboard-current-hash');
        const powerDisplay = document.getElementById('power-level-display');
        const powerSlider = document.getElementById('dashboard-power-slider');
        const statusEl = document.getElementById('dashboard-status');

        if (targetEl) targetEl.textContent = this.getDisplayName();
        if (hashrateEl) hashrateEl.textContent = `${this.getHashRate()} H/s`;
        if (proofsEl) proofsEl.textContent = this.proofsFound.toLocaleString();
        if (hashEl) hashEl.textContent = this.currentHash ? this.currentHash.substring(0, 32) + '...' : 'calculating...';
        if (powerDisplay) powerDisplay.textContent = Math.ceil(this.powerLevel / 10);
        if (powerSlider) powerSlider.value = Math.ceil(this.powerLevel / 10);
        if (statusEl) statusEl.textContent = this.powerLevel === 0 ? 'IDLE' : 'MINING';
    }

    setupHoverMining() {
        document.addEventListener('mouseover', (e) => {
            const threadElement = e.target.closest('[data-thread-id]');
            if (threadElement && threadElement.dataset.threadId) {
                const threadTitle = threadElement.dataset.threadTitle || `Thread ${threadElement.dataset.threadId}`;
                threadElement.style.cursor = 'crosshair';
                this.startHoverMining({
                    dataset: {
                        mineType: 'thread',
                        mineTarget: threadElement.dataset.threadId,
                        mineTitle: threadTitle
                    }
                });
                return;
            }

            const target = e.target.closest('[data-mine-type]');
            if (target) {
                target.style.cursor = 'crosshair';
                this.startHoverMining(target);
            }
        });

        document.addEventListener('mouseout', (e) => {
            const threadElement = e.target.closest('[data-thread-id]');
            const target = e.target.closest('[data-mine-type]');

            if (threadElement) {
                threadElement.style.cursor = '';
                this.stopHoverMining();
            } else if (target) {
                target.style.cursor = '';
                this.stopHoverMining();
            }
        });
    }

    startHoverMining(element) {
        const mineType = element.dataset.mineType;
        const mineTarget = element.dataset.mineTarget;
        const displayName = element.dataset.mineTitle || `${mineType} ${mineTarget}`;

        if (this.targetType !== mineType || this.targetId !== mineTarget) {
            this.isHovering = true;
            this.targetType = mineType;
            this.targetId = mineTarget;
            console.log(`👆 Hover mining: ${displayName}`);
        }
    }

    stopHoverMining() {
        if (this.isHovering) {
            this.isHovering = false;
            this.targetType = this.defaultTargetType;
            this.targetId = this.defaultTargetId;
            console.log(`↩️ Back to default mining: ${this.getDisplayName()}`);
        }
    }

    getDisplayName() {
        if (this.targetType === 'thread') {
            return `🧵 Thread #${this.targetId}`;
        } else if (this.targetType === 'reply') {
            return `💬 Reply #${this.targetId}`;
        } else {
            return `🌐 Global Mining`;
        }
    }

    setupStatusBar() {
        setInterval(() => {
            this.updateHashDisplay();
        }, 1000);
    }

    setupDashboard() {
        // Clean up old dashboards
        const oldDash = document.getElementById('mini-dashboard-overlay');
        if (oldDash) oldDash.remove();

        const simpleDash = document.getElementById('simple-mini-dashboard');
        if (simpleDash) simpleDash.remove();

        // No longer create the simple dashboard - use main layout mini dashboard instead
        console.log('🎯 Mining system initialized - use main mini dashboard');
    }

    // Public API methods
    switchTarget(type, id, title) {
        this.targetType = type;
        this.targetId = id;
        this.updateTargetDisplay(title);
    }

    updateTargetDisplay(customName = null) {
        const targetEl = document.getElementById('simple-target');
        if (targetEl) {
            targetEl.textContent = customName || this.getDisplayName();
        }
    }

    updateThreadMiningBadge(newDifficulty) {
        const threadPowEl = document.getElementById('thread-pow-number');
        if (threadPowEl && newDifficulty !== undefined) {
            const formattedDifficulty = Number(newDifficulty).toFixed(2);
            threadPowEl.textContent = formattedDifficulty;

            const badge = document.getElementById('thread-mining-badge');
            if (badge) {
                badge.style.animation = 'haichan-seizure 0.3s ease-in-out';
                setTimeout(() => {
                    badge.style.animation = '';
                }, 300);
            }

            console.log(`🔄 Thread mining badge updated to: ${formattedDifficulty}`);
        }
    }

    reorderCatalogByPoW(threadId, newPoW) {
        const catalogGrid = document.querySelector('.catalog-grid');
        if (!catalogGrid) return;

        const threads = Array.from(catalogGrid.querySelectorAll('.catalog-thread'));

        const minedThread = threads.find(t => t.dataset.threadId === threadId);
        if (minedThread) {
            let powBadge = minedThread.querySelector('.catalog-pow-badge');
            if (!powBadge) {
                powBadge = document.createElement('div');
                powBadge.className = 'catalog-pow-badge';
                minedThread.appendChild(powBadge);
            }
            powBadge.textContent = `${Number(newPoW).toFixed(1)}⚡`;
            powBadge.dataset.powValue = newPoW;

            minedThread.style.animation = 'haichan-seizure 0.5s ease-in-out';
            setTimeout(() => {
                minedThread.style.animation = '';
            }, 500);
        }

        threads.sort((a, b) => {
            const aPowBadge = a.querySelector('.catalog-pow-badge');
            const bPowBadge = b.querySelector('.catalog-pow-badge');

            const aPow = aPowBadge ? parseFloat(aPowBadge.dataset.powValue || aPowBadge.textContent.replace('⚡', '')) || 0 : 0;
            const bPow = bPowBadge ? parseFloat(bPowBadge.dataset.powValue || bPowBadge.textContent.replace('⚡', '')) || 0 : 0;

            return bPow - aPow;
        });

        threads.forEach(thread => catalogGrid.appendChild(thread));

        console.log(`🔄 Catalog reordered! Thread ${threadId} with PoW ${newPoW} moved to position`);
    }

    getStats() {
        return {
            hashRate: this.getHashRate(),
            proofsFound: this.proofsFound,
            target: this.getDisplayName(),
            powerLevel: this.powerLevel,
            currentHash: this.currentHash
        };
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Initializing Haichan Miner (thread-focused, OFF by default)...');
    window.simpleMiner = new SimpleMiner();
});

// Export for global access
window.SimpleMiner = SimpleMiner;