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
        this.currentPrefix = '21e'; // Default difficulty prefix (current active)
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
                    <span>Mining Control Panel</span>
                    <div class="dashboard-controls">
                        <button class="dashboard-btn minimize-btn" title="Minimize">−</button>
                        <button class="dashboard-btn close-btn" title="Close">×</button>
                    </div>
                </div>
                <div class="dashboard-content">
                    <!-- Power Control -->
                    <div class="power-control">
                        <h4>⚡ Mining Power</h4>
                        <div class="power-slider-container">
                            <input type="range" class="power-slider" id="power-slider" 
                                   min="0" max="10" value="5" step="1">
                            <div class="power-display" id="power-display">5</div>
                        </div>
                        <div class="mining-status">
                            <span class="status-indicator" id="status-indicator"></span>
                            <span class="status-text" id="status-text">Standby</span>
                        </div>
                    </div>
                    
                    <!-- Prefix Selection -->
                    <div class="prefix-control">
                        <h4>🎯 Difficulty Target</h4>
                        <select class="prefix-selector" id="prefix-selector">
                            <option value="2">2 (Easy - 1/16 chance)</option>
                            <option value="21">21 (Medium - 1/256 chance)</option>
                            <option value="21e" selected>21e (Hard - 1/4096 chance) ⭐ CURRENT</option>
                            <option value="21e8">21e8 (Very Hard - 1/65536 chance)</option>
                            <option value="21e88">21e88 (Extreme - 1/1048576 chance)</option>
                            <option value="21e888">21e888 (Insane - 1/16777216 chance)</option>
                        </select>
                    </div>
                    
                    <!-- Stats Grid -->
                    <div class="stats-section">
                        <h4>📊 Session Stats</h4>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <span class="stat-label">HASHES</span>
                                <span class="stat-value" id="stat-hashes">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">PROOFS</span>
                                <span class="stat-value" id="stat-proofs">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">POINTS</span>
                                <span class="stat-value" id="stat-points">0</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">H/S</span>
                                <span class="stat-value" id="stat-hashrate">0</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Target -->
                    <div class="target-display" id="target-display">
                        Target: <span id="current-target">None</span>
                    </div>
                    
                    <!-- Hash Rate -->
                    <div class="hashrate-display" id="hashrate-display">
                        Effective Rate: <span id="effective-rate">0 H/s</span>
                    </div>
                    
                    <!-- Recent Proofs -->
                    <div class="recent-proofs-section">
                        <h4>🏆 Recent Proofs</h4>
                        <div class="recent-proofs" id="recent-proofs">
                            <div style="text-align: center; color: #708B75; font-size: 9px; padding: 8px;">
                                No proofs found yet...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Toggle Button -->
            <button class="minimize-toggle" id="dashboard-toggle" title="Toggle Mining Dashboard">
                ⛏️
            </button>
        `;
        
        // Add to DOM
        document.body.insertAdjacentHTML('beforeend', dashboardHTML);
        
        // Add CSS
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
        });
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
        const powerDisplay = document.getElementById('power-display');
        powerDisplay.textContent = this.powerLevel;
        
        // Update power slider background color
        const slider = document.getElementById('power-slider');
        const percentage = (this.powerLevel / 10) * 100;
        slider.style.background = `linear-gradient(to right, #9AB87A 0%, #9AB87A ${percentage}%, #F5F5DC ${percentage}%, #F5F5DC 100%)`;
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
            container.innerHTML = '<div style="text-align: center; color: #708B75; font-size: 9px; padding: 8px;">No proofs found yet...</div>';
            return;
        }
        
        const proofsHTML = this.stats.recentProofs.slice(-5).reverse().map(proof => `
            <div class="proof-item">
                <span class="proof-hash">${proof.hash.substring(0, 8)}...</span>
                <span class="proof-points">+${proof.points}</span>
            </div>
        `).join('');
        
        container.innerHTML = proofsHTML;
    }
    
    // Public methods for external integration
    addProof(hash, points) {
        this.stats.sessionProofs++;
        this.stats.sessionPoints += points;
        this.stats.recentProofs.push({
            hash: hash,
            points: points,
            timestamp: Date.now()
        });
        
        // Keep only last 10 proofs
        if (this.stats.recentProofs.length > 10) {
            this.stats.recentProofs = this.stats.recentProofs.slice(-10);
        }
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