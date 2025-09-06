class HaichanMiner {
    constructor() {
        this.isActive = false;
        this.currentTarget = null;
        this.currentTargetType = null; // 'thread', 'user', 'board'
        this.currentTargetId = null;
        this.startTime = 0;
        this.nonce = 0;
        this.hashCount = 0;
        this.pattern = '21e8'; // Start with 21e8 vanity marker
        this.ui = null;
        this.miningInterval = null;
        this.hashRateInterval = null;
        this.submitQueue = [];
        this.intensity = 1.0; // Default intensity multiplier
        this.sessionProofs = 0;
        this.isMinimized = localStorage.getItem('haichan-miner-minimized') === 'true';
        this.buzzingEnabled = true; // Enable buzzing animation by default
        this.emojiIndex = 0; // For cycling through emojis
        this.miningEmojis = ['⛏️', '🔨', '⚒️', '🛠️', '🔧', '💎', '🌟', '⚡', '🔥']; // Mining-related emojis to cycle through
        
        // Global emoji categories for site-wide cycling
        this.emojiCategories = {
            mining: ['⛏️', '🔨', '⚒️', '🛠️', '🔧', '💎', '🌟', '⚡', '🔥', '💰', '🏆', '🎯'],
            numbers: ['1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'],
            faces: ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊'],
            tech: ['💻', '🖥️', '⌨️', '🖱️', '🖨️', '💾', '💿', '📀', '🧮', '📱', '📟', '☎️'],
            arrows: ['⬆️', '↗️', '➡️', '↘️', '⬇️', '↙️', '⬅️', '↖️', '↕️', '↔️', '↩️', '↪️'],
            symbols: ['🔴', '🟠', '🟡', '🟢', '🔵', '🟣', '⚫', '⚪', '🟤', '🔶', '🔷', '🔸'],
            boards: ['📋', '📊', '📈', '📉', '📝', '📄', '📃', '📑', '📜', '📰', '🗞️', '📓'],
            browse: ['👀', '🔍', '🔎', '👁️', '👁️‍🗨️', '🕵️', '🔬', '🔭', '📡', '🛰️'],
            access: ['🔐', '🔒', '🔓', '🗝️', '🔑', '🚪', '🚧', '⛔', '🚫', '✅', '❌', '⭐'],
            random: ['🎲', '🎰', '🎪', '🎨', '🎭', '🎪', '🎡', '🎢', '🎠', '🎯', '🎱', '🎮']
        };
        
        this.activeEmojiCycles = new Map(); // Track active cycling elements
        
        this.init();
    }
    
    init() {
        this.createUI();
        this.attachEventListeners();
        this.setupMiniDashboard();
        this.initGlobalEmojiCycling();
        // Set initial target to global mining
        this.switchTarget('global', 'haichan', 'Global Network');
        // Force start mining immediately in IDLE mode
        this.setMiningMode('idle');
        this.startGlobalMining();
        
        // Ensure UI is visible and dashboard is never hidden
        this.ui.style.display = 'block';
        this.isMinimized = false;
        localStorage.setItem('haichan-miner-minimized', 'false');
    }
    
    createUI() {
        // Create prominent, permanent mining UI
        this.ui = document.createElement('div');
        this.ui.id = 'haichan-miner';
        this.ui.innerHTML = `
            <div class="miner-header">
                <span class="miner-title">⛏️ HAICHAN MINING DASHBOARD</span>
                <div class="miner-controls">
                    <button id="miner-minimize-btn" class="miner-minimize" onclick="window.haichanMiner.toggleMinimize()" title="Minimize/Maximize" style="background: #708B75; border: 1px solid #444B6E; color: #FFFFEE; padding: 4px 8px; font-size: 12px; cursor: pointer; border-radius: 2px; font-weight: bold;">−</button>
                    <button id="miner-close-btn" class="miner-close" onclick="window.haichanMiner.closeAndStop()" title="Close Dashboard & Stop Mining" style="background: #8B0000; border: 1px solid #444B6E; color: #FFFFEE; padding: 4px 8px; font-size: 12px; cursor: pointer; border-radius: 2px; font-weight: bold;">×</button>
                </div>
            </div>
            <div class="miner-content">
                <div class="mining-mode-controls">
                    <button id="float-mode-idle" class="mining-mode-btn active" title="~100-500 H/s - Low CPU usage, pattern '21'">
                        🟢 IDLE<br><span style="font-size: 7pt;">~100 H/s</span>
                    </button>
                    <button id="float-mode-active" class="mining-mode-btn" title="~1000-2000 H/s - Normal mining, pattern '21e8'">
                        🟡 ACTIVE<br><span style="font-size: 7pt;">~1K H/s</span>
                    </button>
                    <button id="float-mode-hyperactive" class="mining-mode-btn" title="~3000+ H/s - Maximum mining, pattern '21e8'">
                        🔴 HYPER<br><span style="font-size: 7pt;">~3K+ H/s</span>
                    </button>
                </div>
                
                <div class="miner-target">
                    <span class="target-label">🎯 TARGET:</span>
                    <span class="target-value" id="mining-target">Global Network</span>
                </div>
                
                <div class="miner-stats-grid">
                    <div class="stat">
                        <span class="stat-label">HASH RATE:</span>
                        <span class="stat-value" id="hash-rate">0 H/s</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">TOTAL HASHES:</span>
                        <span class="stat-value" id="total-hashes">0</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">PROOFS FOUND:</span>
                        <span class="stat-value" id="proof-count">0</span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">SESSION TIME:</span>
                        <span class="stat-value" id="session-time">00:00</span>
                    </div>
                </div>
                
                <div class="current-hash-display">
                    <div style="font-size: 9px; color: #666; margin-bottom: 3px;">CURRENT HASH:</div>
                    <div id="current-hash-preview" style="
                        font-family: 'Courier New', monospace;
                        font-size: 8px;
                        color: #00ff00;
                        background: #000;
                        padding: 4px;
                        border-radius: 3px;
                        letter-spacing: 0.5px;
                        word-break: break-all;
                        text-shadow: 0 0 5px #00ff00;
                    ">21e8000abc123def456789abcdef0123456789abcdef...</div>
                </div>
                
                <div class="miner-progress">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill"></div>
                    </div>
                    <div style="font-size: 8px; color: #666; margin-top: 2px; text-align: center;">
                        Mining Pattern: <span id="current-pattern" style="color: #00ff00; font-weight: bold;">21</span>
                    </div>
                </div>
                
                <div class="miner-log" id="miner-log">
                    <div class="log-entry">🚀 Haichan Mining Dashboard Ready</div>
                    <div class="log-entry">💡 Select mining mode and hover over content to mine</div>
                </div>
            </div>
        `;
        
        // Add styles
        const style = document.createElement('style');
        style.textContent = `
            #haichan-miner {
                position: fixed;
                top: 70px;
                right: 20px;
                width: 320px;
                background: #F5F5DC;
                border: 2px solid #708B75;
                border-radius: 5px;
                color: #444B6E;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                z-index: 9998;
                transition: all 0.3s ease;
            }
            .miner-header {
                background: #9AB87A;
                padding: 12px 15px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 3px 3px 0 0;
                border-bottom: 1px solid #708B75;
            }
            .miner-title {
                font-weight: bold;
                color: #444B6E;
                font-size: 10px;
            }
            .miner-controls {
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .miner-minimize, .miner-toggle {
                background: #F5F5DC;
                border: 1px solid #708B75;
                border-radius: 3px;
                padding: 4px 8px;
                color: #444B6E;
                cursor: pointer;
                font-size: 12px;
                transition: all 0.2s ease;
            }
            .miner-minimize:hover, .miner-toggle:hover {
                background: #FFFACD;
                transform: scale(1.05);
            }
            
            /* Strobing buzz animation for active mining */
            @keyframes buzz-strobe {
                0%, 100% { 
                    transform: translateX(0); 
                    border-color: #708B75;
                    box-shadow: 0 8px 24px rgba(112, 139, 117, 0.2);
                }
                10% { 
                    transform: translateX(-1px) translateY(-1px); 
                    border-color: #ff6b35;
                    box-shadow: 0 8px 24px rgba(255, 107, 53, 0.4);
                }
                20% { 
                    transform: translateX(1px) translateY(1px); 
                    border-color: #2ecc71;
                    box-shadow: 0 8px 24px rgba(46, 204, 113, 0.4);
                }
                30% { 
                    transform: translateX(-1px) translateY(1px); 
                    border-color: #9b59b6;
                    box-shadow: 0 8px 24px rgba(155, 89, 182, 0.4);
                }
                40% { 
                    transform: translateX(1px) translateY(-1px); 
                    border-color: #3498db;
                    box-shadow: 0 8px 24px rgba(52, 152, 219, 0.4);
                }
                50% { 
                    transform: translateX(0) translateY(-2px); 
                    border-color: #f1c40f;
                    box-shadow: 0 8px 24px rgba(241, 196, 15, 0.4);
                }
                60% { 
                    transform: translateX(-2px) translateY(0); 
                    border-color: #e74c3c;
                    box-shadow: 0 8px 24px rgba(231, 76, 60, 0.4);
                }
                70% { 
                    transform: translateX(2px) translateY(2px); 
                    border-color: #1abc9c;
                    box-shadow: 0 8px 24px rgba(26, 188, 156, 0.4);
                }
                80% { 
                    transform: translateX(0) translateY(1px); 
                    border-color: #34495e;
                    box-shadow: 0 8px 24px rgba(52, 73, 94, 0.4);
                }
                90% { 
                    transform: translateX(1px) translateY(0); 
                    border-color: #95a5a6;
                    box-shadow: 0 8px 24px rgba(149, 165, 166, 0.4);
                }
            }
            
            .miner-buzzing {
                animation: buzz-strobe 0.8s infinite ease-in-out;
            }
            .miner-minimized .miner-content {
                display: none;
            }
            .miner-minimized {
                width: auto;
            }
            .miner-content {
                padding: 12px;
            }
            .mining-mode-controls {
                display: flex;
                gap: 10px;
                margin-bottom: 15px;
                justify-content: center;
                padding-bottom: 12px;
                border-bottom: 1px solid #708B75;
            }
            .mining-mode-btn {
                border: none;
                padding: 8px 16px;
                font-size: 9pt;
                font-weight: bold;
                cursor: pointer;
                border-radius: 3px;
                opacity: 0.7;
                transition: all 0.2s ease;
                line-height: 1.2;
            }
            #float-mode-idle {
                background: #28a745;
                color: white;
            }
            #float-mode-active {
                background: #ffc107;
                color: #444;
            }
            #float-mode-hyperactive {
                background: #dc3545;
                color: white;
            }
            .mining-mode-btn:hover {
                opacity: 0.9;
            }
            .mining-mode-btn.active {
                opacity: 1;
                transform: scale(1.05);
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            .miner-target {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 12px;
                padding: 8px 12px;
                background: #FFFACD;
                border: 1px solid #708B75;
                border-radius: 3px;
            }
            .target-label {
                color: #666;
                font-weight: 500;
                font-size: 9px;
            }
            .target-value {
                font-weight: bold;
                color: #006400;
            }
            .miner-stats-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 12px;
            }
            .current-hash-display {
                margin-bottom: 10px;
            }
            .stat {
                display: flex;
                flex-direction: column;
                align-items: center;
                background: #FFFACD;
                border: 1px solid #708B75;
                border-radius: 3px;
                padding: 6px 4px;
            }
            .stat-label {
                font-size: 8px;
                color: #666;
                margin-bottom: 2px;
            }
            .stat-value {
                font-weight: bold;
                color: #006400;
                font-size: 10px;
            }
            .miner-progress {
                margin-bottom: 10px;
            }
            .progress-bar {
                width: 100%;
                height: 10px;
                background: #000;
                border: 1px solid rgba(0, 255, 0, 0.3);
                border-radius: 5px;
                overflow: hidden;
            }
            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #00ff00, #00aa00);
                width: 0%;
                transition: width 0.3s ease;
                border-radius: 4px;
                box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            }
            .miner-log {
                max-height: 90px;
                overflow-y: auto;
                background: rgba(0, 0, 0, 0.3);
                border: 1px solid rgba(0, 255, 0, 0.2);
                border-radius: 6px;
                padding: 8px;
            }
            .log-entry {
                font-size: 9px;
                margin: 2px 0;
                opacity: 0.9;
                color: #888;
            }
            .log-entry.success {
                color: #00ff00;
                font-weight: 500;
                text-shadow: 0 0 3px rgba(0, 255, 0, 0.5);
            }
            .log-entry.error {
                color: #ff6b35;
                font-weight: 500;
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(this.ui);
        
        // Set initial minimize state
        if (this.isMinimized) {
            this.ui.classList.add('miner-minimized');
        }
    }
    
    attachMiningModeControls() {
        // Initialize mining mode
        let currentMode = 'idle';
        this.updateMiningModeDisplay(currentMode);
        
        // Attach event handlers to mode buttons (both page and floating UI)
        const idleBtn = document.getElementById('mode-idle');
        const activeBtn = document.getElementById('mode-active');
        const hyperBtn = document.getElementById('mode-hyperactive');
        const floatIdleBtn = document.getElementById('float-mode-idle');
        const floatActiveBtn = document.getElementById('float-mode-active');
        const floatHyperBtn = document.getElementById('float-mode-hyperactive');
        
        if (idleBtn) idleBtn.addEventListener('click', () => this.setMiningMode('idle'));
        if (activeBtn) activeBtn.addEventListener('click', () => this.setMiningMode('active'));
        if (hyperBtn) hyperBtn.addEventListener('click', () => this.setMiningMode('hyperactive'));
        if (floatIdleBtn) floatIdleBtn.addEventListener('click', () => this.setMiningMode('idle'));
        if (floatActiveBtn) floatActiveBtn.addEventListener('click', () => this.setMiningMode('active'));
        if (floatHyperBtn) floatHyperBtn.addEventListener('click', () => this.setMiningMode('hyperactive'));
        
        // Update mining stats in dashboard every second
        setInterval(() => {
            this.updateDashboard();
        }, 1000);
    }
    
    setMiningMode(mode) {
        this.updateMiningModeDisplay(mode);
        
        // Set mining intensity and pattern based on mode
        switch(mode) {
            case 'idle':
                this.setIntensity(0.1); // Very low intensity
                this.pattern = '21e8'; // Always use 21e8 vanity marker
                break;
            case 'active':
                this.setIntensity(1.0); // Normal intensity
                this.pattern = '21e8'; // Always use 21e8 vanity marker
                break;
            case 'hyperactive':
                this.setIntensity(3.0); // High intensity
                this.pattern = '21e8'; // Always use 21e8 vanity marker
                break;
        }
        
        // Update target display to reflect mining mode
        const modeDisplayNames = {
            'idle': 'Global Network (IDLE)',
            'active': 'Global Network (ACTIVE)', 
            'hyperactive': 'Global Network (HYPER)'
        };
        const displayName = modeDisplayNames[mode] || 'Global Network';
        const targetEl = document.getElementById('mining-target');
        if (targetEl) {
            targetEl.textContent = displayName;
        }
        this.currentTarget = displayName;
        
        this.log(`🎯 Mining mode set to ${mode.toUpperCase()} - Pattern: ${this.pattern}`);
    }
    
    updateMiningModeDisplay(mode) {
        // Reset all buttons (both page and floating UI)
        document.querySelectorAll('.mining-mode-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.opacity = '0.5';
            btn.style.transform = 'scale(1)';
        });
        
        // Highlight active mode (both page and floating UI)
        const pageBtns = [document.getElementById('mode-' + mode)];
        const floatBtn = document.getElementById('float-mode-' + mode);
        
        [...pageBtns, floatBtn].forEach(btn => {
            if (btn) {
                btn.classList.add('active');
                btn.style.opacity = '1';
                btn.style.transform = 'scale(1.1)';
            }
        });
    }
    
    updateDashboard() {
        if (this.isActive) {
            const elapsed = (Date.now() - this.startTime) / 1000;
            const hashrate = Math.round(this.hashCount / Math.max(elapsed, 1));
            
            // Update dashboard elements if they exist
            const targetEl = document.getElementById('mining-target');
            const hashrateEl = document.getElementById('current-hashrate');
            const statsEl = document.getElementById('session-stats');
            
            if (targetEl) targetEl.textContent = this.currentTarget || 'Global Network';
            if (hashrateEl) hashrateEl.textContent = hashrate + ' H/s';
            if (statsEl) statsEl.textContent = this.hashCount + ' hashes | ' + (this.sessionProofs || 0) + ' proofs';
        }
    }
    
    attachEventListeners() {
        // Mining mode controls
        this.attachMiningModeControls();
        
        // Thread mining on hover
        document.addEventListener('mouseover', (e) => {
            // Check for mining-specific elements first (highest priority)
            const miningElement = e.target.closest('[data-mine-type]');
            if (miningElement) {
                const mineType = miningElement.dataset.mineType;
                const mineTarget = miningElement.dataset.mineTarget;
                
                if (mineType === 'reply') {
                    const postId = miningElement.dataset.postId || mineTarget.replace('reply-', '');
                    this.switchTarget('reply', postId, `Reply #${postId}`);
                    return;
                } else if (mineType === 'thread') {
                    const threadId = miningElement.dataset.threadId || mineTarget.replace('thread-', '');
                    const threadTitle = miningElement.dataset.threadTitle || `Thread #${threadId}`;
                    this.switchTarget('thread', threadId, threadTitle);
                    return;
                }
            }
            
            // Fallback to thread detection for non-mining elements
            const threadElement = e.target.closest('[data-thread-id]');
            if (threadElement) {
                const threadId = threadElement.dataset.threadId;
                const threadTitle = threadElement.dataset.threadTitle || `Thread #${threadId}`;
                this.switchTarget('thread', threadId, threadTitle);
            }
            
            // User profile mining
            const userElement = e.target.closest('[data-user-id]');
            if (userElement && !threadElement && !miningElement) {
                const userId = userElement.dataset.userId;
                const userName = userElement.dataset.userName || `User #${userId}`;
                this.switchTarget('user', userId, userName);
            }
            
            // Board mining
            const boardElement = e.target.closest('[data-board-code]') || e.target.closest('[data-board-name]');
            if (boardElement && !threadElement && !userElement && !miningElement) {
                const boardCode = boardElement.dataset.boardCode || boardElement.dataset.boardName;
                const boardName = boardElement.dataset.boardName || boardElement.dataset.boardCode || `/${boardCode}/`;
                this.switchTarget('board', boardCode, boardName);
            }
        });
        
        // Global mining when not hovering anything specific
        document.addEventListener('mouseover', (e) => {
            if (!e.target.closest('[data-thread-id]') && 
                !e.target.closest('[data-user-id]') && 
                !e.target.closest('[data-board-code]') &&
                !e.target.closest('[data-board-name]') &&
                !e.target.closest('[data-mine-type]')) {
                this.switchTarget('global', 'haichan', 'Global Network');
            }
        });
    }
    
    switchTarget(type, id, displayName) {
        if (this.currentTargetType === type && this.currentTargetId === id) {
            return; // Already mining this target
        }
        
        this.currentTargetType = type;
        this.currentTargetId = id;
        this.currentTarget = displayName;
        
        // Update UI
        const targetEl = document.getElementById('mining-target');
        if (targetEl) {
            targetEl.textContent = displayName;
        }
        this.log(`🎯 Switched to mining: ${displayName}`);
        
        // Reset mining progress for new target
        this.nonce = Math.floor(Math.random() * 1000000); // Random starting nonce
        this.resetProgress();
    }
    
    startGlobalMining() {
        if (this.isActive) return;
        
        this.isActive = true;
        this.startTime = Date.now();
        this.hashCount = 0;
        
        // Start mining loop with intensity-based interval
        const baseInterval = 10; // Base 10ms interval
        const interval = Math.max(1, baseInterval / this.intensity); // Faster with higher intensity
        this.miningInterval = setInterval(() => {
            this.mineStep();
        }, interval);
        
        // Update hash rate every second
        this.hashRateInterval = setInterval(() => {
            this.updateStats();
        }, 1000);
        
        // Start buzzing animation
        if (this.buzzingEnabled && this.ui) {
            this.ui.classList.add('miner-buzzing');
        }
        
        this.log('🚀 Mining started');
    }
    
    mineStep() {
        if (!this.isActive || !this.currentTargetType) return;
        
        const baseData = this.generateMiningData();
        const testData = `${baseData}:${this.nonce}`;
        
        // Use Web Crypto API for hashing
        this.sha256(testData).then(hash => {
            this.hashCount++;
            this.currentHash = hash; // Store current hash for display
            
            // Only accept hashes that start with the exact pattern
            const pattern = this.pattern.toLowerCase();
            const hashLower = hash.toLowerCase();
            
            // Validate hash matches pattern
            if (hashLower.startsWith(pattern)) {
                this.foundProof(hash, this.nonce, baseData);
            }
            
            this.nonce++;
        });
    }
    
    generateMiningData() {
        const timestamp = Date.now();
        if (this.currentTargetType === 'reply') {
            // For replies, include thread context in the data
            const threadId = document.querySelector('[data-thread-id]')?.dataset.threadId || 'unknown';
            return `${this.currentTargetType}:${this.currentTargetId}:thread-${threadId}:${timestamp}`;
        }
        return `${this.currentTargetType}:${this.currentTargetId}:${timestamp}`;
    }
    
    async sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
        const hashArray = Array.from(new Uint8Array(hashBuffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    }
    
    foundProof(hash, nonce, data) {
        const proofCount = parseInt(document.getElementById('proof-count')?.textContent || 0) + 1;
        if (document.getElementById('proof-count')) {
            document.getElementById('proof-count').textContent = proofCount;
        }
        
        // Update session counters
        this.sessionProofs = (this.sessionProofs || 0) + 1;
        
        // Determine proof difficulty and rarity
        const difficulty = this.getProofDifficulty(hash);
        const rarity = this.getProofRarity(hash);
        
        this.log(`${difficulty.emoji} PROOF FOUND! ${hash.substring(0, 16)}... (${this.currentTarget}) [${rarity.name}]`, 'success');
        
        // Submit proof to server
        this.submitProof(hash, nonce, data);
        
        // Enhanced visual feedback based on rarity
        this.celebrateProof(rarity, hash);
        
        // Continue mining with new nonce range
        this.nonce = Math.floor(Math.random() * 1000000);
    }

    getProofDifficulty(hash) {
        if (hash.startsWith('000021e8')) return { emoji: '🌟', pattern: '000021e8' };
        if (hash.startsWith('21e8000')) return { emoji: '💎', pattern: '21e8000' };
        if (hash.startsWith('21e800')) return { emoji: '🔥', pattern: '21e800' };
        if (hash.startsWith('21e80')) return { emoji: '✨', pattern: '21e80' };
        if (hash.startsWith('21e8')) return { emoji: '🎯', pattern: '21e8' };
        if (hash.startsWith('21')) return { emoji: '🟢', pattern: '21' }; // Idle pattern
        return { emoji: '🟢', pattern: '21' }; // Default to idle
    }

    getProofRarity(hash) {
        if (hash.startsWith('000021e8')) return { 
            name: 'LEGENDARY', 
            color: '#ff6b35', 
            intensity: 'epic',
            sound: 'legendary'
        };
        if (hash.startsWith('21e8000')) return { 
            name: 'RARE', 
            color: '#9b59b6', 
            intensity: 'strong',
            sound: 'rare'
        };
        if (hash.startsWith('21e800')) return { 
            name: 'UNCOMMON', 
            color: '#3498db', 
            intensity: 'medium',
            sound: 'uncommon'
        };
        if (hash.startsWith('21e80')) return { 
            name: 'COMMON+', 
            color: '#2ecc71', 
            intensity: 'light',
            sound: 'common'
        };
        if (hash.startsWith('21')) return { 
            name: 'IDLE', 
            color: '#95a5a6', 
            intensity: 'minimal',
            sound: 'basic'
        };
        return { 
            name: 'COMMON', 
            color: '#95a5a6', 
            intensity: 'minimal',
            sound: 'basic'
        };
    }

    celebrateProof(rarity, hash) {
        // Enhanced visual celebration based on rarity
        switch(rarity.intensity) {
            case 'epic':
                this.epicCelebration(rarity, hash);
                break;
            case 'strong':
                this.strongCelebration(rarity, hash);
                break;
            case 'medium':
                this.mediumCelebration(rarity, hash);
                break;
            case 'light':
                this.lightCelebration(rarity, hash);
                break;
            default:
                this.basicCelebration(rarity, hash);
        }
    }

    epicCelebration(rarity, hash) {
        // Screen flash + shake + sparks
        this.screenFlash(rarity.color);
        this.shakeUI('epic');
        this.createSparks('epic', rarity.color);
        this.screenText('🌟 LEGENDARY PROOF! 🌟', rarity.color);
        this.playSound('legendary');
        
        // Extra dramatic progress animation
        this.epicProgressAnimation();
    }

    strongCelebration(rarity, hash) {
        // UI shake + sparks
        this.shakeUI('strong');
        this.createSparks('strong', rarity.color);
        this.screenText('💎 RARE PROOF! 💎', rarity.color);
        this.playSound('rare');
        this.strongProgressAnimation();
    }

    mediumCelebration(rarity, hash) {
        // Light shake + small sparks
        this.shakeUI('medium');
        this.createSparks('medium', rarity.color);
        this.screenText('🔥 UNCOMMON! 🔥', rarity.color);
        this.playSound('uncommon');
        this.mediumProgressAnimation();
    }

    lightCelebration(rarity, hash) {
        // Gentle pulse + tiny sparks
        this.pulseUI();
        this.createSparks('light', rarity.color);
        this.playSound('common');
        this.lightProgressAnimation();
    }

    basicCelebration(rarity, hash) {
        // Simple flash
        this.flashProgress();
        this.playSound('basic');
    }

    screenFlash(color) {
        const flash = document.createElement('div');
        flash.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: ${color};
            opacity: 0.3;
            z-index: 999999;
            pointer-events: none;
            animation: flashAnimation 0.5s ease-out;
        `;
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes flashAnimation {
                0% { opacity: 0.5; }
                50% { opacity: 0.8; }
                100% { opacity: 0; }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(flash);
        
        setTimeout(() => {
            document.body.removeChild(flash);
            document.head.removeChild(style);
        }, 500);
    }

    shakeUI(intensity) {
        const shakeClass = `shake-${intensity}`;
        const style = document.createElement('style');
        
        let keyframes = '';
        switch(intensity) {
            case 'epic':
                keyframes = `
                    @keyframes shake-epic {
                        0%, 100% { transform: translate(0, 0) rotate(0deg); }
                        10% { transform: translate(-10px, -5px) rotate(-2deg); }
                        20% { transform: translate(10px, 5px) rotate(2deg); }
                        30% { transform: translate(-8px, 3px) rotate(-1deg); }
                        40% { transform: translate(8px, -3px) rotate(1deg); }
                        50% { transform: translate(-6px, 2px) rotate(-1deg); }
                        60% { transform: translate(6px, -2px) rotate(1deg); }
                        70% { transform: translate(-4px, 1px) rotate(-0.5deg); }
                        80% { transform: translate(4px, -1px) rotate(0.5deg); }
                        90% { transform: translate(-2px, 0px) rotate(0deg); }
                    }
                    .shake-epic { animation: shake-epic 1s ease-in-out; }
                `;
                break;
            case 'strong':
                keyframes = `
                    @keyframes shake-strong {
                        0%, 100% { transform: translate(0, 0) rotate(0deg); }
                        25% { transform: translate(-5px, -2px) rotate(-1deg); }
                        50% { transform: translate(5px, 2px) rotate(1deg); }
                        75% { transform: translate(-3px, 1px) rotate(-0.5deg); }
                    }
                    .shake-strong { animation: shake-strong 0.6s ease-in-out; }
                `;
                break;
            case 'medium':
                keyframes = `
                    @keyframes shake-medium {
                        0%, 100% { transform: translate(0, 0); }
                        25% { transform: translate(-3px, -1px); }
                        50% { transform: translate(3px, 1px); }
                        75% { transform: translate(-2px, 0px); }
                    }
                    .shake-medium { animation: shake-medium 0.4s ease-in-out; }
                `;
                break;
        }
        
        style.textContent = keyframes;
        document.head.appendChild(style);
        
        this.ui.classList.add(shakeClass);
        setTimeout(() => {
            this.ui.classList.remove(shakeClass);
            document.head.removeChild(style);
        }, 1000);
    }

    createSparks(intensity, color) {
        const sparkCount = intensity === 'epic' ? 15 : intensity === 'strong' ? 10 : intensity === 'medium' ? 6 : 3;
        const minerRect = this.ui.getBoundingClientRect();
        
        for (let i = 0; i < sparkCount; i++) {
            this.createSingleSpark(minerRect, color, intensity);
        }
    }

    createSingleSpark(minerRect, color, intensity) {
        const spark = document.createElement('div');
        
        const size = intensity === 'epic' ? 8 : intensity === 'strong' ? 6 : 4;
        const duration = intensity === 'epic' ? 2000 : 1500;
        
        spark.style.cssText = `
            position: fixed;
            width: ${size}px;
            height: ${size}px;
            background: ${color};
            border-radius: 50%;
            z-index: 999999;
            pointer-events: none;
            box-shadow: 0 0 ${size*2}px ${color};
        `;
        
        // Random start position around the miner UI
        const startX = minerRect.left + minerRect.width/2 + (Math.random() - 0.5) * 50;
        const startY = minerRect.top + minerRect.height/2 + (Math.random() - 0.5) * 50;
        
        // Random end position
        const endX = startX + (Math.random() - 0.5) * 200;
        const endY = startY + (Math.random() - 0.5) * 200;
        
        spark.style.left = startX + 'px';
        spark.style.top = startY + 'px';
        
        document.body.appendChild(spark);
        
        // Animate spark
        spark.animate([
            { 
                left: startX + 'px', 
                top: startY + 'px', 
                opacity: 1, 
                transform: 'scale(1)' 
            },
            { 
                left: endX + 'px', 
                top: endY + 'px', 
                opacity: 0, 
                transform: 'scale(0)' 
            }
        ], {
            duration: duration,
            easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
        }).onfinish = () => {
            document.body.removeChild(spark);
        };
    }

    screenText(text, color) {
        const textEl = document.createElement('div');
        textEl.textContent = text;
        textEl.style.cssText = `
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-family: 'Courier New', monospace;
            font-size: 2rem;
            font-weight: bold;
            color: ${color};
            text-shadow: 0 0 20px ${color}, 0 0 40px ${color};
            z-index: 999999;
            pointer-events: none;
            animation: textPulse 2s ease-out;
        `;
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes textPulse {
                0% { opacity: 0; transform: translate(-50%, -50%) scale(0.5); }
                10% { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
                20% { transform: translate(-50%, -50%) scale(1); }
                90% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            }
        `;
        
        document.head.appendChild(style);
        document.body.appendChild(textEl);
        
        setTimeout(() => {
            document.body.removeChild(textEl);
            document.head.removeChild(style);
        }, 2000);
    }

    epicProgressAnimation() {
        const fill = document.getElementById('progress-fill');
        fill.style.background = 'linear-gradient(90deg, #ff6b35, #ffd700, #ff6b35)';
        fill.style.width = '100%';
        fill.style.boxShadow = '0 0 20px #ff6b35';
        
        setTimeout(() => {
            fill.style.background = 'linear-gradient(90deg, #00ff88, #ffd700)';
            fill.style.width = '0%';
            fill.style.boxShadow = 'none';
        }, 1000);
    }

    strongProgressAnimation() {
        const fill = document.getElementById('progress-fill');
        fill.style.background = 'linear-gradient(90deg, #9b59b6, #ffd700)';
        fill.style.width = '100%';
        fill.style.boxShadow = '0 0 15px #9b59b6';
        
        setTimeout(() => {
            fill.style.background = 'linear-gradient(90deg, #00ff88, #ffd700)';
            fill.style.width = '0%';
            fill.style.boxShadow = 'none';
        }, 800);
    }

    mediumProgressAnimation() {
        const fill = document.getElementById('progress-fill');
        fill.style.background = 'linear-gradient(90deg, #3498db, #ffd700)';
        fill.style.width = '100%';
        
        setTimeout(() => {
            fill.style.background = 'linear-gradient(90deg, #00ff88, #ffd700)';
            fill.style.width = '0%';
        }, 600);
    }

    lightProgressAnimation() {
        const fill = document.getElementById('progress-fill');
        fill.style.background = 'linear-gradient(90deg, #2ecc71, #ffd700)';
        fill.style.width = '100%';
        
        setTimeout(() => {
            fill.style.background = 'linear-gradient(90deg, #00ff88, #ffd700)';
            fill.style.width = '0%';
        }, 400);
    }

    pulseUI() {
        this.ui.style.animation = 'pulse 0.5s ease-in-out';
        
        setTimeout(() => {
            this.ui.style.animation = '';
        }, 500);
    }

    playSound(type) {
        // Create audio context for sound effects
        if (typeof AudioContext !== 'undefined' || typeof webkitAudioContext !== 'undefined') {
            const AudioCtx = AudioContext || webkitAudioContext;
            const audioCtx = new AudioCtx();
            
            let frequency, duration;
            switch(type) {
                case 'legendary':
                    this.playChime(audioCtx, [523, 659, 784], 500);
                    break;
                case 'rare':
                    this.playChime(audioCtx, [440, 554], 300);
                    break;
                case 'uncommon':
                    this.playChime(audioCtx, [440], 200);
                    break;
                case 'common':
                    this.playTone(audioCtx, 550, 100);
                    break;
                default:
                    this.playTone(audioCtx, 440, 50);
            }
        }
    }

    playTone(audioCtx, frequency, duration) {
        const oscillator = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        
        oscillator.frequency.setValueAtTime(frequency, audioCtx.currentTime);
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + duration / 1000);
        
        oscillator.start(audioCtx.currentTime);
        oscillator.stop(audioCtx.currentTime + duration / 1000);
    }

    playChime(audioCtx, frequencies, duration) {
        frequencies.forEach((freq, index) => {
            setTimeout(() => {
                this.playTone(audioCtx, freq, duration / 2);
            }, index * 100);
        });
    }
    
    async submitProof(hash, nonce, data) {
        try {
            // Detect the actual pattern from the hash first
            const difficulty = this.getProofDifficulty(hash);
            const detectedPattern = difficulty.pattern;
            
            // Validate that hash starts with a valid pattern (not necessarily our current mining pattern)
            const validPatterns = ['000021e8', '21e8000', '21e800', '21e80', '21e8', '21'];
            const isValidPattern = validPatterns.some(pattern => hash.toLowerCase().startsWith(pattern.toLowerCase()));
            
            if (!isValidPattern) {
                this.log(`❌ Invalid hash detected - no valid pattern found`, 'error');
                return;
            }
            
            const response = await fetch('/api/submit-proof', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    hash: hash,
                    nonce: nonce,
                    data: data,
                    pattern: detectedPattern,
                    target_type: this.currentTargetType,
                    target_id: this.currentTargetId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.log(`✅ Proof accepted! Added to thread ranking`, 'success');
            } else {
                this.log(`❌ Proof rejected: ${result.message}`, 'error');
            }
        } catch (error) {
            this.log(`❌ Network error: ${error.message}`, 'error');
        }
    }
    
    updateStats() {
        if (!this.isActive) return;
        
        const elapsed = (Date.now() - this.startTime) / 1000;
        const hashRate = Math.floor(this.hashCount / elapsed);
        
        // Update floating miner stats
        const hashRateEl = document.getElementById('hash-rate');
        if (hashRateEl) {
            hashRateEl.textContent = `${hashRate.toLocaleString()} H/s`;
        }
        
        // Update total hashes
        const totalHashesEl = document.getElementById('total-hashes');
        if (totalHashesEl) {
            totalHashesEl.textContent = this.hashCount.toLocaleString();
        }
        
        // Update session time
        const sessionTimeEl = document.getElementById('session-time');
        if (sessionTimeEl) {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            sessionTimeEl.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        // Update current hash preview
        const hashPreviewEl = document.getElementById('current-hash-preview');
        if (hashPreviewEl && this.currentHash) {
            hashPreviewEl.textContent = this.currentHash;
            // Flash effect
            hashPreviewEl.style.textShadow = '0 0 10px #00ff00';
            setTimeout(() => {
                if (hashPreviewEl) hashPreviewEl.style.textShadow = '0 0 5px #00ff00';
            }, 50);
        }
        
        // Update pattern display
        const patternEl = document.getElementById('current-pattern');
        if (patternEl) {
            patternEl.textContent = this.pattern;
        }
        
        // Update progress bar (visual feedback based on hash count)
        const progressEl = document.getElementById('progress-fill');
        if (progressEl) {
            const progress = (this.hashCount % 10000) / 100; // Reset every 10k hashes
            progressEl.style.width = `${progress}%`;
        }
        
        // UPDATE PROMINENT STATUS BAR
        this.updateProminentStatusBar(hashRate);
        
        // Update mini dashboard if visible
        const miniOverlay = document.getElementById('mini-dashboard-overlay');
        if (miniOverlay && miniOverlay.style.display === 'block') {
            this.updateMiniDashboard();
        }
    }
    
    updateProminentStatusBar(hashRate) {
        // Update network hash rate
        const networkHashrateEl = document.getElementById('network-hashrate');
        if (networkHashrateEl) {
            networkHashrateEl.textContent = `${hashRate.toLocaleString()} H/s`;
        }
        
        // Update total hashes
        const totalHashesEl = document.getElementById('network-total-hashes');
        if (totalHashesEl) {
            totalHashesEl.textContent = this.hashCount.toLocaleString();
        }
        
        // Update valid proofs count
        const validProofsEl = document.getElementById('network-valid-proofs');
        if (validProofsEl) {
            validProofsEl.textContent = (this.sessionProofs || 0).toString();
        }
        
        // Update current hash display
        const currentHashEl = document.getElementById('current-mining-hash');
        if (currentHashEl && this.currentHash) {
            currentHashEl.textContent = this.currentHash.substring(0, 20) + '...';
            // Flash the hash display when it updates
            currentHashEl.style.color = '#00ff00';
            setTimeout(() => {
                if (currentHashEl) currentHashEl.style.color = '#888';
            }, 100);
        }
        
        // Update difficulty
        const difficultyEl = document.getElementById('current-difficulty');
        if (difficultyEl) {
            difficultyEl.textContent = this.pattern;
        }
        
        // Update mining indicator animation speed based on hash rate
        const indicatorEl = document.getElementById('mining-indicator');
        if (indicatorEl) {
            const speed = Math.max(0.2, Math.min(2.0, hashRate / 1000)); // Scale animation speed
            indicatorEl.style.animationDuration = `${1/speed}s`;
            
            // Change color based on intensity
            if (hashRate > 2000) {
                indicatorEl.style.background = '#ff6b35'; // Orange for hyper
            } else if (hashRate > 500) {
                indicatorEl.style.background = '#ffd700'; // Yellow for active
            } else {
                indicatorEl.style.background = '#00ff00'; // Green for idle
            }
        }
    }
    
    flashProgress() {
        const fill = document.getElementById('progress-fill');
        fill.style.background = 'linear-gradient(90deg, #ffd700, #00ff88)';
        fill.style.width = '100%';
        
        setTimeout(() => {
            fill.style.background = 'linear-gradient(90deg, #00ff88, #ffd700)';
            fill.style.width = '0%';
        }, 500);
    }
    
    resetProgress() {
        document.getElementById('progress-fill').style.width = '0%';
    }
    
    log(message, type = 'info') {
        
        const logElement = document.getElementById('miner-log');
        if (!logElement) return;
        
        const entry = document.createElement('div');
        entry.className = `log-entry ${type}`;
        entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
        
        logElement.appendChild(entry);
        logElement.scrollTop = logElement.scrollHeight;
        
        // Keep only last 20 entries
        while (logElement.children.length > 20) {
            logElement.removeChild(logElement.firstChild);
        }
    }
    
    toggle() {
        if (this.isActive) {
            this.stop();
        } else {
            this.startGlobalMining();
        }
    }
    
    toggleMinimize() {
        this.isMinimized = !this.isMinimized;
        localStorage.setItem('haichan-miner-minimized', this.isMinimized.toString());
        
        const minimizeBtn = document.getElementById('miner-minimize-btn');
        
        if (this.isMinimized) {
            this.ui.classList.add('miner-minimized');
            if (minimizeBtn) {
                minimizeBtn.textContent = '+';
                minimizeBtn.title = 'Maximize';
            }
        } else {
            this.ui.classList.remove('miner-minimized');
            if (minimizeBtn) {
                minimizeBtn.textContent = '−';
                minimizeBtn.title = 'Minimize';
            }
        }
    }
    
    closeAndStop() {
        // Stop all mining first
        this.stopMining();
        this.stopGlobalMining();
        
        // Hide the dashboard
        this.ui.style.display = 'none';
        
        // Update the toolbar button to show it can restart mining
        this.updateMiningIndicator();
    }
    
    setIntensity(newIntensity) {
        this.intensity = Math.max(0.1, Math.min(5.0, newIntensity));
        
        // Restart mining with new intensity if currently active
        if (this.isActive) {
            this.restartMiningWithNewIntensity();
        }
        
        this.log(`⚡ Mining intensity set to ${newIntensity}x`);
    }
    
    restartMiningWithNewIntensity() {
        // Clear existing intervals
        if (this.miningInterval) {
            clearInterval(this.miningInterval);
        }
        
        // Start new mining loop with updated intensity
        const baseInterval = 10; // Base 10ms interval
        const interval = Math.max(1, baseInterval / this.intensity); // Faster with higher intensity
        this.miningInterval = setInterval(() => {
            this.mineStep();
        }, interval);
    }
    
    stop() {
        this.isActive = false;
        
        if (this.miningInterval) {
            clearInterval(this.miningInterval);
            this.miningInterval = null;
        }
        
        if (this.hashRateInterval) {
            clearInterval(this.hashRateInterval);
            this.hashRateInterval = null;
        }
        
        // Stop buzzing animation
        if (this.ui) {
            this.ui.classList.remove('miner-buzzing');
        }
        
        document.getElementById('hash-rate').textContent = '0 H/s';
        document.querySelector('.miner-toggle').textContent = '▶️';
        
        this.log('⏸️ Mining stopped');
    }
    
    setupMiniDashboard() {
        // Set up mini dashboard toggle functionality
        const toggleBtn = document.getElementById('mini-dash-toggle');
        const closeBtn = document.getElementById('mini-dash-close');
        const overlay = document.getElementById('mini-dashboard-overlay');
        
        if (toggleBtn && overlay) {
            // Toggle button click
            toggleBtn.addEventListener('click', () => {
                this.toggleMiniDashboard();
            });
            
            // Close button click
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    this.hideMiniDashboard();
                });
            }
            
            // Minimize button click
            const minimizeBtn = document.getElementById('mini-dash-minimize');
            if (minimizeBtn) {
                minimizeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.minimizeMiniDashboard();
                });
            }
            
            // Make dashboard draggable
            const header = document.getElementById('mini-dash-header');
            if (header) {
                this.makeDraggable(overlay, header);
            }
            
            // Ctrl+D keyboard shortcut
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.key === 'd') {
                    e.preventDefault(); // Prevent browser bookmark shortcut
                    this.toggleMiniDashboard();
                }
            });
            
            // Mini dashboard mining controls
            this.setupMiniControls();
            
            // Start emoji cycling for toggle button
            this.startEmojiCycling(toggleBtn);
        }
    }
    
    startEmojiCycling(button) {
        // Cycle through mining emojis every 2 seconds
        setInterval(() => {
            this.emojiIndex = (this.emojiIndex + 1) % this.miningEmojis.length;
            if (button) {
                button.textContent = this.miningEmojis[this.emojiIndex];
            }
        }, 2000);
    }
    
    setupMiniControls() {
        // Connect mini dashboard controls to main mining functions
        const miniIdleBtn = document.getElementById('mini-idle-btn');
        const miniActiveBtn = document.getElementById('mini-active-btn');
        const miniHyperBtn = document.getElementById('mini-hyper-btn');
        const miniStopBtn = document.getElementById('mini-stop-btn');
        
        if (miniIdleBtn) miniIdleBtn.addEventListener('click', () => this.setMiningMode('idle'));
        if (miniActiveBtn) miniActiveBtn.addEventListener('click', () => this.setMiningMode('active'));
        if (miniHyperBtn) miniHyperBtn.addEventListener('click', () => this.setMiningMode('hyperactive'));
        if (miniStopBtn) miniStopBtn.addEventListener('click', () => this.stop());
    }
    
    toggleMiniDashboard() {
        const overlay = document.getElementById('mini-dashboard-overlay');
        if (overlay) {
            if (overlay.style.display === 'none' || !overlay.style.display) {
                this.showMiniDashboard();
            } else {
                this.hideMiniDashboard();
            }
        }
    }
    
    showMiniDashboard() {
        const overlay = document.getElementById('mini-dashboard-overlay');
        if (overlay) {
            overlay.style.display = 'block';
            // Update mini dashboard with current stats
            this.updateMiniDashboard();
        }
    }
    
    hideMiniDashboard() {
        const overlay = document.getElementById('mini-dashboard-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    minimizeMiniDashboard() {
        const overlay = document.getElementById('mini-dashboard-overlay');
        if (overlay) {
            // Store current height
            if (!overlay.dataset.originalHeight) {
                overlay.dataset.originalHeight = overlay.style.height || '250px';
            }
            
            // Toggle between minimized and normal state
            if (overlay.classList.contains('minimized')) {
                // Restore
                overlay.classList.remove('minimized');
                overlay.style.height = overlay.dataset.originalHeight;
                const contentSections = overlay.querySelectorAll('.mini-dash-content');
                contentSections.forEach(section => section.style.display = 'block');
                const minimizeBtn = document.getElementById('mini-dash-minimize');
                if (minimizeBtn) minimizeBtn.textContent = '−';
            } else {
                // Minimize
                overlay.classList.add('minimized');
                overlay.style.height = '40px';
                const contentSections = overlay.querySelectorAll('.mini-dash-content');
                contentSections.forEach(section => section.style.display = 'none');
                const minimizeBtn = document.getElementById('mini-dash-minimize');
                if (minimizeBtn) minimizeBtn.textContent = '+';
            }
        }
    }
    
    makeDraggable(element, handle) {
        let isDragging = false;
        let startX, startY, initialX, initialY;
        
        handle.addEventListener('mousedown', (e) => {
            if (e.target.tagName === 'BUTTON') return; // Don't drag when clicking buttons
            
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            
            const rect = element.getBoundingClientRect();
            initialX = rect.left;
            initialY = rect.top;
            
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
            
            e.preventDefault();
        });
        
        function drag(e) {
            if (!isDragging) return;
            
            const dx = e.clientX - startX;
            const dy = e.clientY - startY;
            
            element.style.left = (initialX + dx) + 'px';
            element.style.top = (initialY + dy) + 'px';
            element.style.right = 'auto'; // Remove right positioning
        }
        
        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }
    }
    
    updateMiniDashboard() {
        if (!this.isActive) return;
        
        const elapsed = (Date.now() - this.startTime) / 1000;
        const hashRate = Math.floor(this.hashCount / elapsed);
        
        // Update mini dashboard elements
        const miniModeEl = document.getElementById('mini-mining-mode');
        const miniRateEl = document.getElementById('mini-personal-rate');
        const miniHashEl = document.getElementById('mini-hash-preview');
        
        // Determine current mode based on intensity
        let currentMode = 'IDLE';
        if (this.intensity >= 3.0) currentMode = 'HYPER';
        else if (this.intensity >= 1.0) currentMode = 'ACTIVE';
        
        if (miniModeEl) miniModeEl.textContent = currentMode;
        if (miniRateEl) miniRateEl.textContent = `${hashRate.toLocaleString()} H/s`;
        if (miniHashEl && this.currentHash) {
            miniHashEl.textContent = this.currentHash.substring(0, 24) + '...';
        }
        
        // Update button states
        document.querySelectorAll('#mini-dashboard-overlay button').forEach(btn => {
            btn.style.background = '#FFFACD';
        });
        
        if (currentMode === 'IDLE') {
            const idleBtn = document.getElementById('mini-idle-btn');
            if (idleBtn) idleBtn.style.background = '#d4edda';
        } else if (currentMode === 'ACTIVE') {
            const activeBtn = document.getElementById('mini-active-btn');
            if (activeBtn) activeBtn.style.background = '#fff3cd';
        } else if (currentMode === 'HYPER') {
            const hyperBtn = document.getElementById('mini-hyper-btn');
            if (hyperBtn) hyperBtn.style.background = '#f8d7da';
        }
    }
    
    // Global Emoji Cycling System (Temporarily Disabled)
    initGlobalEmojiCycling() {
        // Temporarily disable global emoji cycling to fix performance issue
        console.log('Global emoji cycling disabled for now');
        // Only cycle the mini dashboard toggle button
        const toggleBtn = document.getElementById('mini-dash-toggle');
        if (toggleBtn) {
            this.startSpecificEmojiCycling(toggleBtn);
        }
    }
    
    scanAndCycleEmojis() {
        // Find all text nodes that contain emojis
        const walker = document.createTreeWalker(
            document.body,
            NodeFilter.SHOW_TEXT,
            null,
            false
        );
        
        const emojiNodes = [];
        let node;
        while (node = walker.nextNode()) {
            if (this.containsEmoji(node.textContent)) {
                emojiNodes.push(node);
            }
        }
        
        // Also check button and span elements that might contain emojis
        document.querySelectorAll('button, span, div, a, p, h1, h2, h3, h4, h5, h6').forEach(el => {
            if (this.containsEmoji(el.textContent) && !this.activeEmojiCycles.has(el)) {
                this.startEmojiCycling(el);
            }
        });
    }
    
    containsEmoji(text) {
        // Regex to detect emojis (basic detection)
        const emojiRegex = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/u;
        return emojiRegex.test(text);
    }
    
    startEmojiCycling(element) {
        if (this.activeEmojiCycles.has(element)) return; // Already cycling
        
        const originalText = element.textContent;
        const emojis = this.extractEmojis(originalText);
        
        if (emojis.length === 0) return;
        
        // For each emoji, determine its category and cycle through similar ones
        let cycleIndex = 0;
        const cycleData = {
            originalText: originalText,
            emojis: emojis.map(emoji => ({
                original: emoji,
                category: this.detectEmojiCategory(emoji),
                currentIndex: 0
            })),
            element: element
        };
        
        this.activeEmojiCycles.set(element, cycleData);
        
        // Start cycling with varied intervals (2-4 seconds)
        const baseInterval = 2000 + Math.random() * 2000;
        const intervalId = setInterval(() => {
            this.cycleElementEmojis(cycleData);
        }, baseInterval);
        
        // Store interval ID for cleanup
        cycleData.intervalId = intervalId;
    }
    
    extractEmojis(text) {
        // Extract all emojis from text
        const emojiRegex = /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]/gu;
        return text.match(emojiRegex) || [];
    }
    
    detectEmojiCategory(emoji) {
        // Try to categorize the emoji
        for (const [category, emojis] of Object.entries(this.emojiCategories)) {
            if (emojis.includes(emoji)) {
                return category;
            }
        }
        
        // If no category found, try to guess based on unicode ranges
        const code = emoji.codePointAt(0);
        if (code >= 0x1F600 && code <= 0x1F64F) return 'faces';
        if (code >= 0x1F680 && code <= 0x1F6FF) return 'tech';
        if (code >= 0x2600 && code <= 0x26FF) return 'symbols';
        
        return 'random'; // Default to random category
    }
    
    cycleElementEmojis(cycleData) {
        let newText = cycleData.originalText;
        
        cycleData.emojis.forEach(emojiData => {
            let newEmoji;
            
            // 15% chance of completely random emoji
            if (Math.random() < 0.15) {
                const randomCategory = Object.keys(this.emojiCategories)[Math.floor(Math.random() * Object.keys(this.emojiCategories).length)];
                const randomEmojis = this.emojiCategories[randomCategory];
                newEmoji = randomEmojis[Math.floor(Math.random() * randomEmojis.length)];
            } else {
                // Use similar emojis from same category
                const categoryEmojis = this.emojiCategories[emojiData.category] || this.emojiCategories.random;
                emojiData.currentIndex = (emojiData.currentIndex + 1) % categoryEmojis.length;
                newEmoji = categoryEmojis[emojiData.currentIndex];
            }
            
            // Replace the emoji in the text
            newText = newText.replace(emojiData.original, newEmoji);
            emojiData.original = newEmoji; // Update for next cycle
        });
        
        // Update element text content
        if (cycleData.element && cycleData.element.textContent !== undefined) {
            cycleData.element.textContent = newText;
        }
    }
    
    // Safe emoji cycling for specific elements only
    startSpecificEmojiCycling(element) {
        if (!element) return;
        
        let index = 0;
        
        // Simple cycling through mining emojis every 3 seconds
        const cycleInterval = setInterval(() => {
            index = (index + 1) % this.miningEmojis.length;
            element.textContent = this.miningEmojis[index];
        }, 3000); // 3 second intervals
        
        // Store interval for potential cleanup
        element._emojiCycleInterval = cycleInterval;
    }
}

// Initialize global miner when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.haichanMiner = new HaichanMiner();
});