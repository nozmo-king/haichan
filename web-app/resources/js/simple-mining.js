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
        // Slider and gradient bar
        const slider = document.getElementById('compute-slider');
        const bar = document.getElementById('compute-gradient-bar');
        const label = document.getElementById('compute-label');
        const status = document.getElementById('power-status');

        if (slider) slider.value = String(this.powerLevel);
        if (label) label.textContent = `${this.powerLevel}%`;
        if (status) status.textContent = this.powerLevel === 0 ? 'OFF' : 'ON';

        if (bar) {
            const pct = this.powerLevel;
            bar.style.background = `linear-gradient(90deg, #6BBF59 0%, #6BBF59 ${pct}%, #CD5C5C ${pct}%, #CD5C5C 100%)`;
        }

        // Target label
        const targetEl = document.getElementById('simple-target');
        if (targetEl) targetEl.textContent = this.getDisplayName();

        // Hash display
        const hashrateEl = document.getElementById('simple-hashrate');
        const proofsEl = document.getElementById('simple-proofs');
        const hashEl = document.getElementById('simple-current-hash');

        if (hashrateEl) hashrateEl.textContent = `${this.getHashRate()} H/s`;
        if (proofsEl) proofsEl.textContent = this.proofsFound.toLocaleString();
        if (hashEl) hashEl.textContent = this.currentHash ? this.currentHash.substring(0, 24) + '...' : '—';
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
            width: 300px;
            background: #F5F5DC;
            border: 2px solid #708B75;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            z-index: 9999;
            display: block;
            box-shadow: 0 4px 12px rgba(68, 75, 110, 0.3);
        `;

        const isThread = this.targetType === 'thread';

        dashboard.innerHTML = `
            <div style="background: linear-gradient(135deg, #708B75, #9AB87A); color: #FFFFEE; padding: 8px 12px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
                <span>⛏️ HAICHAN MINER</span>
                <button id="simple-close" style="background: transparent; border: 1px solid #FFFFEE; color: #FFFFEE; padding: 2px 6px; cursor: pointer; font-size: 10px;">✕</button>
            </div>

            <div style="padding: 12px;">
                <div style="margin-bottom: 10px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Mining Target:</div>
                    <div id="simple-target" style="color: #708B75; font-size: 9px; padding: 3px; background: #F8F8F8; border: 1px solid #CCCCCC;">${this.getDisplayName()}</div>
                    ${isThread ? '' : '<div style="margin-top:6px;color:#8a8a8a;font-size:9px;">Tip: open a thread page to mine that thread directly.</div>'}
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Hash Rate: <span id="simple-hashrate" style="color: #708B75;">0 H/s</span></div>
                    <div style="color: #444B6E; font-size: 9px;">Proofs Found: <span id="simple-proofs" style="color: #708B75;">${this.proofsFound}</span></div>
                    <div style="color: #444B6E; font-size: 9px;">Pattern: <span style="color: #CD5C5C;">21e8</span></div>
                </div>

                <div style="margin-bottom: 12px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 6px; display:flex; justify-content: space-between; align-items:center;">
                        <span>Compute (choose power)</span>
                        <span id="power-status" style="color:#708B75;">OFF</span>
                    </div>
                    <input id="compute-slider" type="range" min="0" max="100" step="1" value="${this.powerLevel}" style="width: 100%;">
                    <div id="compute-gradient-bar" style="height: 10px; border:1px solid #CCC; border-radius: 4px; margin-top:6px; background: linear-gradient(90deg, #6BBF59 0%, #6BBF59 0%, #CD5C5C 0%, #CD5C5C 100%);"></div>
                    <div style="display:flex; justify-content: space-between; font-size:9px; color:#666; margin-top:4px;">
                        <span>Idle</span>
                        <span id="compute-label">0%</span>
                        <span>Max</span>
                    </div>
                    <div style="margin-top:6px;">
                        <button id="simple-off-btn" style="padding: 4px; font-size: 8px; border: 1px solid #CCCCCC; cursor: pointer; background: #CD5C5C; color: white; width:100%;">Turn Off</button>
                    </div>
                </div>

                <div style="margin-bottom: 10px;">
                    <div style="color: #444B6E; font-weight: bold; margin-bottom: 5px;">Current Hash:</div>
                    <div id="simple-current-hash" style="font-size: 8px; color: #888; word-break: break-all; background: #FAFAFA; padding: 3px; border: 1px solid #DDD;">—</div>
                </div>

                <div style="border-top: 1px solid #CCCCCC; padding-top: 8px; text-align: center; font-size: 8px; color: #666;">
                    Tip: set compute to 0% to stop. Mining is OFF by default.
                </div>
            </div>
        `;

        document.body.appendChild(dashboard);

        // Events
        document.getElementById('simple-close').addEventListener('click', () => {
            dashboard.style.display = 'none';
        });

        document.getElementById('simple-off-btn').addEventListener('click', () => this.setPowerLevel(0));

        const slider = document.getElementById('compute-slider');
        slider.addEventListener('input', (e) => {
            const value = parseInt(e.target.value, 10);
            this.setPowerLevel(value);
        });

        // Initial UI sync
        this.updateMiningUI();
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