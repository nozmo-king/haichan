<!-- Enhanced Mining Dashboard with Sophisticated Animations -->
<div id="mining-dashboard" class="mining-dashboard-enhanced" style="display: block;">
    <div class="mining-dashboard-header">
        <h3 class="mining-dashboard-title">
            <span class="mining-title-icon">⛏️</span>
            QUANTUM MINING SYSTEM
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
</div>

<style>
/* Enhanced Mining Dashboard Styles */
.mining-dashboard-enhanced {
    margin: var(--space-xl) 0;
    padding: 0;
    background: transparent;
}

.mining-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-lg);
    background: var(--bg-mining);
    border: 2px solid var(--border-mining);
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    position: relative;
    overflow: hidden;
}

.mining-dashboard-title {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    font-size: var(--font-size-lg);
    font-weight: 600;
    color: var(--text-mining);
    margin: 0;
    font-family: var(--font-display);
    letter-spacing: 1px;
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
    color: var(--text-secondary);
    font-weight: 600;
    letter-spacing: 0.5px;
}

.difficulty-value {
    font-size: var(--font-size-lg);
    color: var(--text-accent);
    font-weight: 700;
    font-family: var(--font-primary);
}

.difficulty-rarity {
    font-size: var(--font-size-micro);
    color: var(--accent-secondary);
    font-weight: 600;
    letter-spacing: 1px;
    animation: achievementPulse 3s ease-in-out infinite;
}

.mining-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-lg);
    padding: var(--space-lg);
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-top: none;
}

.mining-activity-feed {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-subtle);
    border-radius: 0 0 var(--radius-lg) var(--radius-lg);
    padding: var(--space-lg);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-md);
}

.activity-title {
    font-size: var(--font-size-sm);
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.activity-stream-indicator {
    width: 8px;
    height: 8px;
    background: var(--accent-primary);
    border-radius: 50%;
    animation: miningBreathing 1.5s ease-in-out infinite;
    box-shadow: 0 0 10px rgba(0, 169, 165, 0.5);
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
    font-size: var(--font-size-sm);
    color: var(--text-primary);
    line-height: 1.4;
}

.activity-timestamp {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    margin-top: var(--space-xs);
    font-family: var(--font-primary);
}

.mining-hashrate-display {
    position: absolute;
    top: var(--space-lg);
    right: var(--space-lg);
    background: var(--bg-tertiary);
    border: 1px solid var(--border-primary);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    min-width: 140px;
    text-align: center;
}

.hashrate-label {
    font-size: var(--font-size-micro);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: var(--space-xs);
}

.hashrate-value {
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--text-accent);
    font-family: var(--font-primary);
    margin-bottom: var(--space-sm);
}

.hashrate-unit {
    font-size: var(--font-size-sm);
    color: var(--text-secondary);
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
    background: var(--accent-primary);
    border-radius: 1px 1px 0 0;
    transition: height var(--transition-smooth);
    opacity: 0.7;
    min-height: 2px;
}

.hashrate-bar:last-child {
    opacity: 1;
    background: var(--accent-secondary);
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
</style>

<script>
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
        
        this.initializeHashrateChart();
        this.startHashrateMonitoring();
        
        console.log('🎯 Enhanced Mining Dashboard initialized');
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