/**
 * Enhanced Interactive Mining Dashboard
 * Features: Draggable, power slider (0-10), prefix menu, real-time stats
 */

class EnhancedMiningDashboard {
    constructor() {
        this.isVisible = false;
        this.isMinimized = false;
        this.isDragging = false;
        this.dragOffset = { x: 0, y: 0 };
        
        this.powerLevel = 5; // Default power level (0-10)
        this.currentPrefix = '21e8'; // Default difficulty prefix (current active)
        this.isActive = false;
        
        this.stats = {
            sessionHashes: 0,
            sessionProofs: 0,
            sessionPoints: 0,
            currentHashrate: 0,
            currentTarget: 'None',
            recentProofs: []
        };
        
        this.init();
    }
    
    init() {
        this.createDashboard();
        this.setupEventListeners();
        this.setupDragging();
        this.startStatsUpdate();
        console.log('✅ Enhanced Mining Dashboard initialized');
    }
    
    createDashboard() {
        // Create dashboard HTML
        const dashboardHTML = `
            <div id="enhanced-mining-dashboard">
                <div class="dashboard-header">
                    <span class="dashboard-title">⛏️ HAICHAN MINER v2.0</span>
                    <div class="dashboard-controls">
                        <button class="dashboard-btn auto-mine-btn" id="auto-mine-toggle" title="Toggle Auto Mining">⚡</button>
                        <button class="dashboard-btn minimize-btn" title="Minimize">−</button>
                        <button class="dashboard-btn close-btn" title="Close">×</button>
                    </div>
                </div>
                <div class="dashboard-content">
                    <!-- Mining Mode Selector -->
                    <div class="mining-mode-section">
                        <h4>🚀 Mining Mode</h4>
                        <div class="mode-buttons">
                            <button class="mode-btn active" data-mode="hover">🖱️ Hover Mine</button>
                            <button class="mode-btn" data-mode="auto">🤖 Auto Mine</button>
                            <button class="mode-btn" data-mode="manual">👆 Manual</button>
                        </div>
                    </div>
                    
                    <!-- Power Control -->
                    <div class="power-control">
                        <h4>⚡ Mining Power</h4>
                        <div class="power-slider-container">
                            <input type="range" class="power-slider enhanced" id="power-slider" 
                                   min="0" max="10" value="5" step="1">
                            <div class="power-display enhanced" id="power-display">
                                <span class="power-number">5</span>
                                <span class="power-label">/10</span>
                            </div>
                        </div>
                        <div class="power-description" id="power-description">
                            Moderate mining intensity
                        </div>
                        <div class="mining-status enhanced">
                            <span class="status-indicator" id="status-indicator"></span>
                            <span class="status-text" id="status-text">Standby</span>
                            <button class="manual-mine-btn" id="manual-mine-btn" title="Mine Now!">⚡ MINE</button>
                        </div>
                    </div>
                    
                    <!-- Prefix Selection -->
                    <div class="prefix-control enhanced">
                        <h4>🎯 Difficulty Target</h4>
                        <select class="prefix-selector enhanced" id="prefix-selector">
                            <option value="2">💚 2 (Easy - 1:16)</option>
                            <option value="21">💛 21 (Medium - 1:256)</option>
                            <option value="21e">21e (Hard - 1:4K)</option>
                            <option value="21e8" selected>21e8 (Very Hard - 1:65K) [CURRENT]</option>
                            <option value="21e88">🟣 21e88 (Extreme - 1:1M)</option>
                            <option value="21e888">⚫ 21e888 (Insane - 1:16M)</option>
                        </select>
                        <div class="difficulty-info" id="difficulty-info">
                            Current: Very Hard difficulty (1 in 65,536 chance)
                        </div>
                    </div>
                    
                    <!-- Real-time Stats -->
                    <div class="stats-section enhanced">
                        <h4>📊 Live Statistics</h4>
                        <div class="stats-grid enhanced">
                            <div class="stat-item">
                                <div class="stat-icon">🧮</div>
                                <div class="stat-details">
                                    <span class="stat-value" id="stat-hashes">0</span>
                                    <span class="stat-label">HASHES</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">💎</div>
                                <div class="stat-details">
                                    <span class="stat-value" id="stat-proofs">0</span>
                                    <span class="stat-label">PROOFS</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">⚡</div>
                                <div class="stat-details">
                                    <span class="stat-value" id="stat-points">0</span>
                                    <span class="stat-label">POINTS</span>
                                </div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-icon">🚀</div>
                                <div class="stat-details">
                                    <span class="stat-value" id="stat-hashrate">0</span>
                                    <span class="stat-label">H/S</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Target Display -->
                    <div class="target-section enhanced">
                        <h4>🎯 Current Target</h4>
                        <div class="target-display enhanced" id="target-display">
                            <div class="target-icon">🔍</div>
                            <div class="target-info">
                                <div class="target-name" id="current-target">None</div>
                                <div class="target-type" id="target-type">Hover over content to mine</div>
                            </div>
                        </div>
                        <div class="mining-progress" id="mining-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill" id="progress-fill"></div>
                            </div>
                            <div class="progress-text" id="progress-text">Mining...</div>
                        </div>
                    </div>
                    
                    <!-- Recent Proofs -->
                    <div class="recent-proofs-section enhanced">
                        <h4>🏆 Recent Discoveries</h4>
                        <div class="recent-proofs" id="recent-proofs">
                            <div class="no-proofs">
                                <div class="no-proofs-icon">💎</div>
                                <div class="no-proofs-text">No proofs found yet...</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advanced Settings -->
                    <details class="advanced-section">
                        <summary>⚙️ Advanced Settings</summary>
                        <div class="advanced-content">
                            <div class="setting-item">
                                <label class="setting-label">
                                    <input type="checkbox" id="sound-enabled" checked>
                                    <span class="checkmark"></span>
                                    Sound notifications
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="setting-label">
                                    <input type="checkbox" id="animation-enabled" checked>
                                    <span class="checkmark"></span>
                                    Visual animations
                                </label>
                            </div>
                            <div class="setting-item">
                                <label class="setting-label">Batch Size:</label>
                                <input type="range" id="batch-size" min="100" max="2000" value="1000" step="100">
                                <span id="batch-value">1000</span>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
            
            <!-- Floating Toggle Button -->
            <button class="dashboard-float-btn" id="dashboard-toggle" title="Toggle Mining Dashboard">
                <span class="float-icon">⛏️</span>
                <span class="float-text">MINER</span>
            </button>
        `;
        
        // Add to DOM
        document.body.insertAdjacentHTML('beforeend', dashboardHTML);
        
        // Add CSS inline for better performance
        this.addDashboardStyles();
        
        // Load external CSS too
        const cssLink = document.createElement('link');
        cssLink.rel = 'stylesheet';
        cssLink.href = '/css/enhanced-dashboard.css';
        document.head.appendChild(cssLink);
    }
    
    setupEventListeners() {
        const dashboard = document.getElementById('enhanced-mining-dashboard');
        const toggleBtn = document.getElementById('dashboard-toggle');
        const minimizeBtn = dashboard.querySelector('.minimize-btn');
        const closeBtn = dashboard.querySelector('.close-btn');
        const powerSlider = document.getElementById('power-slider');
        const prefixSelector = document.getElementById('prefix-selector');
        
        // Toggle visibility
        toggleBtn.addEventListener('click', () => this.toggle());
        
        // Minimize/restore
        minimizeBtn.addEventListener('click', () => this.minimize());
        
        // Close dashboard
        closeBtn.addEventListener('click', () => this.hide());
        
        // Power slider
        powerSlider.addEventListener('input', (e) => {
            this.powerLevel = parseInt(e.target.value);
            this.updatePowerDisplay();
            this.updateMiningStatus();
        });
        
        // Prefix selector
        prefixSelector.addEventListener('change', (e) => {
            this.currentPrefix = e.target.value;
            console.log(`🎯 Difficulty changed to: ${this.currentPrefix}`);
            this.updateTargetDisplay();
        });
        
        // Auto-mining toggle
        const autoMineBtn = dashboard.querySelector('#auto-mine-toggle');
        autoMineBtn?.addEventListener('click', () => this.toggleAutoMining());
        
        // Manual mine button
        const manualMineBtn = dashboard.querySelector('#manual-mine-btn');
        manualMineBtn?.addEventListener('click', () => this.performManualMining());
    }
    
    setupDragging() {
        const dashboard = document.getElementById('enhanced-mining-dashboard');
        const header = dashboard.querySelector('.dashboard-header');
        
        header.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('dashboard-btn')) return;
            
            this.isDragging = true;
            const rect = dashboard.getBoundingClientRect();
            this.dragOffset = {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
            
            dashboard.classList.add('dashboard-dragging');
            document.body.style.userSelect = 'none';
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;
            
            const x = e.clientX - this.dragOffset.x;
            const y = e.clientY - this.dragOffset.y;
            
            // Keep within viewport bounds
            const maxX = window.innerWidth - dashboard.offsetWidth;
            const maxY = window.innerHeight - dashboard.offsetHeight;
            
            dashboard.style.left = Math.max(0, Math.min(x, maxX)) + 'px';
            dashboard.style.top = Math.max(0, Math.min(y, maxY)) + 'px';
            dashboard.style.right = 'auto'; // Remove right positioning
        });
        
        document.addEventListener('mouseup', () => {
            if (this.isDragging) {
                this.isDragging = false;
                dashboard.classList.remove('dashboard-dragging');
                document.body.style.userSelect = '';
            }
        });
    }
    
    show() {
        const dashboard = document.getElementById('enhanced-mining-dashboard');
        dashboard.style.display = 'block';
        this.isVisible = true;
        
        // Save state
        localStorage.setItem('mining-dashboard-visible', 'true');
    }
    
    hide() {
        const dashboard = document.getElementById('enhanced-mining-dashboard');
        dashboard.style.display = 'none';
        this.isVisible = false;
        
        // Save state
        localStorage.setItem('mining-dashboard-visible', 'false');
    }
    
    toggle() {
        if (this.isVisible) {
            this.hide();
        } else {
            this.show();
        }
    }
    
    minimize() {
        const dashboard = document.getElementById('enhanced-mining-dashboard');
        const content = dashboard.querySelector('.dashboard-content');
        const minimizeBtn = dashboard.querySelector('.minimize-btn');
        
        if (this.isMinimized) {
            // Restore
            content.style.display = 'block';
            minimizeBtn.textContent = '−';
            minimizeBtn.title = 'Minimize';
            this.isMinimized = false;
        } else {
            // Minimize
            content.style.display = 'none';
            minimizeBtn.textContent = '+';
            minimizeBtn.title = 'Restore';
            this.isMinimized = true;
        }
    }
    
    updatePowerDisplay() {
        const powerNumber = document.querySelector('.power-number');
        const powerDescription = document.getElementById('power-description');
        
        if (powerNumber) powerNumber.textContent = this.powerLevel;
        
        // Update power description
        const descriptions = {
            0: 'Mining disabled',
            1: 'Minimal mining intensity',
            2: 'Low mining intensity', 
            3: 'Light mining intensity',
            4: 'Below average intensity',
            5: 'Moderate mining intensity',
            6: 'Above average intensity',
            7: 'High mining intensity',
            8: 'Very high intensity',
            9: 'Extreme mining intensity',
            10: 'MAXIMUM POWER! 🔥'
        };
        
        if (powerDescription) {
            powerDescription.textContent = descriptions[this.powerLevel] || 'Unknown';
        }
        
        // Update slider background
        const slider = document.getElementById('power-slider');
        const percentage = (this.powerLevel / 10) * 100;
        slider.style.background = `linear-gradient(to right, #00d4ff 0%, #00d4ff ${percentage}%, rgba(255,255,255,0.1) ${percentage}%, rgba(255,255,255,0.1) 100%)`;
    }
    
    updateMiningStatus() {
        const indicator = document.getElementById('status-indicator');
        const statusText = document.getElementById('status-text');
        
        if (this.powerLevel > 0) {
            indicator.className = 'status-indicator active';
            statusText.textContent = `Active (Power: ${this.powerLevel}/10)`;
            this.isActive = true;
        } else {
            indicator.className = 'status-indicator inactive';
            statusText.textContent = 'Standby';
            this.isActive = false;
        }
    }
    
    startStatsUpdate() {
        setInterval(() => {
            this.updateStats();
        }, 1000);
    }
    
    updateStats() {
        // Update display elements
        document.getElementById('stat-hashes').textContent = this.stats.sessionHashes.toLocaleString();
        document.getElementById('stat-proofs').textContent = this.stats.sessionProofs;
        document.getElementById('stat-points').textContent = this.stats.sessionPoints;
        document.getElementById('stat-hashrate').textContent = this.stats.currentHashrate;
        document.getElementById('current-target').textContent = this.stats.currentTarget;
        document.getElementById('effective-rate').textContent = `${this.stats.currentHashrate} H/s`;
        
        // Update recent proofs
        this.updateRecentProofs();
    }
    
    updateRecentProofs() {
        const container = document.getElementById('recent-proofs');
        
        if (this.stats.recentProofs.length === 0) {
            container.innerHTML = `
                <div class="no-proofs">
                    <div class="no-proofs-icon">💎</div>
                    <div class="no-proofs-text">No proofs found yet...</div>
                </div>
            `;
            return;
        }
        
        const proofsHTML = this.stats.recentProofs.slice(-5).reverse().map((proof, index) => {
            const timeAgo = Math.floor((Date.now() - proof.timestamp) / 1000);
            const rarityColor = this.getRarityColor(proof.rarity);
            
            return `
                <div class="proof-item enhanced" style="animation-delay: ${index * 0.1}s">
                    <div class="proof-header">
                        <span class="proof-hash" style="color: ${rarityColor}">
                            ${proof.hash.substring(0, 8)}...
                        </span>
                        <span class="proof-time">${timeAgo}s ago</span>
                    </div>
                    <div class="proof-footer">
                        <span class="proof-rarity">${proof.rarity}</span>
                        <span class="proof-points">+${proof.points}</span>
                    </div>
                </div>
            `;
        }).join('');
        
        container.innerHTML = proofsHTML;
    }
    
    calculateRarity(hash) {
        if (hash.startsWith('deadbeef')) return 'LEGENDARY';
        if (hash.startsWith('000')) return 'RARE';
        if (hash.startsWith('777')) return 'LUCKY';
        if (hash.startsWith('666')) return 'CURSED';
        if (hash.startsWith('21e888')) return 'INSANE';
        if (hash.startsWith('21e88')) return 'EXTREME';
        if (hash.startsWith('21e8')) return 'HARD';
        if (hash.startsWith('21e')) return 'MEDIUM';
        return 'COMMON';
    }
    
    getRarityColor(rarity) {
        const colors = {
            'LEGENDARY': '#FFD700',
            'RARE': '#00FFFF',
            'LUCKY': '#00FF00',
            'CURSED': '#FF0000',
            'INSANE': '#FF69B4',
            'EXTREME': '#FF6B35',
            'HARD': '#00D4FF',
            'MEDIUM': '#FFA500',
            'COMMON': '#FFFFFF'
        };
        return colors[rarity] || '#FFFFFF';
    }
    
    playProofSound() {
        if (document.getElementById('sound-enabled')?.checked) {
            // Create a short beep sound using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.type = 'sine';
                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
                
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (e) {
                console.log('Audio not available');
            }
        }
    }
    
    updateTargetDisplay() {
        const difficultyInfo = document.getElementById('difficulty-info');
        const difficultyMap = {
            '2': '💚 Easy difficulty (1 in 16 chance)',
            '21': '💛 Medium difficulty (1 in 256 chance)',
            '21e': '🟠 Hard difficulty (1 in 4,096 chance)',
            '21e8': '🔴 Very Hard difficulty (1 in 65,536 chance)',
            '21e88': '🟣 Extreme difficulty (1 in 1,048,576 chance)',
            '21e888': '⚫ Insane difficulty (1 in 16,777,216 chance)'
        };
        
        if (difficultyInfo) {
            difficultyInfo.textContent = `Current: ${difficultyMap[this.currentPrefix] || 'Unknown difficulty'}`;
        }
    }
    
    toggleAutoMining() {
        // Toggle auto mining mode
        this.autoMining = !this.autoMining;
        const btn = document.getElementById('auto-mine-toggle');
        
        if (this.autoMining) {
            btn.style.background = '#00ff00';
            btn.title = 'Auto Mining: ON';
            this.startAutoMining();
        } else {
            btn.style.background = '';
            btn.title = 'Auto Mining: OFF';
            this.stopAutoMining();
        }
    }
    
    performManualMining() {
        const btn = document.getElementById('manual-mine-btn');
        btn.textContent = '⚡ MINING...';
        btn.disabled = true;
        
        // Simulate mining process
        setTimeout(() => {
            const hash = this.generateRandomHash();
            if (hash.startsWith(this.currentPrefix.toLowerCase())) {
                this.addProof(hash, this.calculatePoints(hash));
                btn.textContent = '💎 FOUND!';
            } else {
                btn.textContent = '❌ NO PROOF';
            }
            
            setTimeout(() => {
                btn.textContent = '⚡ MINE';
                btn.disabled = false;
            }, 1000);
        }, 2000);
    }
    
    generateRandomHash() {
        const chars = '0123456789abcdef';
        let result = '';
        for (let i = 0; i < 64; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }
    
    calculatePoints(hash) {
        if (hash.startsWith('deadbeef')) return 10000;
        if (hash.startsWith('000')) return 5000;
        if (hash.startsWith('777')) return 777;
        if (hash.startsWith('666')) return 666;
        if (hash.startsWith('21e888')) return 2500;
        if (hash.startsWith('21e88')) return 500;
        if (hash.startsWith('21e8')) return 100;
        if (hash.startsWith('21e')) return 10;
        if (hash.startsWith('21')) return 2;
        if (hash.startsWith('2')) return 1;
        return 0.1;
    }
    
    addDashboardStyles() {
        const style = document.createElement('style');
        style.id = 'enhanced-dashboard-inline';
        style.textContent = `
            #enhanced-mining-dashboard {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 320px;
                max-height: 90vh;
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
                border: 2px solid #00d4ff;
                border-radius: 12px;
                font-family: 'Courier New', monospace;
                font-size: 11px;
                color: #ffffff;
                z-index: 10000;
                box-shadow: 0 8px 32px rgba(0, 212, 255, 0.3), 0 0 20px rgba(0, 212, 255, 0.1);
                backdrop-filter: blur(10px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden;
            }
            
            .dashboard-header {
                background: linear-gradient(90deg, #00d4ff 0%, #0099cc 100%);
                color: #1a1a2e;
                padding: 12px 16px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 10px 10px 0 0;
            }
            
            .dashboard-title {
                font-weight: bold;
                font-size: 12px;
                letter-spacing: 1px;
            }
            
            .dashboard-controls {
                display: flex;
                gap: 6px;
            }
            
            .dashboard-btn {
                background: rgba(255,255,255,0.2);
                border: 1px solid rgba(255,255,255,0.3);
                color: #1a1a2e;
                padding: 4px 8px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 10px;
                font-weight: bold;
                transition: all 0.2s ease;
            }
            
            .dashboard-btn:hover {
                background: rgba(255,255,255,0.3);
                transform: scale(1.05);
            }
            
            .dashboard-content {
                padding: 16px;
                max-height: 80vh;
                overflow-y: auto;
                scrollbar-width: thin;
                scrollbar-color: #00d4ff #1a1a2e;
            }
            
            .dashboard-content::-webkit-scrollbar {
                width: 4px;
            }
            
            .dashboard-content::-webkit-scrollbar-track {
                background: #1a1a2e;
            }
            
            .dashboard-content::-webkit-scrollbar-thumb {
                background: #00d4ff;
                border-radius: 2px;
            }
            
            .mining-mode-section h4,
            .power-control h4,
            .prefix-control h4,
            .stats-section h4,
            .target-section h4,
            .recent-proofs-section h4 {
                margin: 0 0 8px 0;
                color: #00d4ff;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            
            .mode-buttons {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                gap: 4px;
                margin-bottom: 12px;
            }
            
            .mode-btn {
                background: rgba(0, 212, 255, 0.1);
                border: 1px solid rgba(0, 212, 255, 0.3);
                color: #00d4ff;
                padding: 6px 4px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 9px;
                font-weight: bold;
                transition: all 0.2s ease;
                text-align: center;
            }
            
            .mode-btn:hover {
                background: rgba(0, 212, 255, 0.2);
                transform: translateY(-1px);
            }
            
            .mode-btn.active {
                background: #00d4ff;
                color: #1a1a2e;
                box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
            }
            
            .power-slider-container {
                position: relative;
                margin: 8px 0;
                padding: 8px;
                background: rgba(0, 212, 255, 0.05);
                border-radius: 6px;
                border: 1px solid rgba(0, 212, 255, 0.2);
            }
            
            .power-slider.enhanced {
                width: 100%;
                height: 6px;
                background: rgba(255,255,255,0.1);
                outline: none;
                border-radius: 3px;
                appearance: none;
            }
            
            .power-slider.enhanced::-webkit-slider-thumb {
                appearance: none;
                width: 16px;
                height: 16px;
                background: linear-gradient(135deg, #00d4ff, #0099cc);
                border-radius: 50%;
                cursor: pointer;
                box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
            }
            
            .power-display.enhanced {
                text-align: center;
                margin-top: 6px;
            }
            
            .power-number {
                font-size: 16px;
                font-weight: bold;
                color: #00d4ff;
            }
            
            .power-label {
                font-size: 10px;
                color: rgba(255,255,255,0.7);
            }
            
            .power-description {
                text-align: center;
                font-size: 9px;
                color: rgba(255,255,255,0.6);
                margin-top: 4px;
            }
            
            .mining-status.enhanced {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 8px;
                padding: 6px;
                background: rgba(0,0,0,0.2);
                border-radius: 4px;
            }
            
            .status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #666;
                animation: pulse 2s infinite;
            }
            
            .status-indicator.active { background: #00ff00; }
            .status-indicator.mining { background: #00d4ff; }
            .status-indicator.inactive { background: #666; }
            
            .manual-mine-btn {
                background: linear-gradient(135deg, #ff6b35, #ff8c42);
                border: none;
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 9px;
                font-weight: bold;
                transition: all 0.2s ease;
            }
            
            .manual-mine-btn:hover {
                transform: scale(1.05);
                box-shadow: 0 0 10px rgba(255, 107, 53, 0.5);
            }
            
            .stats-grid.enhanced {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                margin-bottom: 12px;
            }
            
            .stat-item {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 8px;
                background: rgba(0, 212, 255, 0.05);
                border: 1px solid rgba(0, 212, 255, 0.2);
                border-radius: 6px;
                transition: all 0.2s ease;
            }
            
            .stat-item:hover {
                background: rgba(0, 212, 255, 0.1);
                transform: translateY(-1px);
            }
            
            .stat-icon {
                font-size: 14px;
            }
            
            .stat-details {
                display: flex;
                flex-direction: column;
            }
            
            .stat-value {
                font-size: 12px;
                font-weight: bold;
                color: #00d4ff;
            }
            
            .stat-label {
                font-size: 8px;
                color: rgba(255,255,255,0.7);
            }
            
            .dashboard-float-btn {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                background: linear-gradient(135deg, #00d4ff, #0099cc);
                border: none;
                border-radius: 50%;
                color: #1a1a2e;
                cursor: pointer;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                z-index: 9999;
                box-shadow: 0 4px 20px rgba(0, 212, 255, 0.3);
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            
            .dashboard-float-btn:hover {
                transform: scale(1.1) rotate(5deg);
                box-shadow: 0 6px 25px rgba(0, 212, 255, 0.5);
            }
            
            .float-icon {
                font-size: 16px;
            }
            
            .float-text {
                font-size: 8px;
                letter-spacing: 0.5px;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Public methods for external integration
    addProof(hash, points) {
        this.stats.sessionProofs++;
        this.stats.sessionPoints += points;
        this.stats.recentProofs.push({
            hash: hash,
            points: points,
            timestamp: Date.now(),
            rarity: this.calculateRarity(hash)
        });
        
        // Keep only last 10 proofs
        if (this.stats.recentProofs.length > 10) {
            this.stats.recentProofs = this.stats.recentProofs.slice(-10);
        }
        
        this.updateRecentProofs();
        this.playProofSound();
    }
    
    updateHashCount(count) {
        this.stats.sessionHashes = count;
    }
    
    updateHashrate(rate) {
        this.stats.currentHashrate = rate;
    }
    
    setTarget(target) {
        this.stats.currentTarget = target;
    }
    
    getPowerLevel() {
        return this.powerLevel;
    }
    
    getCurrentPrefix() {
        return this.currentPrefix;
    }
    
    isActiveMining() {
        return this.isActive;
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Create global dashboard instance
    window.enhancedMiningDashboard = new EnhancedMiningDashboard();
    
    // Restore visibility state
    const wasVisible = localStorage.getItem('mining-dashboard-visible') === 'true';
    if (wasVisible) {
        window.enhancedMiningDashboard.show();
    }
    
    // Integration with existing mining systems
    if (window.createFloatingProof) {
        const originalCreateFloatingProof = window.createFloatingProof;
        window.createFloatingProof = function(element, points) {
            // Call original function
            originalCreateFloatingProof(element, points);
            
            // Update dashboard stats
            if (window.enhancedMiningDashboard) {
                const hash = 'proof_' + Date.now().toString(16);
                window.enhancedMiningDashboard.addProof(hash, points);
            }
        };
    }
    
    console.log('🎯 Enhanced Mining Dashboard ready');
});