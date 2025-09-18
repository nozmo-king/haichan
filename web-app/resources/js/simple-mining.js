// Simple Haichan Mining System v4.0 - ULTRA CLEAN VERSION
// NO PROBLEMATIC TEXT OR SANITIZERS AT ALL

class SimpleMiner {
    constructor() {
        this.isActive = false;
        this.hashCount = 0;
        this.proofsFound = 0; // Track successful proofs found
        this.startTime = 0;
        this.currentHash = '';
        this.pattern = '21e8'; // Should hit roughly every 5 seconds at typical mining rates
        this.nonce = 0;
        this.mode = 'off';
        this.targetType = 'global';
        this.targetId = 'haichan';
        this.isHovering = false;
        this.defaultTargetType = 'global';
        this.defaultTargetId = 'haichan';

        this.init();
    }

    init() {
        this.detectMiningTarget();
        this.setupMiniDashboard();
        this.setupStatusBar();
        this.setupHoverMining();
        this.autoStart();
    }

    detectMiningTarget() {
        const path = window.location.pathname;

        // Special pages - should use global mining
        if (path.match(/^\/boards\/?$/) || path.match(/^\/mining\/?$/) || path.match(/^\/faq\/?$/) || path.match(/^\/rules\/?$/)) {
            this.targetType = 'global';
            this.targetId = 'haichan';
        } else if (path.match(/^\/library\/?$/)) {
            this.targetType = 'images';
            this.targetId = 'library';
        } else if (path.match(/^\/(\w+)\/(\d+)$/)) {
            // Thread pages like /gen/123
            const matches = path.match(/^\/(\w+)\/(\d+)$/);
            this.targetType = 'thread';
            this.targetId = matches[2];
        } else if (path.match(/^\/(\w+)\/catalog$/)) {
            // Catalog pages like /gen/catalog
            const matches = path.match(/^\/(\w+)\/catalog$/);
            this.targetType = 'board';
            this.targetId = matches[1];
        } else if (path.match(/^\/(gen|tech|biz|film|x|lit|meta|mu)\/?$/)) {
            // Individual board pages - only match actual board codes
            const matches = path.match(/^\/(gen|tech|biz|film|x|lit|meta|mu)\/?$/);
            this.targetType = 'board';
            this.targetId = matches[1];
        } else {
            // Default to global for everything else (homepage, etc.)
            this.targetType = 'global';
            this.targetId = 'haichan';
        }

        this.defaultTargetType = this.targetType;
        this.defaultTargetId = this.targetId;
    }

    autoStart() {
        // Load persisted mining state
        const savedMode = localStorage.getItem('haichan_mining_mode') || 'idle';
        const savedProofCount = localStorage.getItem('haichan_proof_count') || '0';

        this.proofsFound = parseInt(savedProofCount);
        this.setMode(savedMode);
    }

    setMode(newMode) {
        console.log(`🎯 Setting mining mode to: ${newMode}`);
        this.mode = newMode;

        // Persist mining mode across page loads
        localStorage.setItem('haichan_mining_mode', newMode);

        if (newMode === 'off') {
            this.stop();
        } else {
            this.start();
        }

        this.updateMiningUI();
    }

    start() {
        if (this.isActive) return;

        console.log(`🔥 Starting mining in ${this.mode} mode, targeting: ${this.targetType}:${this.targetId}`);
        this.isActive = true;
        this.startTime = Date.now();
        this.hashCount = 0;

        this.mine();
        this.updateMiningUI();
    }

    stop() {
        this.isActive = false;
        this.updateMiningUI();
    }

    async mine() {
        if (!this.isActive) return;

        let batchSize = this.mode === 'idle' ? 10 :
                       this.mode === 'active' ? 50 :
                       this.mode === 'hyperactive' ? 100 : 150;

        let delay = this.mode === 'idle' ? 100 :
                   this.mode === 'active' ? 50 :
                   this.mode === 'hyperactive' ? 20 : 10;

        for (let i = 0; i < batchSize && this.isActive; i++) {
            await this.performHash();
        }

        if (this.isActive) {
            setTimeout(() => this.mine(), delay);
        }
    }

    async performHash() {
        // Construct challenge data to match backend expectation
        const challengeData = `${this.targetType}:${this.targetId}`;
        const fullData = `${challengeData}:${this.nonce}`;
        const hash = await this.sha256(fullData);
        this.currentHash = hash;
        this.hashCount++;
        this.nonce++;


        if (hash.startsWith(this.pattern)) {
            console.log(`💎 PROOF FOUND! ${hash.substring(0, 16)}... after ${this.hashCount} attempts`);
            console.log(`📊 Challenge data: ${challengeData}`);
            console.log(`🔢 Nonce: ${this.nonce - 1}`);
            console.log(`🔗 Full hash: ${hash}`);
            console.log(`🎯 Target: ${this.targetType}:${this.targetId}`);
            this.triggerSeizureAnimation();
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

    triggerSeizureAnimation() {
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
                    hash: hash,
                    nonce: nonce,
                    challenge_data: data,
                    hashes_computed: this.hashCount, // Send actual hash count as computational work
                    metadata: {
                        hash_rate: this.getHashRate(),
                        browser: navigator.userAgent.substring(0, 100)
                    }
                })
            });

            const result = await response.json();
            if (result.success) {
                this.proofsFound++;
                localStorage.setItem('haichan_proof_count', this.proofsFound.toString());
                console.log(`✅ Proof accepted! Total proofs found: ${this.proofsFound}`);

                // Update thread mining badge if we're mining a thread
                if (this.targetType === 'thread') {
                    this.updateThreadMiningBadge(result.new_difficulty || result.difficulty);

                    // Check if we're on catalog page and reorder threads
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
        this.updateHashDisplay();

        const modeEls = document.querySelectorAll('[data-mode]');
        modeEls.forEach(el => {
            const isActive = el.dataset.mode === this.mode;
            el.style.background = isActive ? '#708B75' : '#CCCCCC';
            el.style.color = isActive ? '#FFFFEE' : '#666666';
        });
    }

    setupHoverMining() {
        document.addEventListener('mouseover', (e) => {
            // Check for threads first - everything is minable!
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

            // Check for general mining targets
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
        } else if (this.targetType === 'board') {
            return `📋 Board /${this.targetId}/`;
        } else if (this.targetType === 'images') {
            return `🖼️ Image Library`;
        } else {
            return `🌐 Global Mining`;
        }
    }

    setupStatusBar() {
        setInterval(() => {
            this.updateHashDisplay();
        }, 1000);
    }

    setupMiniDashboard() {
        const oldDash = document.getElementById('mini-dashboard-overlay');
        if (oldDash) oldDash.remove();

        const simpleDash = document.getElementById('simple-mini-dashboard');
        if (simpleDash) simpleDash.remove();

        const dashboard = document.createElement('div');
        dashboard.id = 'simple-mini-dashboard';
        dashboard.style.cssText = `
            position: fixed;
            top: 60px;
            right: 20px;
            width: 280px;
            background: #F5F5DC;
            border: 2px solid #708B75;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            z-index: 9999;
            display: none;
            box-shadow: 0 4px 12px rgba(68, 75, 110, 0.3);
        `;

        dashboard.innerHTML = `
            <div style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; padding: 8px 12px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                <span>⛏️ HAICHAN MINER</span>
                <button id="simple-close" style="background: transparent; border: 1px solid #FFFFEE; color: #FFFFEE; padding: 2px 6px; cursor: pointer; font-size: 10px;">✕</button>
            </div>

            <div style="padding: 12px;">
                <div style="margin-bottom: 10px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Mining Target:</div>
                    <div id="simple-target" style="color: #708B75; font-size: 9px; padding: 3px; background: #F8F8F8; border: 1px solid #CCCCCC;">${this.getDisplayName()}</div>
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Hash Rate: <span id="simple-hashrate" style="color: #708B75;">0 H/s</span></div>
                    <div style="color: #444B6E; font-size: 9px;">Proofs Found: <span id="simple-proofs" style="color: #708B75;">0</span></div>
                    <div style="color: #444B6E; font-size: 9px;">Pattern: <span style="color: #CD5C5C;">21e8</span></div>
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Mining Power:</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3px; margin-bottom: 5px;">
                        <button id="simple-off-btn" data-mode="off" style="padding: 4px; font-size: 8px; border: 1px solid #CCCCCC; cursor: pointer;">OFF</button>
                        <button id="simple-idle-btn" data-mode="idle" style="padding: 4px; font-size: 8px; border: 1px solid #CCCCCC; cursor: pointer;">IDLE</button>
                        <button id="simple-active-btn" data-mode="active" style="padding: 4px; font-size: 8px; border: 1px solid #CCCCCC; cursor: pointer;">ACTIVE</button>
                        <button id="simple-hyper-btn" data-mode="hyper" style="padding: 4px; font-size: 8px; border: 1px solid #CCCCCC; cursor: pointer;">HYPER</button>
                    </div>
                </div>

                <div>
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Current Hash:</div>
                    <div id="simple-current-hash" style="font-size: 8px; color: #888; word-break: break-all; background: #FAFAFA; padding: 3px; border: 1px solid #DDD;">calculating...</div>
                </div>

                <div style="border-top: 1px solid #CCCCCC; padding-top: 8px; text-align: center; margin-top: 10px;">
                    <a href="/mining" style="color: #0066CC; text-decoration: underline; font-size: 10px;">🎯 Open Full Dashboard</a>
                </div>
            </div>
        `;

        document.body.appendChild(dashboard);

        // Add event listeners
        document.getElementById('simple-close').addEventListener('click', () => {
            dashboard.style.display = 'none';
        });

        document.getElementById('simple-off-btn').addEventListener('click', () => this.setMode('off'));
        document.getElementById('simple-idle-btn').addEventListener('click', () => this.setMode('idle'));
        document.getElementById('simple-active-btn').addEventListener('click', () => this.setMode('active'));
        document.getElementById('simple-hyper-btn').addEventListener('click', () => this.setMode('hyper'));

        // Show dashboard by default
        dashboard.style.display = 'block';

        // Add toggle functionality
        const toggleBtn = document.getElementById('mini-dash-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
            });
        }

        // Keyboard shortcut
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
            }
        });

        // Update dashboard displays
        setInterval(() => {
            const hashrateEl = document.getElementById('simple-hashrate');
            const proofsEl = document.getElementById('simple-proofs');
            const hashEl = document.getElementById('simple-current-hash');

            if (hashrateEl) hashrateEl.textContent = `${this.getHashRate()} H/s`;
            if (proofsEl) proofsEl.textContent = this.proofsFound.toLocaleString();
            if (hashEl) hashEl.textContent = this.currentHash ? this.currentHash.substring(0, 24) + '...' : 'calculating...';
        }, 1000);
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

            // Add a brief flash effect to show the update
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

        // Update the PoW badge for the mined thread
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

            // Flash the thread
            minedThread.style.animation = 'haichan-seizure 0.5s ease-in-out';
            setTimeout(() => {
                minedThread.style.animation = '';
            }, 500);
        }

        // Sort threads by PoW value (highest first)
        threads.sort((a, b) => {
            const aPowBadge = a.querySelector('.catalog-pow-badge');
            const bPowBadge = b.querySelector('.catalog-pow-badge');

            const aPow = aPowBadge ? parseFloat(aPowBadge.dataset.powValue || aPowBadge.textContent.replace('⚡', '')) || 0 : 0;
            const bPow = bPowBadge ? parseFloat(bPowBadge.dataset.powValue || bPowBadge.textContent.replace('⚡', '')) || 0 : 0;

            return bPow - aPow; // Descending order
        });

        // Clear and re-append in new order
        threads.forEach(thread => catalogGrid.appendChild(thread));

        console.log(`🔄 Catalog reordered! Thread ${threadId} with PoW ${newPoW} moved to position`);
    }

    getStats() {
        return {
            hashRate: this.getHashRate(),
            proofsFound: this.proofsFound,
            target: this.getDisplayName(),
            powerLevel: this.mode.toUpperCase(),
            currentHash: this.currentHash
        };
    }

    setPowerLevel(level, levelNum) {
        this.setMode(level);
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Initializing Ultra Clean Haichan Miner...');
    window.simpleMiner = new SimpleMiner();
});

// Export for global access
window.SimpleMiner = SimpleMiner;