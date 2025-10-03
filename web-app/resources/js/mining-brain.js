/**
 * HAICHAN MINING BRAIN v1.0
 * Centralized mining control system - ONE BRAIN TO RULE THEM ALL
 * Replaces all fragmented mining systems with unified architecture
 */

class HaichanMiningBrain {
    constructor() {
        this.isInitialized = false;
        this.isActive = true;

        // Central state store
        this.state = {
            power: 5, // 0-10 power level
            mode: 'mouseover', // mouseover, manual, form, thread, idle
            currentTarget: null,
            isMinimized: false,

            // Live stats
            sessionStats: {
                startTime: Date.now(),
                totalHashes: 0,
                totalProofs: 0,
                totalPoints: 0,
                currentHashrate: 0
            },

            // Mining targets
            activeTargets: new Map(),

            // Performance monitoring
            performance: {
                avgHashTime: 0,
                cpuUsage: 0,
                memoryUsage: 0
            }
        };

        // Mining configurations
        this.configs = {
            patterns: {
                '21': { points: 0.1, difficulty: 'Trivial' },
                '21e': { points: 0.5, difficulty: 'Easy' },
                '21e8': { points: 100, difficulty: 'Standard' },
                '21e80': { points: 500, difficulty: 'Hard' },
                '21e800': { points: 2500, difficulty: 'Very Hard' },
                '21e8000': { points: 10000, difficulty: 'Extreme' }
            },
            rare: {
                'deadbeef': { points: 5000, rarity: '🏆 LEGENDARY' },
                '1337': { points: 2500, rarity: '👑 ELITE' },
                '777': { points: 777, rarity: '🍀 LUCKY' },
                '666': { points: 666, rarity: '😈 CURSED' },
                '000': { points: 500, rarity: '⚡ RARE' },
                '111': { points: 400, rarity: '⚡ RARE' }
            },
            powerLevels: {
                0: { name: 'Disabled', batchSize: 0, interval: 0 },
                1: { name: 'Minimal', batchSize: 10, interval: 500 },
                2: { name: 'Low', batchSize: 25, interval: 300 },
                3: { name: 'Light', batchSize: 50, interval: 200 },
                4: { name: 'Below Average', batchSize: 75, interval: 150 },
                5: { name: 'Standard', batchSize: 100, interval: 100 },
                6: { name: 'Above Average', batchSize: 150, interval: 80 },
                7: { name: 'High', batchSize: 200, interval: 60 },
                8: { name: 'Very High', batchSize: 300, interval: 40 },
                9: { name: 'Extreme', batchSize: 500, interval: 20 },
                10: { name: 'MAXIMUM POWER', batchSize: 1000, interval: 10 }
            }
        };

        // Mining workers
        this.workers = {
            mouseover: null,
            manual: null,
            background: null
        };

        // UI update intervals
        this.intervals = {
            stats: null,
            performance: null
        };

        this.init();
    }

    init() {
        if (this.isInitialized) return;
        this.isInitialized = true;

        console.log('🧠 MINING BRAIN: Initializing centralized mining system...');

        // Disable all existing mining systems
        this.disableOldSystems();

        // Create brain UI
        this.createBrainUI();

        // Setup mining modes
        this.setupMouseoverMining();
        this.setupManualMining();
        this.setupFormMining();

        // Start performance monitoring
        this.startPerformanceMonitoring();

        // Load saved state
        this.loadState();

        console.log('🧠 MINING BRAIN: Fully operational');
    }

    disableOldSystems() {
        // Stop all existing mining systems
        const oldSystems = [
            'mouseoverMiningV2',
            'mouseoverMining',
            'enhancedMiningDashboard',
            'haichanMiner',
            'emergencyMiner',
            'haichanUnified'
        ];

        oldSystems.forEach(system => {
            if (window[system]) {
                console.log(`🧠 MINING BRAIN: Disabling old system: ${system}`);
                try {
                    if (typeof window[system].disable === 'function') {
                        window[system].disable();
                    }
                    if (typeof window[system].stop === 'function') {
                        window[system].stop();
                    }
                    if (typeof window[system].stopAllMining === 'function') {
                        window[system].stopAllMining();
                    }
                } catch (e) {
                    console.log(`🧠 MINING BRAIN: Could not disable ${system}:`, e);
                }
            }
        });

        // Remove old UI elements
        const oldUIs = [
            'mining-dashboard',
            'unified-mining-dashboard',
            'enhanced-mining-dashboard',
            'dashboard-toggle'
        ];

        oldUIs.forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                console.log(`🧠 MINING BRAIN: Removing old UI: ${id}`);
                element.remove();
            }
        });

        // Clear old intervals
        for (let i = 0; i < 10000; i++) {
            clearInterval(i);
        }
    }

    createBrainUI() {
        const brainHTML = `
            <div id="mining-brain-ui" class="mining-brain">
                <div class="brain-header" id="brain-header">
                    <div class="brain-title">
                        <span class="brain-icon">🧠</span>
                        <span class="brain-text">MINING BRAIN</span>
                        <span class="brain-status" id="brain-status">ACTIVE</span>
                    </div>
                    <div class="brain-controls">
                        <button id="brain-minimize" class="brain-btn">−</button>
                        <button id="brain-close" class="brain-btn">×</button>
                    </div>
                </div>

                <div class="brain-content" id="brain-content">
                    <!-- Power Control -->
                    <div class="brain-section">
                        <div class="section-title">⚡ Power Control</div>
                        <div class="power-control">
                            <div class="power-display">
                                <span id="power-level">${this.state.power}</span>/10
                                <span id="power-name">${this.configs.powerLevels[this.state.power].name}</span>
                            </div>
                            <input type="range" id="power-slider" class="power-slider"
                                   min="0" max="10" value="${this.state.power}" step="1">
                        </div>
                    </div>

                    <!-- Mode Selection -->
                    <div class="brain-section">
                        <div class="section-title">🎯 Mining Mode</div>
                        <div class="mode-selector">
                            <select id="mining-mode" class="mode-select">
                                <option value="mouseover" selected>Mouseover (Auto)</option>
                                <option value="manual">Manual Control</option>
                                <option value="background">Background Mining</option>
                                <option value="idle">Idle (Disabled)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Current Target -->
                    <div class="brain-section">
                        <div class="section-title">🔍 Current Target</div>
                        <div class="target-display" id="target-display">
                            Hover over content to begin mining
                        </div>
                    </div>

                    <!-- Live Statistics -->
                    <div class="brain-section">
                        <div class="section-title">📊 Live Statistics</div>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label">Hash Rate</span>
                                <span class="stat-value" id="stat-hashrate">0 H/s</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Total Hashes</span>
                                <span class="stat-value" id="stat-hashes">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Proofs Found</span>
                                <span class="stat-value" id="stat-proofs">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Points Earned</span>
                                <span class="stat-value" id="stat-points">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Monitor -->
                    <div class="brain-section">
                        <div class="section-title">⚙️ Performance</div>
                        <div class="performance-display">
                            <div class="perf-item">
                                <span>CPU Load:</span>
                                <span id="cpu-load">0%</span>
                            </div>
                            <div class="perf-item">
                                <span>Avg Hash Time:</span>
                                <span id="hash-time">0ms</span>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Controls (when in manual mode) -->
                    <div class="brain-section" id="manual-controls" style="display: none;">
                        <div class="section-title">🎮 Manual Controls</div>
                        <div class="manual-buttons">
                            <button id="start-manual" class="brain-action-btn">Start Mining</button>
                            <button id="stop-manual" class="brain-action-btn">Stop Mining</button>
                        </div>
                        <div class="target-selector">
                            <select id="manual-target" class="target-select">
                                <option value="global">Global Mining</option>
                                <option value="custom">Custom Target</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating Toggle Button -->
            <button id="brain-toggle" class="brain-toggle" title="Mining Brain">
                <div class="toggle-icon">🧠</div>
                <div class="toggle-text">BRAIN</div>
            </button>
        `;

        document.body.insertAdjacentHTML('beforeend', brainHTML);
        this.addBrainStyles();
        this.setupBrainEvents();
    }

    addBrainStyles() {
        const style = document.createElement('style');
        style.id = 'mining-brain-styles';
        style.textContent = `
            .mining-brain {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 320px;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
                border: 2px solid #00d4ff;
                border-radius: 12px;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                color: #ffffff;
                z-index: 999999;
                box-shadow: 0 8px 32px rgba(0, 212, 255, 0.3);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
            }

            .brain-header {
                background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
                color: #1a1a2e;
                padding: 10px 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 10px 10px 0 0;
                cursor: move;
            }

            .brain-title {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: bold;
                font-size: 12px;
            }

            .brain-icon {
                font-size: 16px;
                animation: pulse 2s infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }

            .brain-status {
                background: rgba(0, 255, 136, 0.2);
                padding: 2px 6px;
                border-radius: 8px;
                font-size: 9px;
                color: #00ff88;
                border: 1px solid #00ff88;
            }

            .brain-controls {
                display: flex;
                gap: 5px;
            }

            .brain-btn {
                background: rgba(26, 26, 46, 0.3);
                border: none;
                color: #1a1a2e;
                padding: 4px 8px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: bold;
                font-size: 10px;
                transition: all 0.2s ease;
            }

            .brain-btn:hover {
                background: rgba(26, 26, 46, 0.6);
                transform: scale(1.1);
            }

            .brain-content {
                padding: 15px;
                max-height: 600px;
                overflow-y: auto;
            }

            .brain-section {
                margin-bottom: 15px;
                padding: 10px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 8px;
                border: 1px solid rgba(0, 212, 255, 0.2);
            }

            .section-title {
                font-weight: bold;
                margin-bottom: 8px;
                color: #00d4ff;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            .power-control {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .power-display {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            #power-level {
                font-size: 16px;
                font-weight: bold;
                color: #00ff88;
            }

            #power-name {
                color: #888;
                font-size: 9px;
            }

            .power-slider {
                width: 100%;
                height: 6px;
                border-radius: 3px;
                outline: none;
                appearance: none;
                background: linear-gradient(to right, #00d4ff 0%, #333 0%);
                cursor: pointer;
            }

            .power-slider::-webkit-slider-thumb {
                appearance: none;
                width: 16px;
                height: 16px;
                background: #00ff88;
                border-radius: 50%;
                cursor: pointer;
                box-shadow: 0 0 8px rgba(0, 255, 136, 0.5);
            }

            .mode-select, .target-select {
                width: 100%;
                background: #1a1a2e;
                color: #ffffff;
                border: 1px solid #00d4ff;
                border-radius: 6px;
                padding: 6px;
                font-size: 10px;
                font-family: inherit;
            }

            .target-display {
                padding: 8px;
                background: #1a1a2e;
                border: 1px solid rgba(0, 212, 255, 0.3);
                border-radius: 6px;
                color: #00d4ff;
                font-weight: bold;
                min-height: 20px;
                display: flex;
                align-items: center;
            }

            .stats-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .stat-item {
                display: flex;
                justify-content: space-between;
                padding: 6px;
                background: #1a1a2e;
                border: 1px solid rgba(0, 212, 255, 0.2);
                border-radius: 4px;
            }

            .stat-label {
                color: #888;
                font-size: 9px;
            }

            .stat-value {
                color: #00ff88;
                font-weight: bold;
                font-size: 10px;
            }

            .performance-display {
                display: flex;
                flex-direction: column;
                gap: 6px;
            }

            .perf-item {
                display: flex;
                justify-content: space-between;
                font-size: 9px;
                color: #888;
            }

            .brain-action-btn {
                background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
                color: #1a1a2e;
                border: none;
                padding: 8px 16px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                font-size: 10px;
                margin-right: 8px;
                transition: all 0.2s ease;
            }

            .brain-action-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 4px 12px rgba(0, 212, 255, 0.4);
            }

            .brain-toggle {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #00d4ff 0%, #0099cc 100%);
                border: none;
                border-radius: 50%;
                color: #1a1a2e;
                cursor: pointer;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                z-index: 999998;
                box-shadow: 0 4px 16px rgba(0, 212, 255, 0.4);
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2px;
            }

            .brain-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 212, 255, 0.6);
            }

            .toggle-icon {
                font-size: 18px;
            }

            .toggle-text {
                font-size: 8px;
                font-weight: bold;
                letter-spacing: 1px;
            }

            /* Minimized state */
            .mining-brain.minimized .brain-content {
                display: none;
            }

            .mining-brain.minimized {
                width: auto;
            }

            /* Hidden state */
            .mining-brain.hidden {
                display: none;
            }

            /* Mining active indicators */
            .mining-active {
                box-shadow: 0 0 20px rgba(0, 255, 136, 0.8) !important;
                border: 2px solid #00ff88 !important;
                animation: miningGlow 1.5s infinite alternate;
            }

            @keyframes miningGlow {
                0% { box-shadow: 0 0 20px rgba(0, 255, 136, 0.8); }
                100% { box-shadow: 0 0 30px rgba(0, 255, 136, 1.0); }
            }
        `;

        document.head.appendChild(style);
    }

    setupBrainEvents() {
        // Toggle button
        document.getElementById('brain-toggle').addEventListener('click', () => {
            this.toggleBrain();
        });

        // Minimize/Close
        document.getElementById('brain-minimize').addEventListener('click', () => {
            this.minimizeBrain();
        });

        document.getElementById('brain-close').addEventListener('click', () => {
            this.hideBrain();
        });

        // Power slider
        document.getElementById('power-slider').addEventListener('input', (e) => {
            this.setPower(parseInt(e.target.value));
        });

        // Mode selector
        document.getElementById('mining-mode').addEventListener('change', (e) => {
            this.setMode(e.target.value);
        });

        // Manual controls
        document.getElementById('start-manual')?.addEventListener('click', () => {
            this.startManualMining();
        });

        document.getElementById('stop-manual')?.addEventListener('click', () => {
            this.stopManualMining();
        });

        // Dragging
        this.setupDragging();

        // Start UI updates
        this.startUIUpdates();
    }

    setupDragging() {
        const brain = document.getElementById('mining-brain-ui');
        const header = document.getElementById('brain-header');
        let isDragging = false;
        let dragOffset = { x: 0, y: 0 };

        header.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('brain-btn')) return;

            isDragging = true;
            const rect = brain.getBoundingClientRect();
            dragOffset = {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
            brain.style.opacity = '0.9';
        });

        document.addEventListener('mousemove', (e) => {
            if (!isDragging) return;

            const x = e.clientX - dragOffset.x;
            const y = e.clientY - dragOffset.y;

            brain.style.left = Math.max(0, Math.min(x, window.innerWidth - brain.offsetWidth)) + 'px';
            brain.style.top = Math.max(0, Math.min(y, window.innerHeight - brain.offsetHeight)) + 'px';
            brain.style.right = 'auto';
        });

        document.addEventListener('mouseup', () => {
            if (isDragging) {
                isDragging = false;
                brain.style.opacity = '1';
            }
        });
    }

    // Brain UI Methods
    toggleBrain() {
        const brain = document.getElementById('mining-brain-ui');
        if (brain.style.display === 'none') {
            brain.style.display = 'block';
            this.state.isMinimized = false;
        } else {
            brain.style.display = 'none';
        }
        this.saveState();
    }

    minimizeBrain() {
        const brain = document.getElementById('mining-brain-ui');
        const btn = document.getElementById('brain-minimize');

        brain.classList.toggle('minimized');
        this.state.isMinimized = brain.classList.contains('minimized');
        btn.textContent = this.state.isMinimized ? '+' : '−';
        this.saveState();
    }

    hideBrain() {
        document.getElementById('mining-brain-ui').style.display = 'none';
        this.saveState();
    }

    setPower(level) {
        this.state.power = Math.max(0, Math.min(10, level));
        const config = this.configs.powerLevels[this.state.power];

        document.getElementById('power-level').textContent = this.state.power;
        document.getElementById('power-name').textContent = config.name;

        // Update slider visual
        const slider = document.getElementById('power-slider');
        const percentage = (this.state.power / 10) * 100;
        slider.style.background = `linear-gradient(to right, #00d4ff 0%, #00d4ff ${percentage}%, #333 ${percentage}%, #333 100%)`;

        console.log(`🧠 MINING BRAIN: Power set to ${this.state.power}/10 (${config.name})`);
        this.saveState();

        // Restart mining with new power level if active
        if (this.state.mode !== 'idle' && this.state.currentTarget) {
            this.restartMining();
        }
    }

    setMode(mode) {
        const oldMode = this.state.mode;
        this.state.mode = mode;

        console.log(`🧠 MINING BRAIN: Mode changed from ${oldMode} to ${mode}`);

        // Stop current mode
        this.stopCurrentMode();

        // Start new mode
        this.startCurrentMode();

        // Update UI
        document.getElementById('manual-controls').style.display =
            mode === 'manual' ? 'block' : 'none';

        this.saveState();
    }

    stopCurrentMode() {
        // Clear all active mining
        this.state.activeTargets.clear();
        this.removeAllMiningVisuals();

        // Clear workers
        Object.values(this.workers).forEach(worker => {
            if (worker) {
                clearInterval(worker);
            }
        });
        this.workers = { mouseover: null, manual: null, background: null };
    }

    startCurrentMode() {
        switch (this.state.mode) {
            case 'mouseover':
                this.setupMouseoverMining();
                break;
            case 'manual':
                // Manual mode requires user to click start
                break;
            case 'background':
                this.startBackgroundMining();
                break;
            case 'idle':
                // Do nothing
                break;
        }
    }

    // Core Mining Methods
    setupMouseoverMining() {
        if (this.state.mode !== 'mouseover') return;

        console.log('🧠 MINING BRAIN: Setting up mouseover mining');

        // Remove old listeners
        document.removeEventListener('mouseover', this.handleMouseover);
        document.removeEventListener('mouseout', this.handleMouseout);

        // Add new listeners
        this.handleMouseover = this.handleMouseover.bind(this);
        this.handleMouseout = this.handleMouseout.bind(this);

        document.addEventListener('mouseover', this.handleMouseover);
        document.addEventListener('mouseout', this.handleMouseout);
    }

    handleMouseover(event) {
        if (this.state.mode !== 'mouseover' || this.state.power === 0) return;

        const target = this.getMineableTarget(event.target);
        if (target && !this.state.activeTargets.has(target.id)) {
            this.startMining(target);
        }
    }

    handleMouseout(event) {
        const target = this.getMineableTarget(event.target);
        if (target && this.state.activeTargets.has(target.id)) {
            // Add small delay to prevent flickering
            setTimeout(() => {
                if (!event.target.matches(':hover')) {
                    this.stopMining(target);
                }
            }, 100);
        }
    }

    getMineableTarget(element) {
        let current = element;

        for (let i = 0; i < 6; i++) {
            if (!current) break;

            // Check for images
            if (current.tagName === 'IMG' && current.src) {
                return {
                    id: current.src,
                    type: 'image',
                    element: current,
                    displayName: `🖼️ Image`,
                    points: 25
                };
            }

            // Check for posts
            if (current.classList && current.classList.contains('post')) {
                const postNo = current.querySelector('.post-no');
                if (postNo) {
                    const match = postNo.textContent.match(/No\.(\d+)/);
                    if (match) {
                        return {
                            id: `post-${match[1]}`,
                            type: 'post',
                            element: current,
                            displayName: `💬 Post #${match[1]}`,
                            points: 20
                        };
                    }
                }
            }

            // Check for threads
            if (current.classList && current.classList.contains('catalog-thread')) {
                const threadId = current.dataset.threadId;
                if (threadId) {
                    return {
                        id: `thread-${threadId}`,
                        type: 'thread',
                        element: current,
                        displayName: `🧵 Thread #${threadId}`,
                        points: 22
                    };
                }
            }

            current = current.parentElement;
        }

        return null;
    }

    async startMining(target) {
        console.log(`🧠 MINING BRAIN: Starting mining on ${target.displayName}`);

        this.state.currentTarget = target;
        this.state.activeTargets.set(target.id, {
            target,
            startTime: Date.now(),
            hashes: 0,
            worker: null
        });

        this.addMiningVisual(target.element);
        this.updateTargetDisplay(target.displayName);

        // Start mining worker
        const session = this.state.activeTargets.get(target.id);
        session.worker = setInterval(() => {
            this.performMining(target);
        }, this.configs.powerLevels[this.state.power].interval);
    }

    stopMining(target) {
        console.log(`🧠 MINING BRAIN: Stopping mining on ${target.displayName}`);

        const session = this.state.activeTargets.get(target.id);
        if (session) {
            clearInterval(session.worker);
            this.state.activeTargets.delete(target.id);
            this.removeMiningVisual(target.element);
        }

        if (this.state.currentTarget && this.state.currentTarget.id === target.id) {
            this.state.currentTarget = null;
            this.updateTargetDisplay('Hover over content to begin mining');
        }
    }

    async performMining(target) {
        const batchSize = this.configs.powerLevels[this.state.power].batchSize;
        const pattern = '21e8'; // Standard pattern

        const startTime = performance.now();

        for (let i = 0; i < batchSize; i++) {
            const nonce = Math.floor(Math.random() * 1000000000);
            const data = `${target.type}-${target.id}-${Date.now()}-${nonce}`;

            try {
                const hash = await this.sha256(data);
                this.state.sessionStats.totalHashes++;

                // Check for rare patterns first
                const rareMatch = this.checkRarePattern(hash);
                if (rareMatch) {
                    this.handleProofFound(hash, nonce, data, rareMatch.pattern, rareMatch.points, target);
                    break;
                }

                // Check for standard pattern
                if (hash.startsWith(pattern)) {
                    const points = this.configs.patterns[pattern]?.points || 100;
                    this.handleProofFound(hash, nonce, data, pattern, points, target);
                    break;
                }

            } catch (error) {
                console.error('🧠 MINING BRAIN: Hash error:', error);
            }
        }

        // Update performance metrics
        const elapsed = performance.now() - startTime;
        this.state.performance.avgHashTime = elapsed / batchSize;
    }

    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }

    checkRarePattern(hash) {
        for (const [pattern, data] of Object.entries(this.configs.rare)) {
            if (hash.toLowerCase().startsWith(pattern.toLowerCase())) {
                return { pattern, ...data };
            }
        }
        return null;
    }

    async handleProofFound(hash, nonce, data, pattern, points, target) {
        console.log(`🧠 MINING BRAIN: 💎 PROOF FOUND! ${pattern} (+${points} points)`);

        this.state.sessionStats.totalProofs++;
        this.state.sessionStats.totalPoints += points;

        // Show celebration
        this.showProofCelebration(target.element, points);

        // Submit proof
        try {
            await this.submitProof({
                hash,
                nonce,
                data,
                pattern,
                points,
                target_type: target.type,
                target_id: target.id
            });
        } catch (error) {
            console.error('🧠 MINING BRAIN: Submit error:', error);
        }
    }

    async submitProof(proof) {
        const response = await fetch('/api/submit-proof', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(proof)
        });

        return await response.json();
    }

    // Visual Effects
    addMiningVisual(element) {
        if (!element) return;
        element.classList.add('mining-active');
    }

    removeMiningVisual(element) {
        if (!element) return;
        element.classList.remove('mining-active');
    }

    removeAllMiningVisuals() {
        document.querySelectorAll('.mining-active').forEach(el => {
            el.classList.remove('mining-active');
        });
    }

    showProofCelebration(element, points) {
        if (!element) return;

        const celebration = document.createElement('div');
        celebration.textContent = `💎 +${points}!`;
        celebration.style.cssText = `
            position: fixed;
            color: #00ff88;
            font-weight: bold;
            font-size: 14px;
            z-index: 999999;
            pointer-events: none;
            animation: floatUp 2s ease-out forwards;
        `;

        const rect = element.getBoundingClientRect();
        celebration.style.left = (rect.left + rect.width/2) + 'px';
        celebration.style.top = (rect.top + rect.height/2) + 'px';

        document.body.appendChild(celebration);

        setTimeout(() => celebration.remove(), 2000);

        // Add CSS animation if not exists
        if (!document.getElementById('brain-celebration-styles')) {
            const style = document.createElement('style');
            style.id = 'brain-celebration-styles';
            style.textContent = `
                @keyframes floatUp {
                    0% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                    100% { opacity: 0; transform: translate(-50%, -150px) scale(1.5); }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // UI Updates
    updateTargetDisplay(text) {
        document.getElementById('target-display').textContent = text;
    }

    startUIUpdates() {
        // Update stats every second
        this.intervals.stats = setInterval(() => {
            this.updateStatsDisplay();
        }, 1000);

        // Update performance every 5 seconds
        this.intervals.performance = setInterval(() => {
            this.updatePerformanceDisplay();
        }, 5000);
    }

    updateStatsDisplay() {
        const elapsed = (Date.now() - this.state.sessionStats.startTime) / 1000;
        const hashrate = elapsed > 0 ? Math.floor(this.state.sessionStats.totalHashes / elapsed) : 0;

        document.getElementById('stat-hashrate').textContent = `${hashrate.toLocaleString()} H/s`;
        document.getElementById('stat-hashes').textContent = this.state.sessionStats.totalHashes.toLocaleString();
        document.getElementById('stat-proofs').textContent = this.state.sessionStats.totalProofs.toString();
        document.getElementById('stat-points').textContent = this.state.sessionStats.totalPoints.toFixed(1);

        this.state.sessionStats.currentHashrate = hashrate;
    }

    updatePerformanceDisplay() {
        const cpuLoad = Math.min(100, (this.state.sessionStats.currentHashrate / 10000) * 100);

        document.getElementById('cpu-load').textContent = `${cpuLoad.toFixed(1)}%`;
        document.getElementById('hash-time').textContent = `${this.state.performance.avgHashTime.toFixed(2)}ms`;
    }

    // Manual Mining
    startManualMining() {
        if (this.state.mode !== 'manual') return;

        const target = {
            id: 'manual-global',
            type: 'manual',
            element: null,
            displayName: '🎮 Manual Mining',
            points: 100
        };

        this.startMining(target);
    }

    stopManualMining() {
        if (this.state.currentTarget && this.state.currentTarget.type === 'manual') {
            this.stopMining(this.state.currentTarget);
        }
    }

    // Background Mining
    startBackgroundMining() {
        console.log('🧠 MINING BRAIN: Starting background mining');

        const target = {
            id: 'background-global',
            type: 'background',
            element: null,
            displayName: '🌐 Background Mining',
            points: 50
        };

        this.startMining(target);
    }

    restartMining() {
        if (this.state.currentTarget) {
            const target = this.state.currentTarget;
            this.stopMining(target);
            setTimeout(() => this.startMining(target), 100);
        }
    }

    // State Management
    saveState() {
        const stateToSave = {
            power: this.state.power,
            mode: this.state.mode,
            isMinimized: this.state.isMinimized
        };
        localStorage.setItem('mining-brain-state', JSON.stringify(stateToSave));
    }

    loadState() {
        try {
            const saved = localStorage.getItem('mining-brain-state');
            if (saved) {
                const state = JSON.parse(saved);
                this.setPower(state.power || 5);
                this.setMode(state.mode || 'mouseover');

                if (state.isMinimized) {
                    this.minimizeBrain();
                }
            }
        } catch (error) {
            console.error('🧠 MINING BRAIN: Error loading state:', error);
        }
    }

    startPerformanceMonitoring() {
        // Basic performance monitoring
        setInterval(() => {
            // Monitor memory usage if available
            if (performance.memory) {
                this.state.performance.memoryUsage = performance.memory.usedJSHeapSize / 1024 / 1024;
            }
        }, 10000);
    }

    // Form PoW Integration
    async acquireProofFor(formPayload) {
        // formPayload = { board_code, target_type, target_id, action, difficulty }
        
        // 1. Request challenge from server
        const challengeResponse = await fetch('/api/mining/challenges', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(formPayload)
        });
        
        if (!challengeResponse.ok) {
            throw new Error('Failed to get challenge');
        }
        
        const challenge = await challengeResponse.json();
        
        // 2. Mine proof for this challenge
        const proof = await this.mineProof(challenge.canonical_payload, challenge.signature, formPayload.difficulty);
        
        // 3. Return proof with challenge token
        return {
            nonce: proof.nonce,
            hash: proof.hash,
            challenge_id: challenge.token
        };
    }

    async mineProof(canonicalPayload, signature, difficulty) {
        // Mine until we find a hash starting with the difficulty pattern
        let nonce = 0;
        const data = JSON.stringify(canonicalPayload) + signature;
        
        while (true) {
            const hash = await this.computeSHA256(data + ':' + nonce);
            
            if (hash.toLowerCase().startsWith(difficulty.toLowerCase())) {
                return { nonce, hash };
            }
            
            nonce++;
            
            // Yield to UI every 1000 hashes
            if (nonce % 1000 === 0) {
                await new Promise(resolve => setTimeout(resolve, 0));
            }
        }
    }

    async computeSHA256(str) {
        // Use WasmSha256 for hashing
        if (window.WasmSha256 && window.WasmSha256.hash) {
            return await window.WasmSha256.hash(str);
        }
        // Fallback to SubtleCrypto
        const enc = new TextEncoder();
        const buf = await crypto.subtle.digest('SHA-256', enc.encode(str));
        return Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // Cleanup
    destroy() {
        console.log('🧠 MINING BRAIN: Shutting down');

        this.stopCurrentMode();

        // Clear intervals
        Object.values(this.intervals).forEach(interval => {
            if (interval) clearInterval(interval);
        });

        // Remove event listeners
        document.removeEventListener('mouseover', this.handleMouseover);
        document.removeEventListener('mouseout', this.handleMouseout);

        // Remove UI
        document.getElementById('mining-brain-ui')?.remove();
        document.getElementById('brain-toggle')?.remove();
        document.getElementById('mining-brain-styles')?.remove();
        document.getElementById('brain-celebration-styles')?.remove();

        this.isInitialized = false;
    }
}

// Initialize the Mining Brain
console.log('🧠 MINING BRAIN: Loading...');

// Cleanup any existing instances
if (window.haichanMiningBrain) {
    window.haichanMiningBrain.destroy();
}

// Create new instance
window.haichanMiningBrain = new HaichanMiningBrain();

// Set as primary mining system
window.haichanMiner = window.haichanMiningBrain;

console.log('🧠 MINING BRAIN: System operational');