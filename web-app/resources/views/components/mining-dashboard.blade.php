<!-- Enhanced Mining Dashboard with Sophisticated Animations -->
<div id="mining-dashboard" class="mining-dashboard-enhanced" style="display: none;">
    <div class="mining-dashboard-header">
        <h3 class="mining-dashboard-title">
            <span class="mining-title-icon">⛏️</span>
            MINING SYSTEM
            <button class="dashboard-toggle" id="mining-dashboard-toggle" title="Toggle Mining Dashboard">
                <span id="dashboard-toggle-icon">📊</span>
            </button>
            <span class="mining-status-badge mining-status-active">
                <div class="mining-loader"></div>
                <span>ACTIVE</span>
            </span>
        </h3>
        <div class="mining-difficulty-indicator">
            <span class="difficulty-label">DIFFICULTY</span>
            <span class="difficulty-value">21e8</span>
            <span class="difficulty-rarity">LEGENDARY</span>
        </div>
    </div>

    <div class="mining-metrics-grid">
        <div class="mining-metric">
            <div class="mining-metric-value" data-stat="proofs">0</div>
            <div class="mining-metric-label">Proofs Found</div>
            <div class="mining-progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="mining-metric">
            <div class="mining-metric-value" data-stat="points">0.0</div>
            <div class="mining-metric-label">Points Earned</div>
            <div class="mining-progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="mining-metric">
            <div class="mining-metric-value" data-stat="hashes">0</div>
            <div class="mining-metric-label">Total Hashes</div>
            <div class="mining-progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <div class="mining-metric">
            <div class="mining-metric-value" id="mining-efficiency">0%</div>
            <div class="mining-metric-label">Efficiency</div>
            <div class="mining-progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mining-activity-feed">
        <div class="activity-header">
            <span class="activity-title">Recent Mining Activity</span>
            <div class="activity-stream-indicator"></div>
        </div>
        <div class="activity-list" id="mining-activity-list">
            <div class="activity-item activity-placeholder">
                <div class="activity-icon">💎</div>
                <div class="activity-content">
                    <div class="activity-description">Mouseover mining system initialized</div>
                    <div class="activity-timestamp">Ready for hash discovery</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Hash Rate Display -->
    <div class="mining-hashrate-display">
        <div class="hashrate-label">Hash Rate</div>
        <div class="hashrate-value" id="current-hashrate">
            <span class="hashrate-number">0</span>
            <span class="hashrate-unit">H/s</span>
        </div>
        <div class="hashrate-chart">
            <div class="hashrate-bars">
                <!-- Dynamic bars will be added via JavaScript -->
            </div>
        </div>
    </div>

    <!-- Elite Mining Reputation Display -->
    <div class="mining-reputation-display" style="display: none;" id="mining-reputation">
        <div class="reputation-header">
            <span class="reputation-crown">👑</span>
            <span class="reputation-title" id="reputation-title">Elite Miner</span>
        </div>
        <div class="reputation-level">
            <span>Level </span><span id="reputation-level">1</span>
        </div>
        <div class="reputation-progress">
            <div class="reputation-bar">
                <div class="reputation-fill" id="reputation-fill" style="width: 0%"></div>
            </div>
        </div>
    </div>
</div>

<style nonce="{{ app('csp_nonce') }}">
/* Enhanced Mining Dashboard Styles - New Green & Pink Theme */
.mining-dashboard-enhanced {
    margin: var(--space-xl) 0;
    padding: 0;
    background: transparent;
    --dash-primary: var(--color-dark-green);      /* Dark green from new palette */
    --dash-secondary: var(--color-medium-green);  /* Medium green from new palette */
    --dash-light: var(--color-light-pink);        /* Light pink from new palette */
    --dash-bg: var(--color-very-light-pink);      /* Very light pink background */
    --dash-border: var(--border-primary);         /* Border from global system */
    --dash-text: var(--text-primary);             /* Text from global system */
    --dash-accent: var(--accent-primary);         /* Accent from global system */
}

.mining-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background: var(--dash-primary) !important;
    border: 2px solid var(--dash-border) !important;
    border-radius: 12px 12px 0 0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px var(--dash-border);
}

.mining-dashboard-title {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 20px;
    font-weight: 600;
    color: white !important;
    margin: 0;
    font-family: monospace;
    letter-spacing: 1px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    flex: 1;
}

.dashboard-toggle {
    background: transparent;
    border: 1px solid var(--dash-accent);
    border-radius: 4px;
    padding: 4px 8px;
    color: var(--dash-accent);
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
    margin-left: auto;
    margin-right: 15px;
}

.dashboard-toggle:hover {
    background: var(--dash-accent);
    color: var(--dash-primary);
    transform: scale(1.05);
}

.mining-title-icon {
    animation: miningBreathing 2s ease-in-out infinite;
    font-size: var(--font-size-xl);
}

.mining-status-badge {
    margin-left: auto;
    gap: var(--space-xs);
}

.mining-difficulty-indicator {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    text-align: right;
}

.difficulty-label {
    font-size: var(--font-size-micro);
    color: var(--dash-accent);
    font-weight: 600;
    letter-spacing: 0.5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.difficulty-value {
    font-size: var(--font-size-lg);
    color: var(--grass-text);
    font-weight: 700;
    font-family: var(--font-primary);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.difficulty-rarity {
    font-size: var(--font-size-micro);
    color: var(--dash-accent);
    font-weight: 600;
    letter-spacing: 1px;
    animation: achievementPulse 3s ease-in-out infinite;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.mining-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    padding: 20px;
    background: var(--dash-bg) !important;
    border: 1px solid var(--dash-border) !important;
    border-top: none;
    backdrop-filter: blur(2px);
}

.mining-activity-feed {
    background: var(--dash-bg) !important;
    border: 1px solid var(--dash-border) !important;
    border-radius: 0 0 12px 12px;
    padding: 20px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-md);
}

.activity-title {
    font-size: 14px;
    font-weight: 600;
    color: white !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.activity-stream-indicator {
    width: 8px;
    height: 8px;
    background: var(--grass-accent);
    border-radius: 50%;
    animation: miningBreathing 1.5s ease-in-out infinite;
    box-shadow: 0 0 10px rgba(144, 238, 144, 0.6);
}

.activity-list {
    max-height: 200px;
    overflow-y: auto;
    scrollbar-width: thin;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
    padding: var(--space-sm) 0;
    border-bottom: 1px solid var(--border-subtle);
    opacity: 0;
    transform: translateY(10px);
    animation: activitySlideIn 0.5s ease-out forwards;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-placeholder {
    opacity: 0.6;
}

.activity-icon {
    font-size: var(--font-size-md);
    flex-shrink: 0;
}

.activity-content {
    flex-grow: 1;
    min-width: 0;
}

.activity-description {
    font-size: 14px;
    color: white !important;
    line-height: 1.4;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.activity-timestamp {
    font-size: 12px;
    color: var(--dash-accent) !important;
    margin-top: 5px;
    font-family: monospace;
    opacity: 0.9;
}

.mining-hashrate-display {
    position: absolute;
    top: var(--space-lg);
    right: var(--space-lg);
    background: var(--grass-secondary);
    border: 1px solid var(--grass-border);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    min-width: 140px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(45, 80, 22, 0.2);
}

.hashrate-label {
    font-size: var(--font-size-micro);
    color: var(--dash-accent);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: var(--space-xs);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.hashrate-value {
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--grass-text);
    font-family: var(--font-primary);
    margin-bottom: var(--space-sm);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.hashrate-unit {
    font-size: var(--font-size-sm);
    color: var(--dash-accent);
    font-weight: 500;
}

.hashrate-chart {
    height: 30px;
    position: relative;
}

.hashrate-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    height: 100%;
    gap: 1px;
}

.hashrate-bar {
    flex: 1;
    background: var(--grass-light);
    border-radius: 1px 1px 0 0;
    transition: height var(--transition-smooth);
    opacity: 0.7;
    min-height: 2px;
}

.hashrate-bar:last-child {
    opacity: 1;
    background: var(--grass-accent);
}

/* Mining metric cards with grass theme */
.mining-metric {
    background: var(--dash-primary) !important;
    border: 1px solid var(--dash-border) !important;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(45, 80, 22, 0.1);
}

.mining-metric-value {
    font-size: 24px !important;
    font-weight: 700;
    color: white !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    font-family: monospace;
    margin-bottom: 5px;
}

.mining-metric-label {
    font-size: 14px !important;
    color: var(--dash-accent) !important;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}

.progress-bar {
    background: rgba(0,0,0,0.2);
    border-radius: var(--radius-sm);
    height: 4px;
    overflow: hidden;
}

.progress-fill {
    background: var(--grass-accent);
    height: 100%;
    border-radius: var(--radius-sm);
    transition: width 0.3s ease;
    box-shadow: 0 0 6px rgba(144, 238, 144, 0.4);
}

.mining-status-badge {
    background: var(--grass-accent);
    color: var(--grass-primary);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: var(--font-size-xs);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    box-shadow: 0 2px 4px rgba(144, 238, 144, 0.3);
}

@keyframes activitySlideIn {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Collapsed state */
.mining-dashboard-enhanced.collapsed .mining-metrics-grid,
.mining-dashboard-enhanced.collapsed .mining-activity-feed,
.mining-dashboard-enhanced.collapsed .mining-hashrate-display {
    display: none;
}

.mining-dashboard-enhanced.collapsed {
    margin: var(--space-sm) 0;
}

.mining-dashboard-enhanced.collapsed .mining-dashboard-header {
    border-radius: 8px;
    padding: 12px 20px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .mining-dashboard-header {
        flex-direction: column;
        gap: var(--space-md);
        text-align: center;
    }
    
    .mining-metrics-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .mining-hashrate-display {
        position: static;
        margin-top: var(--space-md);
    }
}

/* Elite Mining Reputation Display */
.mining-reputation-display {
    position: absolute;
    top: var(--space-lg);
    left: var(--space-lg);
    background: linear-gradient(135deg, rgba(255, 215, 0, 0.9), rgba(255, 165, 0, 0.9));
    color: #333;
    border: 1px solid rgba(255, 215, 0, 0.5);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    min-width: 140px;
    text-align: center;
    box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
    backdrop-filter: blur(8px);
}

.reputation-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-bottom: var(--space-xs);
}

.reputation-crown {
    font-size: 16px;
    animation: crown-glow 3s ease-in-out infinite;
}

.reputation-title {
    font-size: var(--font-size-xs);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.reputation-level {
    font-size: var(--font-size-md);
    font-weight: 700;
    color: #FF6B35;
    margin-bottom: var(--space-xs);
}

.reputation-progress {
    width: 100%;
}

.reputation-bar {
    background: rgba(0, 0, 0, 0.2);
    border-radius: var(--radius-sm);
    height: 6px;
    overflow: hidden;
}

.reputation-fill {
    background: linear-gradient(90deg, #FF6B35, #FFD700);
    height: 100%;
    border-radius: var(--radius-sm);
    transition: width 0.5s ease;
    box-shadow: 0 0 8px rgba(255, 107, 53, 0.6);
}

@keyframes crown-glow {
    0%, 100% { 
        transform: scale(1); 
        filter: drop-shadow(0 0 4px rgba(255, 215, 0, 0.8));
    }
    50% { 
        transform: scale(1.1); 
        filter: drop-shadow(0 0 8px rgba(255, 215, 0, 1));
    }
}
</style>

<script nonce="{{ app('csp_nonce') }}">
document.addEventListener('DOMContentLoaded', function() {
    // Initialize enhanced mining dashboard
    if (typeof MiningDashboard === 'undefined') {
        window.MiningDashboard = new EnhancedMiningDashboard();
    }
});

class EnhancedMiningDashboard {
    constructor() {
        this.activityList = document.getElementById('mining-activity-list');
        this.hashrateDisplay = document.getElementById('current-hashrate');
        this.hashrateChart = document.querySelector('.hashrate-bars');
        this.hashrateHistory = [];
        this.maxHistoryLength = 20;
        this.isCollapsed = false;
        
        this.initializeHashrateChart();
        this.startHashrateMonitoring();
        this.bindEvents();
        this.integrateWithGlobalState();
        
        console.log('🎯 Enhanced Mining Dashboard initialized');
    }
    
    bindEvents() {
        const toggleBtn = document.getElementById('mining-dashboard-toggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                this.toggleCollapse();
            });
        }
    }
    
    integrateWithGlobalState() {
        // Wait for global state to be available
        const waitForState = () => {
            if (window.HaichanState) {
                // Subscribe to mining state changes
                window.HaichanState.on('state:mining.isActive', (data) => {
                    this.updateMiningStatus(data.newValue);
                });
                
                window.HaichanState.on('state:ui.miniDashVisible', (data) => {
                    this.setVisibility(data.newValue);
                });
                
                // Initial state sync
                const visible = window.HaichanState.getState('ui.miniDashVisible');
                this.setVisibility(visible);
            } else {
                setTimeout(waitForState, 100);
            }
        };
        waitForState();
    }
    
    toggleCollapse() {
        this.isCollapsed = !this.isCollapsed;
        const dashboard = document.getElementById('mining-dashboard');
        const toggleIcon = document.getElementById('dashboard-toggle-icon');
        
        if (this.isCollapsed) {
            dashboard.classList.add('collapsed');
            toggleIcon.textContent = '📈';
        } else {
            dashboard.classList.remove('collapsed');
            toggleIcon.textContent = '📊';
        }
        
        // Update global state
        if (window.HaichanState) {
            window.HaichanState.setState('ui.miniDashCollapsed', this.isCollapsed);
        }
    }
    
    setVisibility(visible) {
        const dashboard = document.getElementById('mining-dashboard');
        if (dashboard) {
            dashboard.style.display = visible ? 'block' : 'none';
        }
    }
    
    updateMiningStatus(isActive) {
        const statusBadge = document.querySelector('.mining-status-badge span');
        const statusLoader = document.querySelector('.mining-loader');
        
        if (statusBadge) {
            statusBadge.textContent = isActive ? 'ACTIVE' : 'IDLE';
        }
        
        if (statusLoader) {
            statusLoader.style.display = isActive ? 'block' : 'none';
        }
    }

    addActivity(icon, description, timestamp = null) {
        if (!this.activityList) return;
        
        // Remove placeholder if it exists
        const placeholder = this.activityList.querySelector('.activity-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
        
        const activityItem = document.createElement('div');
        activityItem.className = 'activity-item';
        activityItem.innerHTML = `
            <div class="activity-icon">${icon}</div>
            <div class="activity-content">
                <div class="activity-description">${description}</div>
                <div class="activity-timestamp">${timestamp || new Date().toLocaleTimeString()}</div>
            </div>
        `;
        
        // Insert at top
        this.activityList.insertBefore(activityItem, this.activityList.firstChild);
        
        // Limit to 10 items
        const items = this.activityList.querySelectorAll('.activity-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
        
        // Trigger animation
        setTimeout(() => {
            activityItem.style.opacity = '1';
            activityItem.style.transform = 'translateY(0)';
        }, 10);
    }

    updateHashrate(hashrate) {
        if (this.hashrateDisplay) {
            const numberSpan = this.hashrateDisplay.querySelector('.hashrate-number');
            if (numberSpan) {
                numberSpan.textContent = hashrate.toFixed(1);
            }
        }
        
        // Update history
        this.hashrateHistory.push(hashrate);
        if (this.hashrateHistory.length > this.maxHistoryLength) {
            this.hashrateHistory.shift();
        }
        
        this.updateHashrateChart();
    }

    initializeHashrateChart() {
        if (!this.hashrateChart) return;
        
        // Create initial bars
        for (let i = 0; i < this.maxHistoryLength; i++) {
            const bar = document.createElement('div');
            bar.className = 'hashrate-bar';
            bar.style.height = '2px';
            this.hashrateChart.appendChild(bar);
        }
    }

    updateHashrateChart() {
        const bars = this.hashrateChart.querySelectorAll('.hashrate-bar');
        const maxValue = Math.max(...this.hashrateHistory, 1);
        
        this.hashrateHistory.forEach((value, index) => {
            if (bars[index]) {
                const height = (value / maxValue) * 100;
                bars[index].style.height = `${Math.max(height, 5)}%`;
            }
        });
    }

    startHashrateMonitoring() {
        // Real hashrate monitoring - connect to actual mining system
        setInterval(() => {
            // Get real hashrate from mining system if available
            let currentRate = 0;
            
            if (window.mouseoverMiner && window.mouseoverMiner.stats) {
                currentRate = window.mouseoverMiner.stats.currentHashrate || 0;
            }
            
            this.updateHashrate(currentRate);
        }, 2000);
    }
}
</script>