/**
 * Haichan Mining Dashboard v3.0 - Clean & Modern Design
 * No ugly notifications, clean interface, essential stats only
 */

class HaichanMiningDashboard {
    constructor() {
        this.isVisible = false;
        this.isMinimized = false;
        this.isDragging = false;
        this.dragOffset = { x: 0, y: 0 };
        
        this.powerLevel = 5;
        this.currentPrefix = '21e8';
        this.isActive = false;
        
        this.stats = {
            sessionHashes: 0,
            sessionProofs: 0,
            sessionPoints: 0,
            currentHashrate: 0,
            currentTarget: 'None'
        };
        
        this.init();
    }
    
    init() {
        this.createDashboard();
        this.setupEventListeners();
        this.setupDragging();
        this.startStatsUpdate();
        console.log('✅ Mining Dashboard v3.0 initialized');
    }
    
    createDashboard() {
        const dashboardHTML = `
            <div id="mining-dashboard">
                <div class="dashboard-header">
                    <span class="dashboard-title">⛏️ HAICHAN MINER</span>
                    <div class="dashboard-controls">
                        <button class="dash-btn minimize-btn" title="Minimize">−</button>
                        <button class="dash-btn close-btn" title="Close">×</button>
                    </div>
                </div>
                
                <div class="dashboard-content">
                    <!-- Power Control -->
                    <div class="power-section">
                        <div class="power-header">
                            <span class="power-label">Mining Power</span>
                            <span class="power-value" id="power-display">5/10</span>
                        </div>
                        <div class="power-slider-container">
                            <input type="range" class="power-slider" id="power-slider" 
                                   min="0" max="10" value="5" step="1">
                            <div class="power-description" id="power-description">
                                Moderate intensity
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mining Target -->
                    <div class="target-section">
                        <div class="target-header">Current Target</div>
                        <div class="target-display" id="current-target">Hover over content</div>
                        <div class="target-type" id="target-type">No active mining</div>
                    </div>
                    
                    <!-- Live Stats -->
                    <div class="stats-section">
                        <div class="stat-grid">
                            <div class="stat-item">
                                <div class="stat-value" id="stat-hashes">0</div>
                                <div class="stat-label">HASHES</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="stat-hashrate">0</div>
                                <div class="stat-label">H/S</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="stat-proofs">0</div>
                                <div class="stat-label">PROOFS</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="stat-points">0</div>
                                <div class="stat-label">POINTS</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Difficulty Selector -->
                    <div class="difficulty-section">
                        <div class="difficulty-header">Mining Difficulty</div>
                        <select class="difficulty-selector" id="prefix-selector">
                            <option value="21">Easy (1:256)</option>
                            <option value="21e">Medium (1:4K)</option>
                            <option value="21e8" selected>Hard (1:65K)</option>
                            <option value="21e88">Very Hard (1:1M)</option>
                            <option value="21e888">Extreme (1:16M)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Toggle Button -->
            <button class="dashboard-toggle" id="dashboard-toggle" title="Mining Dashboard">
                <div class="toggle-icon">⛏️</div>
                <div class="toggle-text">MINE</div>
            </button>
        `;
        
        document.body.insertAdjacentHTML('beforeend', dashboardHTML);
        this.addStyles();
    }
    
    setupEventListeners() {
        const dashboard = document.getElementById('mining-dashboard');
        const toggleBtn = document.getElementById('dashboard-toggle');
        const minimizeBtn = dashboard.querySelector('.minimize-btn');
        const closeBtn = dashboard.querySelector('.close-btn');
        const powerSlider = document.getElementById('power-slider');
        const prefixSelector = document.getElementById('prefix-selector');
        
        toggleBtn.addEventListener('click', () => this.toggle());
        minimizeBtn.addEventListener('click', () => this.minimize());
        closeBtn.addEventListener('click', () => this.hide());
        
        powerSlider.addEventListener('input', (e) => {
            this.powerLevel = parseInt(e.target.value);
            this.updatePowerDisplay();
        });
        
        prefixSelector.addEventListener('change', (e) => {
            this.currentPrefix = e.target.value;
            console.log(`🎯 Mining difficulty: ${this.currentPrefix}`);
        });
    }
    
    setupDragging() {
        const dashboard = document.getElementById('mining-dashboard');
        const header = dashboard.querySelector('.dashboard-header');
        
        header.addEventListener('mousedown', (e) => {
            if (e.target.classList.contains('dash-btn')) return;
            
            this.isDragging = true;
            const rect = dashboard.getBoundingClientRect();
            this.dragOffset = {
                x: e.clientX - rect.left,
                y: e.clientY - rect.top
            };
            
            dashboard.classList.add('dragging');
        });
        
        document.addEventListener('mousemove', (e) => {
            if (!this.isDragging) return;
            
            const x = e.clientX - this.dragOffset.x;
            const y = e.clientY - this.dragOffset.y;
            
            dashboard.style.left = Math.max(0, Math.min(x, window.innerWidth - dashboard.offsetWidth)) + 'px';
            dashboard.style.top = Math.max(0, Math.min(y, window.innerHeight - dashboard.offsetHeight)) + 'px';
            dashboard.style.right = 'auto';
        });
        
        document.addEventListener('mouseup', () => {
            if (this.isDragging) {
                this.isDragging = false;
                dashboard.classList.remove('dragging');
            }
        });
    }
    
    show() {
        document.getElementById('mining-dashboard').style.display = 'block';
        this.isVisible = true;
        localStorage.setItem('mining-dashboard-visible', 'true');
    }
    
    hide() {
        document.getElementById('mining-dashboard').style.display = 'none';
        this.isVisible = false;
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
        const dashboard = document.getElementById('mining-dashboard');
        const content = dashboard.querySelector('.dashboard-content');
        const minimizeBtn = dashboard.querySelector('.minimize-btn');
        
        if (this.isMinimized) {
            content.style.display = 'block';
            minimizeBtn.textContent = '−';
            this.isMinimized = false;
        } else {
            content.style.display = 'none';
            minimizeBtn.textContent = '+';
            this.isMinimized = true;
        }
    }
    
    updatePowerDisplay() {
        const powerDisplay = document.getElementById('power-display');
        const powerDescription = document.getElementById('power-description');
        
        powerDisplay.textContent = `${this.powerLevel}/10`;
        
        const descriptions = {
            0: 'Mining disabled',
            1: 'Very low intensity',
            2: 'Low intensity', 
            3: 'Light intensity',
            4: 'Below average',
            5: 'Moderate intensity',
            6: 'Above average',
            7: 'High intensity',
            8: 'Very high',
            9: 'Extreme intensity',
            10: 'MAXIMUM POWER'
        };
        
        powerDescription.textContent = descriptions[this.powerLevel];
        
        // Update slider color
        const slider = document.getElementById('power-slider');
        const percentage = (this.powerLevel / 10) * 100;
        slider.style.background = `linear-gradient(to right, var(--accent-color) 0%, var(--accent-color) ${percentage}%, #333 ${percentage}%, #333 100%)`;
    }
    
    startStatsUpdate() {
        setInterval(() => {
            this.updateStatsDisplay();
        }, 1000);
    }
    
    updateStatsDisplay() {
        document.getElementById('stat-hashes').textContent = this.stats.sessionHashes.toLocaleString();
        document.getElementById('stat-hashrate').textContent = this.stats.currentHashrate.toLocaleString();
        document.getElementById('stat-proofs').textContent = this.stats.sessionProofs;
        document.getElementById('stat-points').textContent = this.stats.sessionPoints;
        document.getElementById('current-target').textContent = this.stats.currentTarget;
    }
    
    // Public methods for integration
    addProof(hash, points) {
        this.stats.sessionProofs++;
        this.stats.sessionPoints += points;
        
        // Simple flash effect on stats - NO ugly popup
        const proofsElement = document.getElementById('stat-proofs');
        const pointsElement = document.getElementById('stat-points');
        
        if (proofsElement && pointsElement) {
            proofsElement.style.color = 'var(--success-color)';
            pointsElement.style.color = 'var(--success-color)';
            
            setTimeout(() => {
                proofsElement.style.color = '';
                pointsElement.style.color = '';
            }, 1000);
        }
        
        console.log(`💎 Proof found: ${hash.substring(0, 12)}... (+${points} pts)`);
    }
    
    updateHashCount(count) {
        this.stats.sessionHashes = count;
    }
    
    updateHashrate(rate) {
        this.stats.currentHashrate = rate;
    }
    
    setTarget(target) {
        this.stats.currentTarget = target;
        const typeElement = document.getElementById('target-type');
        if (typeElement) {
            typeElement.textContent = target === 'Hover over content' ? 'No active mining' : 'Mining active';
        }
    }
    
    getPowerLevel() {
        return this.powerLevel;
    }
    
    getCurrentPrefix() {
        return this.currentPrefix;
    }
    
    addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            :root {
                --primary-bg: #1a1a2e;
                --secondary-bg: #16213e;
                --accent-color: #00d4ff;
                --text-color: #ffffff;
                --muted-color: #888;
                --success-color: #00ff88;
                --border-radius: 8px;
            }
            
            #mining-dashboard {
                position: fixed;
                top: 20px;
                right: 20px;
                width: 280px;
                background: var(--primary-bg);
                border: 1px solid var(--accent-color);
                border-radius: var(--border-radius);
                font-family: 'Courier New', monospace;
                font-size: 11px;
                color: var(--text-color);
                z-index: 10000;
                box-shadow: 0 4px 20px rgba(0, 212, 255, 0.2);
                display: none;
            }
            
            #mining-dashboard.dragging {
                opacity: 0.9;
                transform: rotate(2deg);
            }
            
            .dashboard-header {
                background: var(--accent-color);
                color: var(--primary-bg);
                padding: 8px 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: var(--border-radius) var(--border-radius) 0 0;
                cursor: move;
            }
            
            .dashboard-title {
                font-weight: bold;
                font-size: 12px;
            }
            
            .dashboard-controls {
                display: flex;
                gap: 4px;
            }
            
            .dash-btn {
                background: rgba(26, 26, 46, 0.3);
                border: none;
                color: var(--primary-bg);
                padding: 2px 6px;
                border-radius: 3px;
                cursor: pointer;
                font-size: 10px;
                font-weight: bold;
            }
            
            .dash-btn:hover {
                background: rgba(26, 26, 46, 0.5);
            }
            
            .dashboard-content {
                padding: 12px;
                display: flex;
                flex-direction: column;
                gap: 12px;
            }
            
            .power-section {
                background: var(--secondary-bg);
                padding: 8px;
                border-radius: var(--border-radius);
            }
            
            .power-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 6px;
            }
            
            .power-label {
                font-size: 10px;
                color: var(--muted-color);
                text-transform: uppercase;
            }
            
            .power-value {
                font-size: 12px;
                font-weight: bold;
                color: var(--accent-color);
            }
            
            .power-slider-container {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            
            .power-slider {
                width: 100%;
                height: 4px;
                border-radius: 2px;
                outline: none;
                appearance: none;
                background: #333;
                cursor: pointer;
            }
            
            .power-slider::-webkit-slider-thumb {
                appearance: none;
                width: 14px;
                height: 14px;
                background: var(--accent-color);
                border-radius: 50%;
                cursor: pointer;
                box-shadow: 0 0 4px rgba(0, 212, 255, 0.5);
            }
            
            .power-description {
                font-size: 9px;
                color: var(--muted-color);
                text-align: center;
            }
            
            .target-section {
                background: var(--secondary-bg);
                padding: 8px;
                border-radius: var(--border-radius);
                text-align: center;
            }
            
            .target-header {
                font-size: 10px;
                color: var(--muted-color);
                text-transform: uppercase;
                margin-bottom: 4px;
            }
            
            .target-display {
                font-size: 11px;
                font-weight: bold;
                color: var(--text-color);
                margin-bottom: 2px;
            }
            
            .target-type {
                font-size: 9px;
                color: var(--muted-color);
            }
            
            .stats-section {
                background: var(--secondary-bg);
                padding: 8px;
                border-radius: var(--border-radius);
            }
            
            .stat-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            
            .stat-item {
                text-align: center;
                padding: 4px;
                background: rgba(0, 212, 255, 0.1);
                border-radius: 4px;
                transition: color 0.3s ease;
            }
            
            .stat-value {
                font-size: 12px;
                font-weight: bold;
                color: var(--text-color);
            }
            
            .stat-label {
                font-size: 8px;
                color: var(--muted-color);
                text-transform: uppercase;
                margin-top: 2px;
            }
            
            .difficulty-section {
                background: var(--secondary-bg);
                padding: 8px;
                border-radius: var(--border-radius);
            }
            
            .difficulty-header {
                font-size: 10px;
                color: var(--muted-color);
                text-transform: uppercase;
                margin-bottom: 6px;
            }
            
            .difficulty-selector {
                width: 100%;
                background: var(--primary-bg);
                color: var(--text-color);
                border: 1px solid var(--accent-color);
                border-radius: 4px;
                padding: 4px;
                font-size: 10px;
                font-family: inherit;
            }
            
            .dashboard-toggle {
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 60px;
                height: 60px;
                background: var(--accent-color);
                border: none;
                border-radius: 50%;
                color: var(--primary-bg);
                cursor: pointer;
                font-family: 'Courier New', monospace;
                font-weight: bold;
                z-index: 9999;
                box-shadow: 0 4px 12px rgba(0, 212, 255, 0.3);
                transition: all 0.3s ease;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 2px;
            }
            
            .dashboard-toggle:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 16px rgba(0, 212, 255, 0.4);
            }
            
            .toggle-icon {
                font-size: 16px;
            }
            
            .toggle-text {
                font-size: 8px;
                font-weight: bold;
            }
        `;
        
        document.head.appendChild(style);
    }
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    window.enhancedMiningDashboard = new HaichanMiningDashboard();
    
    // Restore visibility state
    const wasVisible = localStorage.getItem('mining-dashboard-visible') === 'true';
    if (wasVisible) {
        window.enhancedMiningDashboard.show();
    }
    
    console.log('🚀 Mining Dashboard v3.0 loaded');
});

// Initialize if DOM already loaded
if (document.readyState !== 'loading') {
    window.enhancedMiningDashboard = new HaichanMiningDashboard();
}