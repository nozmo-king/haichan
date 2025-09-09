// Simple Haichan Mining System v2.0 - 2025-09-09

class SimpleMiner {
    constructor() {
        this.isActive = false;
        this.hashCount = 0;
        this.startTime = 0;
        this.currentHash = '';
        this.pattern = '21e8';
        this.nonce = 0;
        this.mode = 'idle'; // idle, active, hyper
        this.targetType = 'global';
        this.targetId = 'haichan';
        
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
        // Detect what we should mine based on current page
        const path = window.location.pathname;
        
        if (path.match(/^\/(\w+)\/(\d+)$/)) {
            // Thread page: /gen/123
            const matches = path.match(/^\/(\w+)\/(\d+)$/);
            this.targetType = 'thread';
            this.targetId = matches[2];
        } else if (path.match(/^\/forum\/board\/(\w+)\/thread\/(\d+)/)) {
            // Alternative thread page: /forum/board/gen/thread/123
            const matches = path.match(/^\/forum\/board\/(\w+)\/thread\/(\d+)/);
            this.targetType = 'thread';
            this.targetId = matches[2];
        } else if (path.match(/^\/(\w+)\/catalog$/)) {
            // Board catalog page: /gen/catalog
            const matches = path.match(/^\/(\w+)\/catalog$/);
            this.targetType = 'board';
            this.targetId = matches[1];
        } else if (path.match(/^\/(\w+)\/?$/)) {
            // Board page: /gen/
            const matches = path.match(/^\/(\w+)\/?$/);
            this.targetType = 'board';
            this.targetId = matches[1];
        } else {
            // Global/other pages
            this.targetType = 'global';
            this.targetId = 'haichan';
        }
    }
    
    autoStart() {
        // Auto-start mining in idle mode after page loads
        setTimeout(() => {
            this.start();
        }, 1000);
    }
    
    start() {
        if (this.isActive) return;
        
        this.isActive = true;
        this.startTime = Date.now();
        this.hashCount = 0;
        
        // Start mining loop
        this.mineLoop();
        
        // Update stats every second
        this.statsInterval = setInterval(() => {
            this.updateStats();
        }, 1000);
        
        this.updateUI();
    }
    
    stop() {
        this.isActive = false;
        if (this.miningTimeout) clearTimeout(this.miningTimeout);
        if (this.statsInterval) clearInterval(this.statsInterval);
        this.updateUI();
    }
    
    mineLoop() {
        if (!this.isActive) return;
        
        // Calculate batch size based on mode
        let batchSize = this.mode === 'idle' ? 10 : this.mode === 'active' ? 50 : 150;
        
        for (let i = 0; i < batchSize; i++) {
            this.mineStep();
        }
        
        // Schedule next batch
        let delay = this.mode === 'idle' ? 100 : this.mode === 'active' ? 50 : 10;
        this.miningTimeout = setTimeout(() => this.mineLoop(), delay);
    }
    
    mineStep() {
        const data = `${this.targetType}:${this.targetId}:${Date.now()}:${this.nonce}`;
        const hash = this.simpleHash(data);
        
        this.hashCount++;
        this.currentHash = hash;
        this.nonce++;
        
        // Check if hash matches pattern
        if (hash.toLowerCase().startsWith(this.pattern.toLowerCase())) {
            this.foundProof(hash);
        }
    }
    
    simpleHash(data) {
        // Simple hash function for demo - in production use crypto.subtle
        let hash = 0;
        for (let i = 0; i < data.length; i++) {
            const char = data.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        
        // Create a more realistic looking hash by combining multiple hash parts
        let hexHash = '';
        let seed = Math.abs(hash);
        
        // Generate 64 character hex hash that looks more natural
        for (let i = 0; i < 16; i++) {
            // Create varied hex digits instead of just leading zeros
            seed = (seed * 1103515245 + 12345) & 0x7fffffff;
            const hex4 = (seed % 65536).toString(16).padStart(4, '0');
            hexHash += hex4;
        }
        
        return hexHash.substring(0, 64);
    }
    
    foundProof(hash) {
        console.log(`💎 PROOF FOUND! ${hash.substring(0, 16)}...`);
        
        // Submit proof to server
        this.submitProof(hash);
        
        // Visual celebration
        this.celebrate();
        
        // Update thread PoW total if mining on a thread
        this.updateThreadPoW();
    }
    
    async submitProof(hash) {
        try {
            const response = await fetch('/api/submit-proof', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({
                    hash: hash,
                    nonce: this.nonce,
                    data: `${this.targetType}:${this.targetId}:${Date.now()}`,
                    pattern: this.pattern,
                    target_type: this.targetType,
                    target_id: this.targetId
                })
            });
            
            const result = await response.json();
            if (result.success) {
                console.log('✅ Proof accepted!');
            } else {
                console.log('❌ Proof rejected:', result.message);
            }
        } catch (error) {
            console.log('❌ Submit error:', error);
        }
    }
    
    updateThreadPoW() {
        // If we're mining on a thread page, increment the thread's PoW total
        if (this.targetType === 'thread') {
            const powDisplay = document.querySelector('[data-thread-pow]');
            if (powDisplay) {
                const currentPow = parseInt(powDisplay.textContent) || 0;
                powDisplay.textContent = (currentPow + 1).toString();
                
                // Flash the display
                powDisplay.style.color = '#00FF00';
                setTimeout(() => {
                    powDisplay.style.color = '';
                }, 1000);
            }
        }
    }
    
    celebrate() {
        // Flash the mini dashboard
        const dashboard = document.getElementById('simple-mini-dashboard');
        if (dashboard) {
            dashboard.style.background = '#90EE90';
            setTimeout(() => {
                dashboard.style.background = '#FFFBF7';
            }, 500);
        }
    }
    
    setMode(newMode) {
        console.log(`🎯 Setting mining mode to: ${newMode}`);
        this.mode = newMode;
        this.updateUI();
    }
    
    updateStats() {
        if (!this.isActive) return;
        
        const elapsed = (Date.now() - this.startTime) / 1000;
        const hashRate = Math.floor(this.hashCount / elapsed);
        
        // Update mini dashboard
        const modeEl = document.getElementById('simple-mode');
        const rateEl = document.getElementById('simple-rate');
        const hashEl = document.getElementById('simple-hash');
        const targetEl = document.getElementById('simple-target');
        
        if (modeEl) modeEl.textContent = this.mode.toUpperCase();
        if (rateEl) rateEl.textContent = `${hashRate} H/s`;
        if (hashEl) hashEl.textContent = this.currentHash.substring(0, 20) + '...';
        if (targetEl) {
            let targetText = '';
            if (this.targetType === 'thread') {
                targetText = `🧵 Thread #${this.targetId}`;
            } else if (this.targetType === 'board') {
                targetText = `📋 Board /${this.targetId}/`;
            } else {
                targetText = `🌐 Global Mining`;
            }
            targetEl.textContent = targetText;
        }
        
        // Update status bar
        const statusRate = document.getElementById('network-hashrate');
        const statusHashes = document.getElementById('network-total-hashes');
        
        if (statusRate) statusRate.textContent = `${hashRate} H/s`;
        if (statusHashes) statusHashes.textContent = this.hashCount.toLocaleString();
    }
    
    updateUI() {
        // Update button states
        document.querySelectorAll('.simple-mode-btn').forEach(btn => {
            btn.style.background = '#F0F0F0';
        });
        
        const activeBtn = document.getElementById(`simple-${this.mode}-btn`);
        if (activeBtn) {
            activeBtn.style.background = '#D0D0D0';
        }
        
        // Update mining indicator
        const indicator = document.getElementById('mining-indicator');
        if (indicator) {
            indicator.style.background = this.isActive ? '#00FF00' : '#FF0000';
        }
    }
    
    setupStatusBar() {
        // The status bar is already in layout.blade.php
    }
    
    setupHoverMining() {
        
        // Store the default target
        this.defaultTargetType = this.targetType;
        this.defaultTargetId = this.targetId;
        this.isHovering = false;
        
        // Set up hover listeners after page loads
        document.addEventListener('DOMContentLoaded', () => {
            this.setupHoverListeners();
        });
        
        // Also set up listeners immediately if DOM is already ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupHoverListeners());
        } else {
            this.setupHoverListeners();
        }
    }
    
    setupHoverListeners() {
        // Thread previews (on board pages)
        document.querySelectorAll('.thread-preview').forEach(preview => {
            const threadId = preview.dataset.threadId;
            const threadTitle = preview.dataset.threadTitle;
            
            if (threadId) {
                preview.addEventListener('mouseenter', () => {
                    this.switchToHoverTarget('thread', threadId, `Thread #${threadId}: ${threadTitle || 'No Subject'}`);
                });
                
                preview.addEventListener('mouseleave', () => {
                    this.switchBackToDefault();
                });
            }
        });
        
        // Posts (on thread pages)
        document.querySelectorAll('.post').forEach(post => {
            const postId = post.id?.replace('post', '');
            const threadId = post.dataset.threadId;
            
            if (postId && threadId) {
                post.addEventListener('mouseenter', () => {
                    if (post.classList.contains('op-post')) {
                        this.switchToHoverTarget('thread', threadId, `Thread #${threadId}`);
                    } else {
                        this.switchToHoverTarget('reply', postId, `Reply #${postId}`);
                    }
                });
                
                post.addEventListener('mouseleave', () => {
                    this.switchBackToDefault();
                });
            }
        });
        
        // Catalog threads
        document.querySelectorAll('.catalog-thread').forEach(thread => {
            const threadId = thread.dataset.threadId;
            const threadTitle = thread.dataset.threadTitle;
            
            if (threadId) {
                thread.addEventListener('mouseenter', () => {
                    this.switchToHoverTarget('thread', threadId, `Thread #${threadId}: ${threadTitle || 'No Subject'}`);
                });
                
                thread.addEventListener('mouseleave', () => {
                    this.switchBackToDefault();
                });
            }
        });
    }
    
    switchToHoverTarget(type, id, displayName) {
        this.isHovering = true;
        this.targetType = type;
        this.targetId = id;
        console.log(`👆 Hover mining: ${displayName}`);
        this.updateTargetDisplay(displayName);
    }
    
    switchBackToDefault() {
        this.isHovering = false;
        this.targetType = this.defaultTargetType;
        this.targetId = this.defaultTargetId;
        console.log(`↩️ Back to default mining: ${this.getDisplayName()}`);
        this.updateTargetDisplay();
    }
    
    getDisplayName() {
        if (this.targetType === 'thread') {
            return `🧵 Thread #${this.targetId}`;
        } else if (this.targetType === 'reply') {
            return `💬 Reply #${this.targetId}`;
        } else if (this.targetType === 'board') {
            return `📋 Board /${this.targetId}/`;
        } else {
            return `🌐 Global Mining`;
        }
    }
    
    updateTargetDisplay(customName = null) {
        const targetEl = document.getElementById('simple-target');
        if (targetEl) {
            targetEl.textContent = customName || this.getDisplayName();
            
            // Add visual indication when hovering
            if (this.isHovering) {
                targetEl.style.background = '#E8F5E8';
                targetEl.style.borderColor = '#9AB87A';
            } else {
                targetEl.style.background = '#F8F8F8';
                targetEl.style.borderColor = '#CCCCCC';
            }
        }
    }
    
    setupMiniDashboard() {
        
        // Remove old dashboard if exists
        const oldDash = document.getElementById('mini-dashboard-overlay');
        if (oldDash) oldDash.remove();
        
        // Create new simple dashboard
        const dashboard = document.createElement('div');
        dashboard.id = 'simple-mini-dashboard';
        dashboard.style.cssText = `
            position: fixed;
            top: 60px;
            right: 20px;
            width: 350px;
            background: #FFFBF7;
            border: 2px solid #444B6E;
            padding: 0;
            z-index: 9998;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            color: #444B6E;
            box-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            display: none;
        `;
        
        dashboard.innerHTML = `
            <div style="background: #E8E8E8; padding: 8px 12px; border-bottom: 1px solid #AAAAAA; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-weight: bold; font-size: 10px;">⛏️ HAICHAN MINING DASHBOARD</span>
                <button id="simple-close" style="background: #DDDDDD; border: 1px solid #999999; color: #333333; padding: 1px 4px; font-size: 10px; cursor: pointer; font-weight: bold; width: 16px; height: 16px;">×</button>
            </div>
            
            <div style="padding: 12px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                    <div>
                        <div style="color: #666666; font-size: 9px; margin-bottom: 4px;">Current Mode</div>
                        <div id="simple-mode" style="color: #339966; font-weight: bold; font-size: 11px;">IDLE</div>
                    </div>
                    <div>
                        <div style="color: #666666; font-size: 9px; margin-bottom: 4px;">Hash Rate</div>
                        <div id="simple-rate" style="color: #339966; font-weight: bold; font-size: 11px;">0 H/s</div>
                    </div>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <div style="color: #666666; font-size: 9px; margin-bottom: 5px;">Mining Controls</div>
                    <div style="display: flex; gap: 3px;">
                        <button id="simple-idle-btn" class="simple-mode-btn" style="background: #F0F0F0; border: 1px solid #AAAAAA; padding: 4px 8px; font-size: 9px; cursor: pointer; color: #333333;">IDLE</button>
                        <button id="simple-active-btn" class="simple-mode-btn" style="background: #F0F0F0; border: 1px solid #AAAAAA; padding: 4px 8px; font-size: 9px; cursor: pointer; color: #333333;">ACTIVE</button>
                        <button id="simple-hyper-btn" class="simple-mode-btn" style="background: #F0F0F0; border: 1px solid #AAAAAA; padding: 4px 8px; font-size: 9px; cursor: pointer; color: #333333;">HYPER</button>
                        <button id="simple-stop-btn" style="background: #F0F0F0; border: 1px solid #AAAAAA; padding: 4px 8px; font-size: 9px; cursor: pointer; color: #333333;">STOP</button>
                    </div>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <div style="color: #666666; font-size: 9px; margin-bottom: 3px;">Mining Target</div>
                    <div id="simple-target" style="font-size: 9px; color: #333333; background: #F8F8F8; padding: 4px 6px; border: 1px solid #CCCCCC; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">detecting...</div>
                </div>
                
                <div style="margin-bottom: 12px;">
                    <div style="color: #666666; font-size: 9px; margin-bottom: 3px;">Current Hash</div>
                    <div id="simple-hash" style="font-family: 'Courier New', monospace; font-size: 8px; color: #333333; background: #F8F8F8; padding: 4px 6px; border: 1px solid #CCCCCC; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">waiting...</div>
                </div>
                
                <div style="border-top: 1px solid #CCCCCC; padding-top: 8px; text-align: center;">
                    <a href="/mining" style="color: #0066CC; text-decoration: underline; font-size: 10px;">🎯 Open Full Dashboard</a>
                </div>
            </div>
        `;
        
        document.body.appendChild(dashboard);
        
        // Add event listeners
        document.getElementById('simple-close').addEventListener('click', () => {
            dashboard.style.display = 'none';
        });
        
        document.getElementById('simple-idle-btn').addEventListener('click', () => this.setMode('idle'));
        document.getElementById('simple-active-btn').addEventListener('click', () => this.setMode('active'));
        document.getElementById('simple-hyper-btn').addEventListener('click', () => this.setMode('hyper'));
        document.getElementById('simple-stop-btn').addEventListener('click', () => this.stop());
        
        // Show dashboard by default (user can hide it if they want)
        dashboard.style.display = 'block';
        
        // Add toggle button to status bar
        const toggleBtn = document.getElementById('mini-dash-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
            });
        }
        
        // Ctrl+D shortcut
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'd') {
                e.preventDefault();
                dashboard.style.display = dashboard.style.display === 'none' ? 'block' : 'none';
            }
        });
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 Initializing Simple Haichan Miner...');
    window.simpleMiner = new SimpleMiner();
});